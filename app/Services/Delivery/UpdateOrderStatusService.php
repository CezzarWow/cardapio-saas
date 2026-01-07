<?php

namespace App\Services\Delivery;

use App\Repositories\Delivery\DeliveryOrderRepository;

/**
 * Service para atualizar status de pedidos Delivery
 * Contém regras de transição de status
 */
class UpdateOrderStatusService
{
    private DeliveryOrderRepository $repository;

    /**
     * Transições permitidas de status
     */
    private const TRANSITIONS = [
        'novo'    => ['preparo', 'cancelado'],
        'preparo' => ['rota', 'cancelado'],
        'rota'    => ['entregue', 'cancelado'],
    ];

    public function __construct()
    {
        $this->repository = new DeliveryOrderRepository();
    }

    /**
     * Atualiza status do pedido com validação de transição
     * 
     * @return array ['success' => bool, 'message' => string, 'new_status' => ?string]
     */
    public function execute(int $orderId, string $newStatus, int $restaurantId): array
    {
        // Busca pedido
        $order = $this->repository->findById($orderId, $restaurantId);

        if (!$order) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        if (!in_array($order['order_type'], ['delivery', 'pickup'])) {
            return ['success' => false, 'message' => 'Este pedido não é delivery/retirada'];
        }

        $currentStatus = $order['status'];

        // Valida transição permitida
        $allowed = self::TRANSITIONS[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowed)) {
            return [
                'success' => false, 
                'message' => "Transição não permitida: {$currentStatus} → {$newStatus}"
            ];
        }

        // Executa UPDATE
        $this->repository->updateStatus($orderId, $newStatus);

        return [
            'success' => true, 
            'message' => 'Status atualizado',
            'new_status' => $newStatus
        ];
    }
}
