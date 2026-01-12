<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Delivery\CreateDeliveryStandaloneAction;
use App\Services\PaymentService;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\ClientRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;

/**
 * Testes unitários para CreateDeliveryStandaloneAction
 */
class CreateDeliveryStandaloneActionTest extends TestCase
{
    private $paymentService;
    private $orderRepo;
    private $itemRepo;
    private $clientRepo;
    private $stockRepo;
    private CreateDeliveryStandaloneAction $action;

    protected function setUp(): void
    {
        $this->paymentService = $this->createMock(PaymentService::class);
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->itemRepo = $this->createMock(OrderItemRepository::class);
        $this->clientRepo = $this->createMock(ClientRepository::class);
        $this->stockRepo = $this->createMock(StockRepository::class);

        $this->action = new CreateDeliveryStandaloneAction(
            $this->paymentService,
            $this->orderRepo,
            $this->itemRepo,
            $this->clientRepo,
            $this->stockRepo
        );
    }

    public function testExecuteCreatesDeliveryWithNovoStatusWhenUnpaid(): void
    {
        $restaurantId = 1;
        $data = [
            'client_name' => 'João',
            'address' => 'Rua Teste',
            'cart' => [
                ['id' => 1, 'price' => 20.00, 'quantity' => 1]
            ],
            'delivery_fee' => 5.00
        ];

        // Mock Find/Create Client
        $this->clientRepo
            ->method('create')
            ->willReturn(10);

        // Expect create with status NOVO (sem pagamento)
        $this->orderRepo
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(function($orderData) use ($restaurantId) {
                    return $orderData['restaurant_id'] === $restaurantId
                        && $orderData['client_id'] === 10
                        && $orderData['order_type'] === 'delivery'
                        && $orderData['total'] === 25.00; // 20 + 5
                }),
                OrderStatus::NOVO
            )
            ->willReturn(789);

        $this->itemRepo->expects($this->once())->method('insert');
        $this->stockRepo->expects($this->once())->method('decrement');
        
        // Não deve registrar pagamento
        $this->paymentService->expects($this->never())->method('registerPayments');

        $result = $this->action->execute($restaurantId, $data);

        $this->assertEquals(789, $result['order_id']);
        $this->assertEquals(OrderStatus::NOVO, $result['status']);
        $this->assertFalse($result['is_paid']);
    }

    public function testExecuteCreatesDeliveryWithAguardandoStatusWhenPaid(): void
    {
        $restaurantId = 1;
        $data = [
            'client_name' => 'Maria',
            'address' => 'Rua Teste 2',
            'cart' => [
                ['id' => 1, 'price' => 50.00, 'quantity' => 1]
            ],
            'payments' => [['method' => 'pix', 'amount' => 50.00]]
        ];

        $this->clientRepo->method('create')->willReturn(11);

        // Expect create with status AGUARDANDO (pago)
        $this->orderRepo
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->anything(),
                OrderStatus::AGUARDANDO
            )
            ->willReturn(790);

        // Deve registrar pagamento
        $this->paymentService->expects($this->once())->method('registerPayments');
        $this->orderRepo->expects($this->once())->method('updatePayment')->with(790, true, 'pix');

        $result = $this->action->execute($restaurantId, $data);

        $this->assertEquals(790, $result['order_id']);
        $this->assertEquals(OrderStatus::AGUARDANDO, $result['status']);
        $this->assertTrue($result['is_paid']);
    }

    public function testExecuteUsesExistingClientByPhone(): void
    {
        $restaurantId = 1;
        $data = [
            'phone' => '11999999999',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'address' => 'Rua X'
        ];

        $this->clientRepo
            ->expects($this->once())
            ->method('findByPhone')
            ->with($restaurantId, '11999999999')
            ->willReturn(['id' => 55, 'name' => 'User Existente']);
            
        $this->clientRepo->expects($this->never())->method('create');

        $this->orderRepo->method('create')->willReturn(100);

        $result = $this->action->execute($restaurantId, $data);

        $this->assertEquals(55, $result['client_id']);
    }
}
