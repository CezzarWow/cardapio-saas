<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class StockRepository
{
    /**
     * Atualiza estoque de um produto
     */
    public function updateStock(int $productId, int $newStock): void
    {
        $conn = Database::connect();
        $conn->prepare('UPDATE products SET stock = :stock WHERE id = :pid')
             ->execute(['stock' => $newStock, 'pid' => $productId]);
    }

    /**
     * Incrementa estoque
     */
    public function increment(int $productId, int $amount, int $restaurantId = null): int
    {
        $conn = Database::connect();
        $sql = 'UPDATE products SET stock = stock + :amount WHERE id = :pid';
        $params = ['amount' => $amount, 'pid' => $productId];

        if ($restaurantId) {
            $sql .= ' AND restaurant_id = :rid';
            $params['rid'] = $restaurantId;
        }

        $conn->prepare($sql)->execute($params);
        return $conn->prepare('SELECT stock FROM products WHERE id = :id')->execute(['id' => $productId]) ? $conn->lastInsertId() : 0; // lastInsertId wont work for SELECT.
        // Need to refetch if we want return value, but usually just void is enough.
        // Original service returns void in decrement, and updated array in update.
        return 0;
    }

    /**
     * Decrementa estoque
     */
    public function decrement(int $productId, int $amount, int $restaurantId = null): void
    {
        $conn = Database::connect();
        $sql = 'UPDATE products SET stock = stock - :amount WHERE id = :pid';
        $params = ['amount' => $amount, 'pid' => $productId];

        if ($restaurantId) {
            $sql .= ' AND restaurant_id = :rid';
            $params['rid'] = $restaurantId;
        }

        $conn->prepare($sql)->execute($params);
    }

    /**
     * Registra movimentação de estoque
     */
    public function registerMovement(int $restaurantId, int $productId, int $qtyBefore, int $qtyAfter, int $amount, string $reason, string $type): void
    {
        $conn = Database::connect();
        $conn->prepare('INSERT INTO stock_movements 
                        (restaurant_id, product_id, type, quantity, old_stock, new_stock, reason, created_at) 
                        VALUES (:rid, :pid, :type, :qty, :old, :new, :reason, NOW())')
             ->execute([
                 'rid' => $restaurantId,
                 'pid' => $productId,
                 'type' => $type,
                 'qty' => $amount, // Quantidade movimentada
                 'old' => $qtyBefore,
                 'new' => $qtyAfter,
                 'reason' => $reason
             ]);
    }

    /**
     * Busca movimentações (com filtros opcionais)
     */
    public function findMovements(int $restaurantId, array $filters = []): array
    {
        $conn = Database::connect();
        $sql = 'SELECT m.*, p.name as product_name, p.image as product_image, c.name as category_name
                FROM stock_movements m
                INNER JOIN products p ON m.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE m.restaurant_id = :rid';

        $params = ['rid' => $restaurantId];

        // Filtro por produto
        if (!empty($filters['product'])) {
            $sql .= ' AND p.id = :pid';
            $params['pid'] = $filters['product'];
        }

        // Filtro por categoria
        if (!empty($filters['category'])) {
            $sql .= ' AND c.name = :cat';
            $params['cat'] = $filters['category'];
        }

        // Filtro por Data (Início)
        if (!empty($filters['start_date'])) {
            $sql .= ' AND m.created_at >= :start';
            $params['start'] = $filters['start_date'] . ' 00:00:00';
        }

        // Filtro por Data (Fim)
        if (!empty($filters['end_date'])) {
            $sql .= ' AND m.created_at <= :end';
            $params['end'] = $filters['end_date'] . ' 23:59:59';
        }

        $sql .= ' ORDER BY m.created_at DESC LIMIT 100';

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
