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
use PHPUnit\Framework\TestCase;

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
            ->willReturn(['id' => 1]);

        // Note: this unit test does NOT mock Database::connect() (static), so it cannot reliably
        // assert beginTransaction/commit. Those behaviors are better covered by integration tests.

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

        try {
            $result = $this->action->execute(1, 5, $data);
            $this->assertEquals(123, $result);
        } catch (\App\Exceptions\DatabaseConnectionException $e) {
            $this->markTestSkipped('Database connection required for this test');
        }
    }

    public function testExecuteFailsValidationWithEmptyCart(): void
    {
        $data = [];

        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('O carrinho');

        try {
            $this->action->execute(1, 5, $data);
        } catch (\App\Exceptions\DatabaseConnectionException $e) {
            $this->markTestSkipped('Database connection required for this test');
        }
    }

    public function testExecuteNormalizesOrderType(): void
    {
        $this->markTestIncomplete('Requires database setup to test order type normalization');
    }

    public function testExecuteHandlesExistingOrder(): void
    {
        $this->markTestIncomplete('Requires database setup to test existing order handling');
    }
}
