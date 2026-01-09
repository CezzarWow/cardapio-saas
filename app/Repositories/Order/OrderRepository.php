<?php

namespace App\Repositories\Order;

use App\Core\Database;
use PDO;

/**
 * Repository para Pedidos (API)
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
        
        // Força update do order_type caso tenha falhado
        $conn->prepare("UPDATE orders SET order_type = :ot WHERE id = :oid AND (order_type IS NULL OR order_type = '')")
             ->execute(['ot' => $data['order_type'], 'oid' => $orderId]);
        
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
     * Busca itens do pedido
     */
    public function findItems(int $orderId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT product_id, quantity, price, id, name FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca item específico
     */
    public function findItem(int $itemId, int $orderId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT product_id, quantity, price FROM order_items WHERE id = :id AND order_id = :oid");
        $stmt->execute(['id' => $itemId, 'oid' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Insere itens do pedido
     */
    public function insertItems(int $orderId, array $items): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, quantity, price) VALUES (:oid, :pid, :name, :qty, :price)");
        
        foreach ($items as $item) {
            $stmt->execute([
                'oid' => $orderId,
                'pid' => $item['product_id'] ?? ($item['id'] ?? null), // Aceita id ou product_id
                'name' => $item['name'] ?? 'Produto',
                'qty' => $item['quantity'] ?? 1,
                'price' => $item['price']
            ]);
        }
    }

    /**
     * Adiciona um item unitário
     */
    public function addItem(int $orderId, array $item): void
    {
        $this->insertItems($orderId, [$item]);
    }

    /**
     * Atualiza quantidade de item
     */
    public function updateItemQuantity(int $itemId, int $quantity): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE order_items SET quantity = :qty WHERE id = :id")
             ->execute(['qty' => $quantity, 'id' => $itemId]);
    }

    /**
     * Deleta itens do pedido
     */
    public function deleteItems(int $orderId): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM order_items WHERE order_id = :oid")->execute(['oid' => $orderId]);
    }

    /**
     * Deleta um item específico
     */
    public function deleteItem(int $itemId): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM order_items WHERE id = :id")->execute(['id' => $itemId]);
    }

    /**
     * Deleta pagamentos do pedido
     */
    public function deletePayments(int $orderId): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM order_payments WHERE order_id = :oid")->execute(['oid' => $orderId]);
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
     * Retorna resumo de vendas por método (para fechamento de caixa)
     */
    public function getSalesSummary(int $restaurantId, string $openedAt): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT op.method, SUM(op.amount) as total 
            FROM order_payments op
            INNER JOIN orders o ON o.id = op.order_id
            WHERE o.restaurant_id = :rid 
            AND o.created_at >= :opened_at 
            AND o.status = 'concluido'
            GROUP BY op.method
        ");
        $stmt->execute(['rid' => $restaurantId, 'opened_at' => $openedAt]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Busca pedidos de clientes em aberto (não vinculados a mesas)
     * Ex: Delivery ou Comanda Avulsa
     */
    public function findOpenClientOrders(int $restaurantId): array
    {
        $conn = Database::connect();
        // Busca pedidos que NÃO estão 'cancelado' ou 'finalizado'
        // E idealmente que não estejam vinculados a uma mesa ativa (embora a lógica de mesa use current_order_id)
        // Aqui assumimos que 'open clients' são pedidos sem mesa vinculada na tabela de mesas?
        // OU simplesmente pedidos do tipo 'delivery'/'balcao' que estão abertos.
        // Vamos focar em orders.status != concluidos/cancelados
        
        $sql = "
            SELECT o.*, c.name as client_name, c.phone as client_phone 
            FROM orders o
            LEFT JOIN clients c ON o.client_id = c.id
            WHERE o.restaurant_id = :rid 
            AND o.status NOT IN ('concluido', 'cancelado')
            AND (o.order_type = 'delivery' OR o.order_type = 'balcao')
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
