<?php

namespace App\Repositories\Order;

use App\Core\Database;
use PDO;

/**
 * Repository para Itens de Pedido
 * 
 * Responsável exclusivamente pela tabela `order_items`
 */
class OrderItemRepository
{
    /**
     * Busca todos os itens de um pedido
     */
    public function findAll(int $orderId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT product_id, quantity, price, id, name 
            FROM order_items 
            WHERE order_id = :oid
        ");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um item específico
     */
    public function find(int $itemId, int $orderId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT product_id, quantity, price 
            FROM order_items 
            WHERE id = :id AND order_id = :oid
        ");
        $stmt->execute(['id' => $itemId, 'oid' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Insere múltiplos itens em um pedido
     */
    public function insert(int $orderId, array $items): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, name, quantity, price) 
            VALUES (:oid, :pid, :name, :qty, :price)
        ");
        
        foreach ($items as $item) {
            $stmt->execute([
                'oid' => $orderId,
                'pid' => $item['product_id'] ?? ($item['id'] ?? null),
                'name' => $item['name'] ?? 'Produto',
                'qty' => $item['quantity'] ?? 1,
                'price' => $item['price']
            ]);
        }
    }

    /**
     * Adiciona um único item ao pedido
     */
    public function add(int $orderId, array $item): void
    {
        $this->insert($orderId, [$item]);
    }

    /**
     * Atualiza quantidade de um item
     */
    public function updateQuantity(int $itemId, int $quantity): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE order_items SET quantity = :qty WHERE id = :id")
             ->execute(['qty' => $quantity, 'id' => $itemId]);
    }

    /**
     * Deleta todos os itens de um pedido
     */
    public function deleteAll(int $orderId): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM order_items WHERE order_id = :oid")
             ->execute(['oid' => $orderId]);
    }

    /**
     * Deleta um item específico
     */
    public function delete(int $itemId): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM order_items WHERE id = :id")
             ->execute(['id' => $itemId]);
    }
}
