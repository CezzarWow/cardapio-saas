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
        $stmt = $conn->prepare('SELECT t.*, o.total as order_total 
                                FROM tables t 
                                LEFT JOIN orders o ON t.current_order_id = o.id 
                                WHERE t.id = :tid AND t.restaurant_id = :rid');
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
        
        // [FIX] Calcula total somando os itens, pois orders.total nem sempre está atualizado em tempo real
        $sql = "SELECT t.*, 
                       (SELECT SUM(price * quantity) FROM order_items WHERE order_id = o.id) as order_total, 
                       o.order_type, 
                       c.credit_limit, 
                       c.name as client_name 
                FROM tables t 
                LEFT JOIN orders o ON t.current_order_id = o.id 
                LEFT JOIN clients c ON o.client_id = c.id
                WHERE t.restaurant_id = :rid 
                ORDER BY CAST(t.number AS UNSIGNED)";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca mesa pelo número
     */
    public function findByNumber(int $restaurantId, string $number): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM tables WHERE restaurant_id = :rid AND number = :num');
        $stmt->execute(['rid' => $restaurantId, 'num' => $number]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Cria nova mesa
     */
    public function create(int $restaurantId, string $number): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO tables (restaurant_id, number, status) VALUES (:rid, :num, 'livre')");
        $stmt->execute(['rid' => $restaurantId, 'num' => $number]);
        return (int) $conn->lastInsertId();
    }

    /**
     * Deleta mesa por ID
     */
    public function delete(int $tableId): void
    {
        $conn = Database::connect();
        $conn->prepare('DELETE FROM tables WHERE id = :id')->execute(['id' => $tableId]);
    }

    /**
     * Busca mesa pelo order_id vinculado
     */
    public function findByOrderId(int $orderId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM tables WHERE current_order_id = :oid LIMIT 1');
        $stmt->execute(['oid' => $orderId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca mesa pelo ID
     */
    public function findById(int $tableId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM tables WHERE id = :id');
        $stmt->execute(['id' => $tableId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
