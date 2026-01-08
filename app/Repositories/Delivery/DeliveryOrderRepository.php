<?php

namespace App\Repositories\Delivery;

use App\Core\Database;
use PDO;

/**
 * Repository para Pedidos de Delivery
 * SQL puro extraído do DeliveryController
 */
class DeliveryOrderRepository
{
    /**
     * Busca pedidos de delivery/pickup/local (para Kanban)
     */
    public function fetchByRestaurant(int $restaurantId, ?string $statusFilter = null): array
    {
        $conn = Database::connect();
        
        // Query base - busca APENAS delivery e retirada (Local vai para Mesas/Comandas)
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
        
        $params = [
            'rid' => $restaurantId
        ];
        
        // Filtro por status (se fornecido)
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
        
        // Calcula o dia da semana (0=Dom, 6=Sáb)
        $dayOfWeek = date('w', strtotime($date));
        
        // Busca horário de funcionamento do dia
        $stmtHour = $conn->prepare("
            SELECT * FROM business_hours 
            WHERE restaurant_id = :rid AND day_of_week = :day
        ");
        $stmtHour->execute(['rid' => $restaurantId, 'day' => $dayOfWeek]);
        $businessHour = $stmtHour->fetch(PDO::FETCH_ASSOC);
        
        // Se não encontrou ou está fechado, retorna vazio
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
        
        // Monta os timestamps de início e fim
        $periodStart = $date . ' ' . $openTime . ':00';
        
        // Se fechamento < abertura, fecha no dia seguinte
        if ($closeTime < $openTime) {
            $nextDay = date('Y-m-d', strtotime($date . ' +1 day'));
            $periodEnd = $nextDay . ' ' . $closeTime . ':00';
        } else {
            $periodEnd = $date . ' ' . $closeTime . ':00';
        }
        
        // Busca pedidos do período
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
        
        // Busca pedido com dados do cliente e restaurante
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

        // Busca itens do pedido
        $order['items'] = $this->getItems($id);
        
        return $order;
    }

    /**
     * Busca itens de um pedido
     */
    public function getItems(int $orderId): array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT name, quantity, price 
            FROM order_items 
            WHERE order_id = :oid
        ");
        $stmt->execute(['oid' => $orderId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
