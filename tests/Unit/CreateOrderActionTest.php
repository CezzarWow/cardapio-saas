<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\CreateOrderAction;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\StockRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\TableRepository;
use App\Repositories\ClientRepository;

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

    public function testExecuteCreatesOrderSuccessfully()
    {
        // Arrange
        $data = [
            'restaurant_id' => 1,
            'table_id' => 10,
            'user_id' => 5,
            'cart' => [
                ['id' => 1, 'product_id' => 1, 'quantity' => 2, 'price' => 10]
            ],
            'payment_method' => 'dinheiro'
        ];

        // Mock caixa aberto
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        // Mocks expectations
        $this->orderRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn(123);

        $this->orderRepository
            ->expects($this->once())
            ->method('updateOrderType');

        $this->itemRepository
            ->expects($this->once())
            ->method('insert');

        $this->stockRepository
            ->expects($this->atLeastOnce())
            ->method('decrement');

        // Act
        $result = $this->action->execute(1, 5, $data);

        // Assert
        $this->assertEquals(123, $result);
    }

    public function testExecuteFailsValidation()
    {
        // Arrange
        $data = []; // Empty cart

        // Mock caixa aberto
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        // Assert - should throw exception for empty cart
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('O carrinho está vazio');

        // Act
        $this->action->execute(1, 5, $data);
    }
}

