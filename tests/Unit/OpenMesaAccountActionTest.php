<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Mesa\OpenMesaAccountAction;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\TableRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;

/**
 * Testes unitários para OpenMesaAccountAction
 */
class OpenMesaAccountActionTest extends TestCase
{
    private $orderRepo;
    private $itemRepo;
    private $tableRepo;
    private $stockRepo;
    private OpenMesaAccountAction $action;

    protected function setUp(): void
    {
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->itemRepo = $this->createMock(OrderItemRepository::class);
        $this->tableRepo = $this->createMock(TableRepository::class);
        $this->stockRepo = $this->createMock(StockRepository::class);

        $this->action = new OpenMesaAccountAction(
            $this->orderRepo,
            $this->itemRepo,
            $this->tableRepo,
            $this->stockRepo
        );
    }

    public function testExecuteCreatesOrderWithAbertoStatus(): void
    {
        // Arrange
        $restaurantId = 1;
        $data = [
            'table_id' => 5,
            'cart' => [
                ['id' => 1, 'price' => 25.00, 'quantity' => 2]
            ]
        ];

        // Mock mesa disponível
        $this->tableRepo
            ->expects($this->once())
            ->method('findWithCurrentOrder')
            ->with(5, $restaurantId)
            ->willReturn(['id' => 5, 'number' => 5, 'current_order_id' => null]);

        // Expect create with status ABERTO
        $this->orderRepo
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(function($orderData) use ($restaurantId) {
                    return $orderData['restaurant_id'] === $restaurantId
                        && $orderData['order_type'] === 'mesa'
                        && $orderData['total'] === 50.00;
                }),
                OrderStatus::ABERTO
            )
            ->willReturn(123);

        // Expect table occupy
        $this->tableRepo
            ->expects($this->once())
            ->method('occupy')
            ->with(5, 123);

        // Expect items insert
        $this->itemRepo
            ->expects($this->once())
            ->method('insert');

        // Expect stock decrement
        $this->stockRepo
            ->expects($this->once())
            ->method('decrement')
            ->with(1, 2);

        // Act
        $result = $this->action->execute($restaurantId, $data);

        // Assert
        $this->assertEquals(123, $result['order_id']);
        $this->assertEquals(50.00, $result['total']);
        $this->assertEquals(5, $result['table_id']);
    }

    public function testExecuteFailsWhenTableNotFound(): void
    {
        $this->tableRepo
            ->method('findWithCurrentOrder')
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não encontrada');

        $this->action->execute(1, ['table_id' => 999, 'cart' => []]);
    }

    public function testExecuteFailsWhenTableOccupied(): void
    {
        $this->tableRepo
            ->method('findWithCurrentOrder')
            ->willReturn(['id' => 5, 'number' => 5, 'current_order_id' => 100]); // Mesa ocupada

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ocupada');

        $this->action->execute(1, ['table_id' => 5, 'cart' => []]);
    }

    public function testUsesOrderStatusConstant(): void
    {
        $this->assertEquals('aberto', OrderStatus::ABERTO);
    }
}
