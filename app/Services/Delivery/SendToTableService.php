<?php

namespace App\Services\Delivery;

use App\Repositories\Delivery\DeliveryOrderRepository;

/**
 * Service para enviar pedido Local para a aba Mesas
 * Muda status de 'novo' para 'aberto'
 */
class SendToTableService
{
    private DeliveryOrderRepository $repository;

    public function __construct()
    {
        $this->repository = new DeliveryOrderRepository();
    }

    /**
     * Envia pedido Local para a aba Mesas
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public function execute(int $orderId, int $restaurantId): array
    {
        // Busca pedido
        $order = $this->repository->findById($orderId, $restaurantId);

        if (!$order) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        if ($order['order_type'] !== 'local') {
            return ['success' => false, 'message' => 'Este pedido não é do tipo Local'];
        }

        if ($order['status'] !== 'novo') {
            return ['success' => false, 'message' => 'Pedido já foi enviado para mesa'];
        }

        // Muda status para 'aberto' - isso faz aparecer na aba Mesas > Clientes/Comanda
        $this->repository->updateStatus($orderId, 'aberto');

        return [
            'success' => true, 
            'message' => 'Pedido enviado para Mesas'
        ];
    }
}
