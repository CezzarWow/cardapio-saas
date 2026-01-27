<?php

namespace Tests\Unit;

use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\TableRepository;
use App\Services\CashRegisterService;
use App\Services\Order\CreateOrderAction;
use App\Services\PaymentService;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

class CreateOrderActionTest extends TestCase
{
    private $paymentService;
    private $cashRegisterService;
    private $stockRepository;
    private $orderRepository;
    private $itemRepository;
    private $tableRepository;
    private $clientRepository;
    private $action;

    protected function setUp(): void
    {
        TestDatabase::truncateAll();

        $this->paymentService = $this->createMock(PaymentService::class);
        $this->cashRegisterService = $this->createMock(CashRegisterService::class);
        $this->stockRepository = $this->createMock(StockRepository::class);
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->itemRepository = $this->createMock(OrderItemRepository::class);
        $this->tableRepository = $this->createMock(TableRepository::class);
        $this->clientRepository = $this->createMock(ClientRepository::class);

        $this->action = new CreateOrderAction(
            $this->paymentService,
            $this->cashRegisterService,
            $this->stockRepository,
            $this->orderRepository,
            $this->itemRepository,
            $this->tableRepository,
            $this->clientRepository
        );
    }

    public function testExecuteCreatesOrderSuccessfully(): void
    {
        $data = [
            'cart' => [
                ['id' => 1, 'product_id' => 1, 'quantity' => 2, 'price' => 10.00],
            ],
            'order_type' => 'balcao',
            'finalize_now' => true,
            'is_paid' => 1,
            'payments' => [['method' => 'dinheiro', 'amount' => 20.00]],
        ];

        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->with($this->isInstanceOf(PDO::class), 1)
            ->willReturn(['id' => 1]);

        $this->orderRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $payload) {
                return $payload['order_type'] === 'balcao'
                    && $payload['total'] === 20.0;
            }), 'concluido')
            ->willReturn(123);

        $this->orderRepository
            ->expects($this->once())
            ->method('updatePayment');

        $this->orderRepository
            ->expects($this->once())
            ->method('updateOrderType')
            ->with(123, 'balcao');

        $this->itemRepository
            ->expects($this->once())
            ->method('insert')
            ->with(123, $data['cart']);

        $this->stockRepository
            ->expects($this->atLeastOnce())
            ->method('decrement');

        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments');

        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement');

        $result = $this->action->execute(1, 5, $data);
        $this->assertEquals(123, $result);
    }

    public function testExecuteFailsValidationWithEmptyCart(): void
    {
        $data = [];

        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->with($this->isInstanceOf(PDO::class), 1)
            ->willReturn(['id' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('O carrinho');

        $this->action->execute(1, 5, $data);
    }

    public function testExecuteNormalizesOrderType(): void
    {
        $data = [
            'cart' => [
                ['id' => 1, 'product_id' => 1, 'quantity' => 1, 'price' => 10.00],
            ],
            'order_type' => 'entrega',
            'finalize_now' => false,
            'is_paid' => 0,
            'payments' => [],
        ];

        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->with($this->isInstanceOf(PDO::class), 1)
            ->willReturn(['id' => 1]);

        $this->orderRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $payload) {
                return $payload['order_type'] === 'delivery';
            }), 'novo')
            ->willReturn(456);

        $this->orderRepository
            ->expects($this->once())
            ->method('updateOrderType')
            ->with(456, 'delivery');

        $this->itemRepository
            ->expects($this->once())
            ->method('insert');

        $this->stockRepository
            ->expects($this->once())
            ->method('decrement');

        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments');

        $this->cashRegisterService
            ->expects($this->never())
            ->method('registerMovement');

        $result = $this->action->execute(1, 5, $data);
        $this->assertEquals(456, $result);
    }

    public function testExecuteHandlesExistingOrder(): void
    {
        $data = [
            'order_id' => 10,
            'cart' => [
                ['id' => 1, 'product_id' => 1, 'quantity' => 1, 'price' => 12.00],
            ],
            'order_type' => 'balcao',
            'finalize_now' => true,
            'is_paid' => 1,
            'payments' => [['method' => 'pix', 'amount' => 12.00]],
        ];

        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->with($this->isInstanceOf(PDO::class), 1)
            ->willReturn(['id' => 1]);

        $this->orderRepository
            ->expects($this->once())
            ->method('find')
            ->with(10)
            ->willReturn([
                'id' => 10,
                'status' => 'aberto',
                'total' => 0,
                'order_type' => 'balcao',
            ]);

        $this->orderRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with(10, 'concluido');

        $this->itemRepository
            ->expects($this->once())
            ->method('insert')
            ->with(10, $data['cart']);

        $this->orderRepository
            ->expects($this->once())
            ->method('updateTotal')
            ->with(10, 12.0);

        $this->orderRepository
            ->expects($this->never())
            ->method('create');

        $this->stockRepository
            ->expects($this->once())
            ->method('decrement');

        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments');

        $this->orderRepository
            ->expects($this->once())
            ->method('updatePayment');

        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement');

        $result = $this->action->execute(1, 5, $data);
        $this->assertEquals(10, $result);
    }

    public function testExecuteProcessesComboWithAdditionalsAndMultiplePayments(): void
    {
        $cart = [
            [
                'id' => 1,
                'product_id' => 1,
                'name' => 'Combo Especial',
                'price' => 20.00,
                'quantity' => 1,
                'extras' => [
                    ['id' => 101, 'name' => 'Molho Especial', 'price' => 3.00]
                ],
                'observation' => 'Sem cebola'
            ],
            [
                'id' => 2,
                'product_id' => 2,
                'name' => 'Cerveja',
                'price' => 8.00,
                'quantity' => 2
            ]
        ];

        $payments = [
            ['method' => 'pix', 'amount' => 20.00],
            ['method' => 'dinheiro', 'amount' => 16.00],
        ];

        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->with($this->isInstanceOf(PDO::class), 1)
            ->willReturn(['id' => 1]);

        $this->orderRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $payload) {
                return $payload['payment_method'] === 'multiplo'
                    && $payload['total'] === 36.0
                    && $payload['observation'] === null;
            }), 'concluido')
            ->willReturn(555);

        $this->orderRepository
            ->expects($this->once())
            ->method('updatePayment')
            ->with(555, true, 'multiplo');

        $this->orderRepository
            ->expects($this->once())
            ->method('updateOrderType')
            ->with(555, 'balcao');

        $this->itemRepository
            ->expects($this->once())
            ->method('insert')
            ->with(555, $cart);

        $stockCalls = [];
        $this->stockRepository
            ->expects($this->exactly(2))
            ->method('decrement')
            ->willReturnCallback(function (int $productId, int $quantity) use (&$stockCalls) {
                $stockCalls[] = [$productId, $quantity];
                return null;
            });

        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments')
            ->with($this->isInstanceOf(PDO::class), 555, $payments);

        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement')
            ->with(
                $this->isInstanceOf(PDO::class),
                1,
                36.0,
                'Venda Balcao #555',
                555
            );

        $this->action->execute(1, 5, [
            'cart' => $cart,
            'order_type' => 'balcao',
            'finalize_now' => true,
            'is_paid' => 1,
            'payments' => $payments,
        ]);

        $this->assertEquals([[1, 1], [2, 2]], $stockCalls);
    }
}
