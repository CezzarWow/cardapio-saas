<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Repositories\Order\OrderRepository;
use Exception;

class DeliverOrderAction
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function execute(int $orderId, int $restaurantId): void
    {
        try {
            $order = $this->orderRepo->find($orderId, $restaurantId);
            if (!$order) throw new Exception('Pedido não encontrado');

            $this->orderRepo->updateStatus($orderId, 'concluido');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
