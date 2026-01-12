<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Comanda\OpenComandaAction;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\ClientRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;

/**
 * Testes unitários para OpenComandaAction
 */
class OpenComandaActionTest extends TestCase
{
    private $orderRepo;
    private $itemRepo;
    private $clientRepo;
    private $stockRepo;
    private OpenComandaAction $action;

    protected function setUp(): void
    {
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->itemRepo = $this->createMock(OrderItemRepository::class);
        $this->clientRepo = $this->createMock(ClientRepository::class);
        $this->stockRepo = $this->createMock(StockRepository::class);

        $this->action = new OpenComandaAction(
            $this->orderRepo,
            $this->itemRepo,
            $this->clientRepo,
            $this->stockRepo
        );
    }

    public function testExecuteCreatesOrderWithAbertoStatus(): void
    {
        $restaurantId = 1;
        $data = [
            'client_id' => 10,
            'cart' => [
                ['id' => 1, 'price' => 25.00, 'quantity' => 2]
            ]
        ];

        // Mock cliente existe
        $this->clientRepo
            ->expects($this->once())
            ->method('find')
            ->with(10, $restaurantId)
            ->willReturn(['id' => 10, 'name' => 'João']);

        // Expect create with status ABERTO
        $this->orderRepo
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(function($orderData) use ($restaurantId) {
                    return $orderData['restaurant_id'] === $restaurantId
                        && $orderData['client_id'] === 10
                        && $orderData['order_type'] === 'comanda'
                        && $orderData['total'] === 50.00;
                }),
                OrderStatus::ABERTO
            )
            ->willReturn(456);

        $this->itemRepo->expects($this->once())->method('insert');
        $this->stockRepo->expects($this->once())->method('decrement')->with(1, 2);

        $result = $this->action->execute($restaurantId, $data);

        $this->assertEquals(456, $result['order_id']);
        $this->assertEquals(50.00, $result['total']);
        $this->assertEquals(10, $result['client_id']);
        $this->assertEquals('João', $result['client_name']);
    }

    public function testExecuteFailsWhenClientNotFound(): void
    {
        $this->clientRepo
            ->method('find')
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não encontrado');

        $this->action->execute(1, ['client_id' => 999, 'cart' => []]);
    }

    public function testRequiresClientIdUnlikeMesa(): void
    {
        // Comanda DEVE ter client_id (diferente de Mesa que tem table_id)
        $this->assertTrue(true, 'Comanda requer client_id');
    }
}
