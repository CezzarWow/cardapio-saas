<?php

namespace App\Services;

use App\Repositories\Order\OrderRepository;
use App\Services\Order\CancelOrderAction;
use App\Services\Order\CloseCommandAction;
use App\Services\Order\CloseTableAction;
use App\Services\Order\CreateDeliveryLinkedAction;
use App\Services\Order\CreateOrderAction;
use App\Services\Order\DeliverOrderAction;
use App\Services\Order\IncludePaidItemsAction;
use App\Services\Order\RemoveItemAction;

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
        // LÓGICA DE DELIVERY/PICKUP COM CLIENTE/MESA:
        // - NÃO PAGO + cliente/mesa: Cria comanda (para cobrar depois) e vai pro Kanban
        // - PAGO: Vai direto pro Kanban (não precisa comanda)
        // [FIX] Prioridade de Atualização
        // Se já existe um ID de pedido, trata como ATUALIZAÇÃO (Update)
        // Isso impede que o sistema tente criar um novo pedido vinculado (splitting)
        // quando estamos apenas mudando o tipo de um pedido existente (ex: Retirada -> Entrega).
        $existingOrderId = $data['order_id'] ?? null;
        if ($existingOrderId && $existingOrderId > 0) {
            return $this->createOrderAction->execute($restaurantId, $userId, $data);
        }



        // LÓGICA DE DELIVERY/PICKUP COM CLIENTE/MESA:
        // - NÃO PAGO + cliente/mesa: Cria comanda (para cobrar depois) e vai pro Kanban
        // - PAGO: Vai direto pro Kanban (não precisa comanda)
        $orderType = $data['order_type'] ?? '';
        $isDeliveryOrPickup = in_array($orderType, ['delivery', 'pickup']);
        $hasClient = !empty($data['client_id']);
        $hasTable = !empty($data['table_id']);
        $isPaid = !empty($data['payments']) || (isset($data['is_paid']) && $data['is_paid'] == 1);

        if ($isDeliveryOrPickup && ($hasClient || $hasTable) && !$isPaid) {
            
            // [FIX] Detecta Balcão Pickup OU Delivery Simples (Com Cliente)
            // Se for Retirada OU Entrega com Cliente (sem mesa), não deve duplicar (criar linked).
            // Deve cair no fluxo padrão de CreateOrderAction para atualizar/criar pedido único.
            $isSimpleOrder = (($orderType === 'pickup' || $orderType === 'delivery') && $hasClient && !$hasTable);

            if (!$isSimpleOrder) {
                // Delivery/Pickup vinculado a MESA -> Mantém lógica de criar comanda separada
                if ($hasTable) {
                    $data['link_to_table'] = true;
                } else {
                    $data['link_to_comanda'] = true;
                }
                return $this->createDeliveryLinkedAction->execute($restaurantId, $userId, $data);
            }
        }

        // Delivery/Pickup vinculado a Mesa ou Comanda (apenas se EXPLICITAMENTE solicitado)
        $linkToTable = !empty($data['link_to_table']) && !empty($data['table_id']);
        $linkToComanda = !empty($data['link_to_comanda']) && !empty($data['client_id']);

        if ($linkToTable || $linkToComanda) {
            return $this->createDeliveryLinkedAction->execute($restaurantId, $userId, $data);
        }

        return $this->createOrderAction->execute($restaurantId, $userId, $data);
    }

    public function closeTable(int $restaurantId, int $tableId, array $payments): int
    {
        return $this->closeTableAction->execute($restaurantId, $tableId, $payments);
    }

    public function closeCommand(int $restaurantId, int $orderId, array $payments): int
    {
        return $this->closeCommandAction->execute($restaurantId, $orderId, $payments);
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
