<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Repositories\Order\OrderRepository;
use Exception;
use RuntimeException;

/**
 * Marca pedido como entregue/concluído.
 * 
 * Usado para delivery já pago ou balcão.
 */
class DeliverOrderAction
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    /**
     * Executa a entrega do pedido.
     * 
     * @param int $orderId ID do pedido
     * @param int $restaurantId ID do restaurante
     * @throws Exception Se pedido não encontrado
     * @throws RuntimeException Se updateStatus não afetar linhas
     */
    public function execute(int $orderId, int $restaurantId): void
    {
        $order = $this->orderRepo->find($orderId, $restaurantId);
        
        if (!$order) {
            throw new Exception('Pedido não encontrado');
        }

        $affected = $this->orderRepo->updateStatus($orderId, 'concluido');
        
        if ($affected === 0) {
            throw new RuntimeException(
                "updateStatus affected 0 rows for orderId: {$orderId}"
            );
        }
        
        error_log("[DELIVER_ORDER] Pedido #{$orderId} status: concluido");
    }
}

