<?php

namespace App\Repositories\Order;

use App\Core\Database;
use PDO;

/**
 * Repository para Pedidos (API)
 * 
 * Responsável exclusivamente pela tabela `orders`.
 * Para itens de pedido, use OrderItemRepository.
 * Para pagamentos de pedido, use OrderPaymentRepository.
 */
class OrderRepository
{
    /**
     * Restaura pedido (Cria com ID específico)
     */
    public function restore(array $order): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            INSERT INTO orders (id, restaurant_id, total, status, payment_method, created_at) 
            VALUES (:id, :rid, :total, :status, :pay, :date)
        ");
        $stmt->execute([
            'id' => $order['id'],
            'rid' => $order['restaurant_id'],
            'total' => $order['total'],
            'status' => $order['status'],
            'pay' => $order['payment_method'],
            'date' => $order['created_at']
        ]);
    }

    /**
     * Cria um novo pedido
     * @return int ID do pedido criado
     */
    public function create(array $data): int
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            INSERT INTO orders (
                restaurant_id, 
                client_id, 
                total, 
                status, 
                order_type, 
                payment_method,
                observation,
                change_for,
                source,
                created_at
            ) VALUES (
                :rid, 
                :cid, 
                :total, 
                'novo', 
                :otype, 
                :payment,
                :obs,
                :change,
                'web',
                NOW()
            )
        ");
        
        // DEBUG: Rastrear parâmetros antes do INSERT
        error_log("[DEBUG OrderRepository::create] params: " . json_encode([
            'rid' => $data['restaurant_id'],
            'cid' => $data['client_id'],
            'total' => $data['total'],
            'otype' => $data['order_type'] ?? 'VAZIO',
            'payment' => $data['payment_method']
        ]));
        
        $stmt->execute([
            'rid' => $data['restaurant_id'],
            'cid' => $data['client_id'],
            'total' => $data['total'],
            'otype' => $data['order_type'],
            'payment' => $data['payment_method'],
            'obs' => $data['observation'] ?? null,
            'change' => $data['change_for'] ?? null
        ]);
        
        $orderId = (int) $conn->lastInsertId();
        
        // DEBUG: Força update do order_type COM VALOR DIRETO (teste)
        $orderTypeValue = $data['order_type'] ?? 'balcao';
        $updateSql = "UPDATE orders SET order_type = '{$orderTypeValue}' WHERE id = {$orderId}";
        error_log("[DEBUG OrderRepository] Executando UPDATE direto: " . $updateSql);
        $conn->exec($updateSql);
        
        return $orderId;
    }

    /**
     * Busca pedido por ID
     */
    public function find(int $id, int $restaurantId = null): ?array
    {
        $conn = Database::connect();
        $sql = "SELECT * FROM orders WHERE id = :id";
        $params = ['id' => $id];

        if ($restaurantId) {
            $sql .= " AND restaurant_id = :rid";
            $params['rid'] = $restaurantId;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca todos os pedidos com detalhes (para listagem)
     */
    public function findAllWithDetails(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT o.*, 
                   COALESCE(SUM(oi.quantity * oi.price), 0) as calculated_total
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE o.restaurant_id = :rid
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza status do pedido
     */
    public function updateStatus(int $id, string $status): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE orders SET status = :status WHERE id = :id")
             ->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * Atualiza dados de pagamento
     */
    public function updatePayment(int $id, bool $isPaid, string $method): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE orders SET is_paid = :paid, payment_method = :method WHERE id = :id")
             ->execute(['paid' => $isPaid ? 1 : 0, 'method' => $method, 'id' => $id]);
    }

    /**
     * Atualiza cliente do pedido
     */
    public function updateClient(int $id, int $clientId): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE orders SET client_id = :cid WHERE id = :oid")
             ->execute(['cid' => $clientId, 'oid' => $id]);
    }

    /**
     * Atualiza total do pedido
     */
    public function updateTotal(int $id, float $total): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE orders SET total = GREATEST(0, :total) WHERE id = :id")
             ->execute(['total' => $total, 'id' => $id]);
    }

    /**
     * Atualiza o tipo do pedido
     */
    public function updateOrderType(int $id, string $orderType): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE orders SET order_type = :ot WHERE id = :id")
             ->execute(['ot' => $orderType, 'id' => $id]);
    }

    /**
     * Deleta pedido
     */
    public function delete(int $id): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM orders WHERE id = :id")->execute(['id' => $id]);
    }

    /**
     * Busca pedidos de clientes em aberto (não vinculados a mesas)
     */
    public function findOpenClientOrders(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $sql = "
            SELECT o.*, c.name as client_name, c.phone as client_phone 
            FROM orders o
            LEFT JOIN clients c ON o.client_id = c.id
            WHERE o.restaurant_id = :rid 
            AND o.status NOT IN ('concluido', 'cancelado')
            AND o.order_type IN ('delivery', 'balcao', 'comanda')
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
