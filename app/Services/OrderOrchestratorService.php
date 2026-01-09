<?php

namespace App\Services;

use App\Services\Order\CreateOrderAction;
use App\Services\Order\CloseTableAction;
use App\Services\Order\CloseCommandAction;
use App\Services\Order\RemoveItemAction;
use App\Services\Order\CancelOrderAction;
use App\Services\Order\IncludePaidItemsAction;
use App\Services\Order\DeliverOrderAction;

/**
 * Facade para as Ações de Pedido
 * 
 * Este serviço NÃO contém lógica de negócios.
 * Apenas delega para as Actions específicas em App\Services\Order.
 */
class OrderOrchestratorService
{
    private $createOrderAction;
    private $closeTableAction;
    private $closeCommandAction;
    private $removeItemAction;
    private $cancelOrderAction;
    private $includePaidItemsAction;
    private $deliverOrderAction;

    public function __construct(
        CreateOrderAction $createOrderAction,
        CloseTableAction $closeTableAction,
        CloseCommandAction $closeCommandAction,
        RemoveItemAction $removeItemAction,
        CancelOrderAction $cancelOrderAction,
        IncludePaidItemsAction $includePaidItemsAction,
        DeliverOrderAction $deliverOrderAction
    ) {
        $this->createOrderAction = $createOrderAction;
        $this->closeTableAction = $closeTableAction;
        $this->closeCommandAction = $closeCommandAction;
        $this->removeItemAction = $removeItemAction;
        $this->cancelOrderAction = $cancelOrderAction;
        $this->includePaidItemsAction = $includePaidItemsAction;
        $this->deliverOrderAction = $deliverOrderAction;
    }

    public function createOrder(int $restaurantId, int $userId, array $data): int
    {
        return $this->createOrderAction->execute($restaurantId, $userId, $data);
    }

    public function closeTable(int $restaurantId, int $tableId, array $payments): void
    {
        $this->closeTableAction->execute($restaurantId, $tableId, $payments);
    }

    public function closeCommand(int $restaurantId, int $orderId, array $payments): void
    {
        $this->closeCommandAction->execute($restaurantId, $orderId, $payments);
    }

    public function removeItem(int $itemId, int $orderId): void
    {
        $this->removeItemAction->execute($itemId, $orderId);
    }

    public function cancelOrder(int $orderId, ?int $tableId = null): void
    {
        $this->cancelOrderAction->execute($orderId, $tableId);
    }

    public function includePaidItems(int $orderId, array $cart, array $payments, int $restaurantId): float
    {
        return $this->includePaidItemsAction->execute($orderId, $cart, $payments, $restaurantId);
    }

    public function deliverOrder(int $orderId, int $restaurantId): void
    {
        $this->deliverOrderAction->execute($orderId, $restaurantId);
    }
}
