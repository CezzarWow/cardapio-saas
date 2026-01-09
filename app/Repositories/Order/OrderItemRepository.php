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
            SELECT product_id, quantity, price, id, name, extras, observation 
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
     * Insere múltiplos itens em um pedido (agrupa itens duplicados)
     */
    public function insert(int $orderId, array $items): void
    {
        $conn = Database::connect();
        
        // Preparar statements
        $stmtFind = $conn->prepare("
            SELECT id, quantity FROM order_items 
            WHERE order_id = :oid AND product_id = :pid
            LIMIT 1
        ");
        
        $stmtInsert = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, name, quantity, price, extras, observation) 
            VALUES (:oid, :pid, :name, :qty, :price, :extras, :obs)
        ");
        
        $stmtUpdate = $conn->prepare("
            UPDATE order_items SET quantity = :qty WHERE id = :id
        ");
        
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? ($item['id'] ?? null);
            $quantity = $item['quantity'] ?? 1;
            
            // Preparar extras como JSON se for array
            $extras = $item['extras'] ?? null;
            if (is_array($extras)) {
                $extras = json_encode($extras);
            }
            
            // Verificar se item já existe no pedido (sem extras)
            // Itens com extras são sempre novos (não agrupa)
            $shouldInsertNew = !empty($extras);
            
            if (!$shouldInsertNew) {
                $stmtFind->execute(['oid' => $orderId, 'pid' => $productId]);
                $existing = $stmtFind->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    // Item existe: incrementar quantidade
                    $newQty = $existing['quantity'] + $quantity;
                    $stmtUpdate->execute(['qty' => $newQty, 'id' => $existing['id']]);
                    continue;
                }
            }
            
            // Item não existe ou tem extras: inserir novo
            $stmtInsert->execute([
                'oid' => $orderId,
                'pid' => $productId,
                'name' => $item['name'] ?? 'Produto',
                'qty' => $quantity,
                'price' => $item['price'],
                'extras' => $extras,
                'obs' => $item['observation'] ?? null
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
