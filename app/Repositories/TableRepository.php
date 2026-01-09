<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class TableRepository
{
    /**
     * Busca mesa com pedido atual vinculado
     */
    public function findWithCurrentOrder(int $tableId, int $restaurantId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT t.*, o.total as order_total 
                                FROM tables t 
                                LEFT JOIN orders o ON t.current_order_id = o.id 
                                WHERE t.id = :tid AND t.restaurant_id = :rid");
        $stmt->execute(['tid' => $tableId, 'rid' => $restaurantId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Ocupa a mesa vinculando um pedido
     */
    public function occupy(int $tableId, int $orderId): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE tables SET status = 'ocupada', current_order_id = :oid WHERE id = :tid")
             ->execute(['oid' => $orderId, 'tid' => $tableId]);
    }

    /**
     * Libera a mesa
     */
    public function free(int $tableId): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE tables SET status = 'livre', current_order_id = NULL WHERE id = :tid")
             ->execute(['tid' => $tableId]);
    }

    /**
     * Retorna todas as mesas do restaurante
     */
    public function findAll(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT t.*, o.total as order_total 
                                FROM tables t 
                                LEFT JOIN orders o ON t.current_order_id = o.id 
                                WHERE t.restaurant_id = :rid 
                                ORDER BY CAST(t.number AS UNSIGNED)");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
