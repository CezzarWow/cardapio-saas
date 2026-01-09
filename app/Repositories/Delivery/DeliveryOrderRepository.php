<?php

namespace App\Repositories\Delivery;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use PDO;

/**
 * Repository para Pedidos de Delivery
 * SQL puro extraído do DeliveryController
 */
class DeliveryOrderRepository
{
    private OrderItemRepository $itemRepo;

    public function __construct(OrderItemRepository $itemRepo)
    {
        $this->itemRepo = $itemRepo;
    }

    /**
     * Busca pedidos de delivery/pickup/local (para Kanban)
     */
    public function fetchByRestaurant(int $restaurantId, ?string $statusFilter = null): array
    {
        $conn = Database::connect();
        
        $sql = "
            SELECT o.id, o.total, o.status, o.created_at, o.payment_method, o.order_type, o.is_paid,
                   c.name as client_name, 
                   c.phone as client_phone,
                   c.address as client_address,
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
            FROM orders o
            LEFT JOIN clients c ON o.client_id = c.id
            WHERE o.restaurant_id = :rid 
              AND o.order_type IN ('delivery', 'pickup')
        ";
        
        $params = ['rid' => $restaurantId];
        
        $validStatuses = ['novo', 'preparo', 'rota', 'entregue', 'cancelado'];
        if ($statusFilter && in_array($statusFilter, $validStatuses)) {
            $sql .= " AND o.status = :status";
            $params['status'] = $statusFilter;
        }
        
        $sql .= " ORDER BY 
            CASE o.status 
                WHEN 'novo' THEN 1 
                WHEN 'preparo' THEN 2 
                WHEN 'rota' THEN 3 
                WHEN 'entregue' THEN 4 
                WHEN 'cancelado' THEN 5 
            END,
            o.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca pedidos por dia operacional (respeitando horário de funcionamento)
     */
    public function fetchByOperationalDay(int $restaurantId, string $date): array
    {
        $conn = Database::connect();
        
        $dayOfWeek = date('w', strtotime($date));
        
        $stmtHour = $conn->prepare("
            SELECT * FROM business_hours 
            WHERE restaurant_id = :rid AND day_of_week = :day
        ");
        $stmtHour->execute(['rid' => $restaurantId, 'day' => $dayOfWeek]);
        $businessHour = $stmtHour->fetch(PDO::FETCH_ASSOC);
        
        if (!$businessHour || !$businessHour['is_open']) {
            return [
                'orders' => [],
                'business_hour' => $businessHour,
                'period_start' => null,
                'period_end' => null
            ];
        }
        
        $openTime = $businessHour['open_time'];
        $closeTime = $businessHour['close_time'];
        
        $periodStart = $date . ' ' . $openTime . ':00';
        
        if ($closeTime < $openTime) {
            $nextDay = date('Y-m-d', strtotime($date . ' +1 day'));
            $periodEnd = $nextDay . ' ' . $closeTime . ':00';
        } else {
            $periodEnd = $date . ' ' . $closeTime . ':00';
        }
        
        $sql = "
            SELECT o.id, o.total, o.status, o.created_at, o.payment_method,
                   c.name as client_name, 
                   c.phone as client_phone
            FROM orders o
            LEFT JOIN clients c ON o.client_id = c.id
            WHERE o.restaurant_id = :rid 
              AND o.order_type = 'delivery'
              AND o.created_at >= :start
              AND o.created_at < :end
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'rid' => $restaurantId,
            'start' => $periodStart,
            'end' => $periodEnd
        ]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'orders' => $orders,
            'business_hour' => $businessHour,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ];
    }

    /**
     * Busca pedido por ID com dados básicos
     */
    public function findById(int $id, int $restaurantId): ?array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT id, status, order_type 
            FROM orders 
            WHERE id = :oid AND restaurant_id = :rid
        ");
        $stmt->execute(['oid' => $id, 'rid' => $restaurantId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca pedido com todos os detalhes (para modal e impressão)
     */
    public function findWithDetails(int $id, int $restaurantId): ?array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT o.*, 
                   c.name as client_name, 
                   c.phone as client_phone,
                   c.address as client_address,
                   c.address_number as client_number,
                   c.neighborhood as client_neighborhood,
                   r.name as restaurant_name,
                   r.phone as restaurant_phone
            FROM orders o
            LEFT JOIN clients c ON o.client_id = c.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            WHERE o.id = :oid AND o.restaurant_id = :rid
        ");
        $stmt->execute(['oid' => $id, 'rid' => $restaurantId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // Usa OrderItemRepository injetado
        $order['items'] = $this->itemRepo->findAll($id);
        
        return $order;
    }

    /**
     * Atualiza status do pedido
     */
    public function updateStatus(int $id, string $status): void
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :oid");
        $stmt->execute(['status' => $status, 'oid' => $id]);
    }
}
