<?php

namespace App\Services;

use App\Services\Order\CreateOrderAction;
use App\Services\Order\CloseTableAction;
use App\Services\Order\CloseCommandAction;
use App\Services\Order\RemoveItemAction;
use App\Services\Order\CancelOrderAction;
use App\Services\Order\IncludePaidItemsAction;
use App\Services\Order\DeliverOrderAction;
use App\Services\Order\CreateDeliveryLinkedAction;

use App\Repositories\Order\OrderRepository;

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
    private $createDeliveryLinkedAction;
    private $orderRepository;

    public function __construct(
        CreateOrderAction $createOrderAction,
        CloseTableAction $closeTableAction,
        CloseCommandAction $closeCommandAction,
        RemoveItemAction $removeItemAction,
        CancelOrderAction $cancelOrderAction,
        IncludePaidItemsAction $includePaidItemsAction,
        DeliverOrderAction $deliverOrderAction,
        CreateDeliveryLinkedAction $createDeliveryLinkedAction,
        OrderRepository $orderRepository
    ) {
        $this->createOrderAction = $createOrderAction;
        $this->closeTableAction = $closeTableAction;
        $this->closeCommandAction = $closeCommandAction;
        $this->removeItemAction = $removeItemAction;
        $this->cancelOrderAction = $cancelOrderAction;
        $this->includePaidItemsAction = $includePaidItemsAction;
        $this->deliverOrderAction = $deliverOrderAction;
        $this->createDeliveryLinkedAction = $createDeliveryLinkedAction;
        $this->orderRepository = $orderRepository;
    }

    public function createOrder(int $restaurantId, int $userId, array $data): int
    {
        error_log("[DEBUG Orchestrator] createOrder called. Data keys: " . implode(',', array_keys($data)));
        error_log("[DEBUG Orchestrator] link_to_comanda=" . ($data['link_to_comanda'] ?? 'NULL') . ", client_id=" . ($data['client_id'] ?? 'NULL'));
        error_log("[DEBUG Orchestrator] link_to_table=" . ($data['link_to_table'] ?? 'NULL') . ", table_id=" . ($data['table_id'] ?? 'NULL'));

        // FASE 1: Backend Defensivo para Delivery
        // Se for delivery e tiver cliente, verifica se tem comanda aberta.
        // Se tiver, força o vínculo, independente de flag do frontend.
        if (($data['order_type'] ?? '') === 'delivery' && !empty($data['client_id'])) {
            // Hotfix Agressivo: Se tem cliente, assume intenção de vincular/criar comanda.
            // Isso resolve o problema de visualização "itens soltos" imediatamente.
            // Risco: Delivery "Pagar na Entrega" vai abrir uma comanda que precisará ser fechada.
            // Aceitável para resolver o bloqueio atual.
            error_log("[DEBUG Orchestrator] DELIVERY DEFENSIVE: Client {$data['client_id']} present. Forcing Linked Action.");
            $data['link_to_comanda'] = true; 
            return $this->createDeliveryLinkedAction->execute($restaurantId, $userId, $data);
        }

        // Delivery vinculado a Mesa ou Comanda (Lógica Original / Fallback)
        $linkToTable = !empty($data['link_to_table']) && !empty($data['table_id']);
        $linkToComanda = !empty($data['link_to_comanda']) && !empty($data['client_id']);
        
        if ($linkToTable || $linkToComanda) {
            error_log("[DEBUG Orchestrator] Routing to CreateDeliveryLinkedAction (Explicit Flag)");
            return $this->createDeliveryLinkedAction->execute($restaurantId, $userId, $data);
        }
        
        error_log("[DEBUG Orchestrator] Routing to CreateOrderAction");
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
