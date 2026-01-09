<?php
namespace App\Services\Delivery;

use App\Repositories\Delivery\DeliveryOrderRepository;

/**
 * DeliveryService - Lógica de Negócio de Delivery/Pedidos
 * Unifica consultas, atualização de status e envio para mesas
 */
class DeliveryService
{
    private DeliveryOrderRepository $repository;

    /**
     * Transições permitidas de status (Regra de Negócio)
     */
    private const TRANSITIONS = [
        'novo'    => ['preparo', 'cancelado'],
        'preparo' => ['rota', 'cancelado'],
        'rota'    => ['entregue', 'cancelado'],
        // Adicionei suporte reverso opcional ou fluxo simplificado se necessário depois
    ];

    public function __construct(DeliveryOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    // ====================================================
    // CONSULTAS (QUERIES)
    // ====================================================

    /**
     * Busca pedidos para o Kanban
     */
    public function getOrders(int $restaurantId, ?string $statusFilter = null): array
    {
        return $this->repository->fetchByRestaurant($restaurantId, $statusFilter);
    }

    /**
     * Busca histórico por dia operacional
     */
    public function getOrdersByOperationalDay(int $restaurantId, string $date): array
    {
        return $this->repository->fetchByOperationalDay($restaurantId, $date);
    }

    /**
     * Detalhes do pedido para modal
     */
    public function getOrderDetails(int $orderId, int $restaurantId): ?array
    {
        $order = $this->repository->findWithDetails($orderId, $restaurantId);
        
        if (!$order) {
            return null;
        }

        // Separa items para manter compatibilidade com front
        $items = $order['items'] ?? [];
        unset($order['items']);
        
        return [
            'order' => $order,
            'items' => $items
        ];
    }

    // ====================================================
    // AÇÕES (COMMANDS)
    // ====================================================

    /**
     * Atualiza status com validação de transição
     */
    public function updateStatus(int $orderId, string $newStatus, int $restaurantId): array
    {
        $order = $this->repository->findById($orderId, $restaurantId);

        if (!$order) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        if (!in_array($order['order_type'], ['delivery', 'pickup'])) {
            return ['success' => false, 'message' => 'Este pedido não é delivery/retirada'];
        }

        $currentStatus = $order['status'];
        
        // Permite "forçar" update se for admin? Por enquanto regra estrita.
        // Se status igual, retorna ok
        if ($currentStatus === $newStatus) {
            return ['success' => true, 'message' => 'Status já atualizado', 'new_status' => $newStatus];
        }

        $allowed = self::TRANSITIONS[$currentStatus] ?? [];
        
        // Permite atualizações livres se o status atual não estiver mapeado (ex: entregue)
        // Mas se estiver, exige seguir o fluxo.
        // E 'cancelado' é terminal?
        if (isset(self::TRANSITIONS[$currentStatus]) && !in_array($newStatus, $allowed)) {
            return [
                'success' => false, 
                'message' => "Transição inválida: {$currentStatus} → {$newStatus}"
            ];
        }

        $this->repository->updateStatus($orderId, $newStatus);

        return [
            'success' => true, 
            'message' => 'Status atualizado com sucesso',
            'new_status' => $newStatus
        ];
    }

    /**
     * Envia pedido Local para Mesas
     */
    public function sendToTable(int $orderId, int $restaurantId): array
    {
        $order = $this->repository->findById($orderId, $restaurantId);

        if (!$order) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        if ($order['order_type'] !== 'local') {
            return ['success' => false, 'message' => 'Este pedido não é do tipo Local'];
        }

        if ($order['status'] !== 'novo') {
            return ['success' => false, 'message' => 'Pedido já foi enviado ou processado'];
        }

        // 'aberto' faz aparecer na aba Mesas
        $this->repository->updateStatus($orderId, 'aberto');

        return [
            'success' => true, 
            'message' => 'Pedido enviado para Mesas com sucesso'
        ];
    }
}
