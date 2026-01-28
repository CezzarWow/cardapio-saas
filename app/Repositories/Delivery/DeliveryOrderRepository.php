<?php

namespace App\Repositories\Delivery;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use PDO;

/**
 * Repositorio dedicado aos pedidos exibidos nas telas de Delivery/Kanban.
 * Centraliza consultas otimizadas para status, historico e detalhes.
 */
class DeliveryOrderRepository
{
    private OrderItemRepository $orderItems;

    /** Status que aparecem no Kanban/Historico */
    private const ALLOWED_STATUSES = ['novo', 'preparo', 'rota', 'entregue', 'cancelado'];

    /** Tipos de pedido contemplados no modulo */
    private const ORDER_TYPES = ['delivery', 'pickup', 'retirada', 'local'];

    /** Tipos de items que compõem o total do delivery */
    private const DELIVERY_SOURCES = ['delivery', 'pickup', 'retirada', 'entrega'];

    public function __construct(OrderItemRepository $orderItems)
    {
        $this->orderItems = $orderItems;
    }

    /**
     * Lista pedidos para o Kanban, com filtro opcional de status.
     */
    public function fetchByRestaurant(int $restaurantId, ?string $statusFilter = null): array
    {
        $conn = Database::connect();

        $params = ['rid' => $restaurantId];
        $statusSql = '';

        if ($statusFilter && $statusFilter !== 'todos' && in_array($statusFilter, self::ALLOWED_STATUSES, true)) {
            $statusSql = ' AND o.status = :status';
            $params['status'] = $statusFilter;
        } else {
            $placeholders = "'" . implode("','", self::ALLOWED_STATUSES) . "'";
            $statusSql = " AND o.status IN ({$placeholders})";
        }

        $deliverySources = "'" . implode("','", self::DELIVERY_SOURCES) . "'";

        $sql = "
            SELECT o.id, o.restaurant_id, o.client_id, o.table_id,
                   COALESCE(o.total_delivery, 0) as total,
                   o.status, o.order_type, o.payment_method, o.is_paid, o.created_at,
                   c.name AS client_name, c.phone AS client_phone,
                   c.address AS client_address, c.address_number AS client_number,
                   c.neighborhood AS client_neighborhood, c.city AS client_city,
                   t.number AS table_number
            FROM orders o
            LEFT JOIN clients c ON c.id = o.client_id
            LEFT JOIN tables t ON t.id = o.table_id
            WHERE o.restaurant_id = :rid
              AND o.order_type IN ('delivery','pickup','retirada','local')
              {$statusSql}
            ORDER BY o.created_at DESC, o.id DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrateAddress'], $rows);
    }

    /**
     * Lista pedidos de um dia específico (histórico).
     */
    public function fetchByOperationalDay(int $restaurantId, string $date): array
    {
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';

        $conn = Database::connect();
        $deliverySources = "'" . implode("','", self::DELIVERY_SOURCES) . "'";

        $stmt = $conn->prepare("
            SELECT o.id, o.restaurant_id, o.client_id, o.table_id,
                   COALESCE(o.total_delivery, 0) as total,
                   o.status, o.order_type, o.payment_method, o.is_paid, o.created_at,
                   c.name AS client_name, c.phone AS client_phone,
                   c.address AS client_address, c.address_number AS client_number,
                   c.neighborhood AS client_neighborhood, c.city AS client_city,
                   t.number AS table_number
            FROM orders o
            LEFT JOIN clients c ON c.id = o.client_id
            LEFT JOIN tables t ON t.id = o.table_id
            WHERE o.restaurant_id = :rid
              AND o.order_type IN ('delivery','pickup','retirada','local')
              AND o.created_at BETWEEN :start AND :end
            ORDER BY o.created_at DESC, o.id DESC
        ");
        $stmt->execute(['rid' => $restaurantId, 'start' => $start, 'end' => $end]);

        $orders = array_map([$this, 'hydrateAddress'], $stmt->fetchAll(PDO::FETCH_ASSOC));

        return [
            'orders' => $orders,
            'business_hour' => null,
            'period_start' => $start,
            'period_end' => $end,
        ];
    }

    /**
     * Busca pedido com detalhes e itens.
     */
    public function findWithDetails(int $orderId, int $restaurantId): ?array
    {
        $order = $this->findById($orderId, $restaurantId);
        if (!$order) {
            return null;
        }

        $order['items'] = $this->orderItems->findAll($orderId);
        return $order;
    }

    /**
     * Busca pedido básico (sem itens).
     */
    public function findById(int $orderId, int $restaurantId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT o.id, o.restaurant_id, o.client_id, o.table_id,
                   o.total, o.status, o.order_type, o.payment_method, o.is_paid, o.created_at,
                   c.name AS client_name, c.phone AS client_phone,
                   c.address AS client_address, c.address_number AS client_number,
                   c.neighborhood AS client_neighborhood, c.city AS client_city,
                   t.number AS table_number
            FROM orders o
            LEFT JOIN clients c ON c.id = o.client_id
            LEFT JOIN tables t ON t.id = o.table_id
            WHERE o.id = :oid AND o.restaurant_id = :rid
            LIMIT 1
        ");
        $stmt->execute(['oid' => $orderId, 'rid' => $restaurantId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateAddress($row) : null;
    }

    /**
     * Atualiza status de pedido.
     * Retorna número de linhas afetadas para logs/validação.
     */
    public function updateStatus(int $orderId, string $newStatus): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $orderId]);
        return $stmt->rowCount();
    }

    /**
     * Gera hash do estado atual (usado no polling rápido).
     */
    public function getLastUpdateHash(int $restaurantId): string
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT id, status, is_paid, created_at
            FROM orders
            WHERE restaurant_id = :rid
              AND order_type IN ('delivery','pickup','retirada','local')
              AND status IN ('novo','preparo','rota','entregue','cancelado')
        ");
        $stmt->execute(['rid' => $restaurantId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        usort($rows, fn ($a, $b) => ($a['id'] ?? 0) <=> ($b['id'] ?? 0));

        $signature = array_map(function ($row) {
            return implode('|', [
                $row['id'] ?? 0,
                $row['status'] ?? '',
                $row['is_paid'] ?? 0,
                $row['created_at'] ?? ''
            ]);
        }, $rows);

        return sha1(implode(';', $signature));
    }

    /**
     * Retorna dados do cliente e seus pedidos (Client Hub).
     */
    public function fetchClientHubData(int $clientId, int $restaurantId): array
    {
        $conn = Database::connect();

        $stmtClient = $conn->prepare("
            SELECT id, name, phone, address, address_number, neighborhood, city
            FROM clients
            WHERE id = :cid AND restaurant_id = :rid
            LIMIT 1
        ");
        $stmtClient->execute(['cid' => $clientId, 'rid' => $restaurantId]);
        $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            return ['success' => false, 'message' => 'Cliente não encontrado'];
        }

        $stmtOrders = $conn->prepare("
            SELECT o.id, o.total, o.status, o.payment_method, o.order_type, o.created_at, o.is_paid,
                   o.table_id, t.number AS table_number
            FROM orders o
            LEFT JOIN tables t ON t.id = o.table_id
            WHERE o.restaurant_id = :rid 
              AND o.client_id = :cid
              AND (o.is_paid = 0 OR o.is_paid IS NULL)
              AND o.status NOT IN ('entregue', 'cancelado', 'fechado')
            ORDER BY o.created_at DESC, o.id DESC
            LIMIT 50
        ");
        $stmtOrders->execute(['rid' => $restaurantId, 'cid' => $clientId]);
        $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $order['client_address'] = $client['address'] ?? null;
            $order['client_number'] = $client['address_number'] ?? null;
            $order['client_neighborhood'] = $client['neighborhood'] ?? null;
            $order['client_city'] = $client['city'] ?? null;
            $order['items'] = $this->orderItems->findAll((int) $order['id']);
            $order = $this->hydrateAddress($order);
        }
        unset($order);

        $clientAddressParts = array_filter([
            $client['address'] ?? null,
            $client['address_number'] ?? null,
            $client['neighborhood'] ?? null,
            $client['city'] ?? null,
        ], fn ($val) => $val !== null && $val !== '');

        return [
            'success' => true,
            'client' => [
                'id' => (int) $client['id'],
                'name' => $client['name'],
                'phone' => $client['phone'],
                'address' => $client['address'] ?? null,
                'address_number' => $client['address_number'] ?? null,
                'neighborhood' => $client['neighborhood'] ?? null,
                'city' => $client['city'] ?? null,
                'full_address' => $clientAddressParts ? implode(', ', $clientAddressParts) : null,
            ],
            'orders' => $orders
        ];
    }

    /**
     * Normaliza endereço do cliente para uso nas views.
     */
    private function hydrateAddress(array $row): array
    {
        $parts = array_filter([
            $row['client_address'] ?? null,
            $row['client_number'] ?? null,
            $row['client_neighborhood'] ?? null,
            $row['client_city'] ?? null,
        ], fn ($val) => $val !== null && $val !== '');

        if ($parts) {
            $row['client_address'] = implode(', ', $parts);
        }

        return $row;
    }
}
