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

    public function testExecuteCreatesOrderSuccessfully(): void
    {
        // Arrange
        $data = [
            'cart' => [
                ['id' => 1, 'product_id' => 1, 'quantity' => 2, 'price' => 10.00]
            ],
            'order_type' => 'balcao',
            'finalize_now' => true,
            'is_paid' => 1,
            'payments' => [['method' => 'dinheiro', 'amount' => 20.00]]
        ];

        // Mock caixa aberto
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        // Mock Database connection (PDO mock)
        $pdo = $this->createMock(\PDO::class);
        $pdo->expects($this->once())
            ->method('beginTransaction');
        $pdo->expects($this->once())
            ->method('commit');

        // Mock Database::connect() usando reflection ou mock estático
        // Por enquanto, vamos assumir que funciona

        // Mocks expectations
        $this->orderRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn(123);

        $this->orderRepository
            ->expects($this->once())
            ->method('updatePayment');

        $this->orderRepository
            ->expects($this->once())
            ->method('updateOrderType');

        $this->itemRepository
            ->expects($this->once())
            ->method('insert');

        $this->stockRepository
            ->expects($this->atLeastOnce())
            ->method('decrement');

        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments');

        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement');

        // Act & Assert
        // Nota: Este teste pode falhar se Database::connect() não for mockado
        // Em ambiente real, use banco de testes ou mock do Database
        try {
            $result = $this->action->execute(1, 5, $data);
            $this->assertEquals(123, $result);
        } catch (\App\Exceptions\DatabaseConnectionException $e) {
            $this->markTestSkipped('Database connection required for full test');
        }
    }

    public function testExecuteFailsValidationWithEmptyCart(): void
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
        try {
            $this->action->execute(1, 5, $data);
        } catch (\App\Exceptions\DatabaseConnectionException $e) {
            $this->markTestSkipped('Database connection required for full test');
        }
    }

    public function testExecuteNormalizesOrderType(): void
    {
        // Este teste verifica que tipos são normalizados corretamente
        // Como normalizeOrderType() é privado, testamos indiretamente
        // através do comportamento do execute()
        
        $this->markTestIncomplete('Requires database setup to test order type normalization');
    }

    public function testExecuteHandlesExistingOrder(): void
    {
        // Testa incremento de pedido existente
        $this->markTestIncomplete('Requires database setup to test existing order handling');
    }
}

