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
     * Insere itens do pedido
     */
    public function insertItems(int $orderId, array $items): void
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            INSERT INTO order_items (
                order_id, 
                product_id, 
                name, 
                quantity, 
                price
            ) VALUES (
                :oid, 
                :pid, 
                :name, 
                :qty, 
                :price
            )
        ");
        
        foreach ($items as $item) {
            $stmt->execute([
                'oid' => $orderId,
                'pid' => $item['product_id'] ?? null,
                'name' => $item['name'] ?? 'Produto',
                'qty' => $item['quantity'] ?? 1,
                'price' => $item['price']
            ]);
        }
    }
}
