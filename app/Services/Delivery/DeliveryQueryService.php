<?php

namespace App\Services\Delivery;

use App\Repositories\Delivery\DeliveryOrderRepository;

/**
 * Query Service para consultas de pedidos Delivery
 */
class DeliveryQueryService
{
    private DeliveryOrderRepository $repository;

    public function __construct()
    {
        $this->repository = new DeliveryOrderRepository();
    }

    /**
     * Retorna pedidos para o Kanban
     */
    public function getOrders(int $restaurantId, ?string $statusFilter = null): array
    {
        return $this->repository->fetchByRestaurant($restaurantId, $statusFilter);
    }

    /**
     * Retorna pedidos por dia operacional (para histórico)
     */
    public function getOrdersByOperationalDay(int $restaurantId, string $date): array
    {
        return $this->repository->fetchByOperationalDay($restaurantId, $date);
    }

    /**
     * Retorna detalhes completos de um pedido (para modal/impressão)
     */
    public function getOrderDetails(int $orderId, int $restaurantId): ?array
    {
        $order = $this->repository->findWithDetails($orderId, $restaurantId);
        
        if (!$order) {
            return null;
        }

        // Separa items do order para compatibilidade com API existente
        $items = $order['items'] ?? [];
        unset($order['items']);
        
        return [
            'order' => $order,
            'items' => $items
        ];
    }
}
