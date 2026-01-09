<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\CreateOrderAction;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\StockRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\TableRepository;

class CreateOrderActionTest extends TestCase
{
    private $paymentService;
    private $cashRegisterService;
    private $stockRepository;
    private $orderRepository;
    private $tableRepository;
    private $action;

    protected function setUp(): void
    {
        $this->paymentService = $this->createMock(PaymentService::class);
        $this->cashRegisterService = $this->createMock(CashRegisterService::class);
        $this->stockRepository = $this->createMock(StockRepository::class);
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->tableRepository = $this->createMock(TableRepository::class);

        $this->action = new CreateOrderAction(
            $this->paymentService,
            $this->cashRegisterService,
            $this->stockRepository,
            $this->orderRepository,
            $this->tableRepository
        );
    }

    public function testExecuteCreatesOrderSuccessfully()
    {
        // Arrange
        $data = [
            'restaurant_id' => 1,
            'table_id' => 10,
            'user_id' => 5,
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'unit_price' => 10, 'total' => 20]
            ],
            'payment_method' => 'dinheiro'
        ];

        // Mocks expectations
        $this->orderRepository->expects($this->once())
            ->method('create')
            ->willReturn(123); // Order ID

        $this->orderRepository->expects($this->once())
            ->method('insertItems');

        $this->stockRepository->expects($this->once())
             ->method('registerMovement');

        // Act
        $result = $this->action->execute($data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['order_id']);
    }

    public function testExecuteFailsValidation()
    {
        // Arrange
        $data = []; // Empty data

        // Act
        // Assuming validation happens inside or before. 
        // If strict typing catches it, this might throw TypeError. 
        // Based on previous code, let's see.
        // The Service usually does minimal validation or assumes Validator ran before.
        // Let's assume the controller passed valid data structure but maybe logic fails.
        
        // For now, just simplistic test.
        $this->markTestIncomplete('Validation test pending implementation detail check');
    }
}
