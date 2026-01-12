<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Balcao\CreateBalcaoSaleAction;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;

/**
 * Testes unitários para CreateBalcaoSaleAction
 */
class CreateBalcaoSaleActionTest extends TestCase
{
    private $paymentService;
    private $cashRegisterService;
    private $orderRepo;
    private $itemRepo;
    private $stockRepo;
    private CreateBalcaoSaleAction $action;

    protected function setUp(): void
    {
        $this->paymentService = $this->createMock(PaymentService::class);
        $this->cashRegisterService = $this->createMock(CashRegisterService::class);
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->itemRepo = $this->createMock(OrderItemRepository::class);
        $this->stockRepo = $this->createMock(StockRepository::class);

        $this->action = new CreateBalcaoSaleAction(
            $this->paymentService,
            $this->cashRegisterService,
            $this->orderRepo,
            $this->itemRepo,
            $this->stockRepo
        );
    }

    public function testExecuteCreatesOrderWithConcluidoStatus(): void
    {
        // Arrange
        $restaurantId = 1;
        $data = [
            'cart' => [
                ['id' => 1, 'price' => 25.00, 'quantity' => 2]
            ],
            'payments' => [
                ['method' => 'pix', 'amount' => 50.00]
            ],
            'discount' => 0
        ];

        // Mock caixa aberto
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        // Expect create with status CONCLUIDO
        $this->orderRepo
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(function($orderData) use ($restaurantId) {
                    return $orderData['restaurant_id'] === $restaurantId
                        && $orderData['order_type'] === 'balcao'
                        && $orderData['total'] === 50.00;
                }),
                OrderStatus::CONCLUIDO // Segundo parâmetro = status
            )
            ->willReturn(123);

        // Expect updatePayment with main method
        $this->orderRepo
            ->expects($this->once())
            ->method('updatePayment')
            ->with(123, true, 'pix');

        // Expect items insert
        $this->itemRepo
            ->expects($this->once())
            ->method('insert');

        // Expect payments registration
        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments');

        // Expect stock decrement
        $this->stockRepo
            ->expects($this->once())
            ->method('decrement')
            ->with(1, 2);

        // Expect cash movement
        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement');

        // Act
        $result = $this->action->execute($restaurantId, $data);

        // Assert
        $this->assertEquals(123, $result['order_id']);
        $this->assertEquals(50.00, $result['total']);
    }

    public function testExecuteCalculatesTotalCorrectly(): void
    {
        // Arrange
        $data = [
            'cart' => [
                ['id' => 1, 'price' => 10.00, 'quantity' => 3], // 30
                ['id' => 2, 'price' => 20.00, 'quantity' => 2]  // 40
            ],
            'payments' => [
                ['method' => 'dinheiro', 'amount' => 65.00]
            ],
            'discount' => 5.00 // Total: 70 - 5 = 65
        ];

        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepo
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(function($orderData) {
                    return $orderData['total'] === 65.00;
                }),
                $this->anything()
            )
            ->willReturn(456);

        $this->stockRepo->method('decrement');
        $this->itemRepo->method('insert');
        $this->paymentService->method('registerPayments');
        $this->cashRegisterService->method('registerMovement');
        $this->orderRepo->method('updatePayment');

        // Act
        $result = $this->action->execute(1, $data);

        // Assert
        $this->assertEquals(65.00, $result['total']);
    }

    public function testExecuteDecrementsStockForEachItem(): void
    {
        // Arrange
        $data = [
            'cart' => [
                ['id' => 10, 'price' => 5.00, 'quantity' => 3],
                ['id' => 20, 'price' => 8.00, 'quantity' => 1]
            ],
            'payments' => [['method' => 'pix', 'amount' => 23.00]]
        ];

        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepo->method('create')->willReturn(789);
        $this->orderRepo->method('updatePayment');
        $this->itemRepo->method('insert');
        $this->paymentService->method('registerPayments');
        $this->cashRegisterService->method('registerMovement');

        // Expect 2 decrements - one for each product
        $this->stockRepo
            ->expects($this->exactly(2))
            ->method('decrement');

        // Act
        $this->action->execute(1, $data);
    }

    public function testUsesOrderStatusConstant(): void
    {
        // Verifica que a constante existe e tem o valor correto
        $this->assertEquals('concluido', OrderStatus::CONCLUIDO);
        $this->assertTrue(OrderStatus::isFinal(OrderStatus::CONCLUIDO));
    }
}
