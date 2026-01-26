<?php

namespace App\Services\Order\Flows\Delivery;

use App\Core\Logger;
use App\Repositories\Order\OrderRepository;
use Exception;
use RuntimeException;

/**
 * Action: Atualizar Status de Delivery
 *
 * Fluxo de status delivery:
 * novo → aguardando → em_preparo → pronto → em_entrega → entregue → concluido
 *
 * Estados finais: concluido, cancelado
 */
class UpdateDeliveryStatusAction
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    /**
     * Atualiza status do pedido de delivery
     *
     * @param int $restaurantId ID do restaurante
     * @param array $data Payload validado
     * @return array ['order_id' => int, 'old_status' => string, 'new_status' => string]
     */
    public function execute(int $restaurantId, array $data): array
    {
        $orderId = intval($data['order_id']);
        $newStatus = $data['new_status'];

        // 1. Buscar pedido
        $order = $this->orderRepo->find($orderId, $restaurantId);

        if (!$order) {
            throw new Exception("Pedido #{$orderId} não encontrado");
        }

        // 2. Validar order_type = delivery
        if ($order['order_type'] !== 'delivery') {
            throw new Exception("Pedido #{$orderId} não é delivery");
        }

        $oldStatus = $order['status'];

        // 3. Validar transição (OrderRepository já faz isso, mas log extra)
        Logger::debug("[DELIVERY_STATUS] Tentando transição", [
            'restaurant_id' => $restaurantId,
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);

        try {
            // 4. Atualizar status (OrderRepository valida transições)
            $affected = $this->orderRepo->updateStatus($orderId, $newStatus);

            if ($affected === 0) {
                throw new RuntimeException(
                    "Transição de status não permitida: {$oldStatus} → {$newStatus}"
                );
            }

            Logger::info("[DELIVERY_STATUS] Status atualizado", [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            return [
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'success' => true
            ];

        } catch (\Throwable $e) {
            Logger::error('[DELIVERY_STATUS] ERRO ao atualizar status', [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Erro ao atualizar status: ' . $e->getMessage());
        }
    }
}
