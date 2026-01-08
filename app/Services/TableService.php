<?php
namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

class TableService {

    /**
     * Retorna todas as mesas de um restaurante, incluindo o total do pedido atual se houver.
     */
    public function getAllTables($restaurantId) {
        $conn = Database::connect();
        $sql = "SELECT t.*, o.total as current_total 
                FROM tables t 
                LEFT JOIN orders o ON t.current_order_id = o.id 
                WHERE t.restaurant_id = :rid 
                ORDER BY t.number ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna pedidos de clientes/delivery em aberto (sem mesa vinculada).
     */
    public function getOpenClientOrders($restaurantId) {
        $conn = Database::connect();
        $sql = "SELECT o.id as order_id, o.total, o.created_at, o.is_paid, c.name as client_name, c.id as client_id 
                FROM orders o 
                JOIN clients c ON o.client_id = c.id 
                WHERE o.restaurant_id = :rid 
                AND o.status = 'aberto' 
                AND (o.id NOT IN (SELECT current_order_id FROM tables WHERE restaurant_id = :rid AND current_order_id IS NOT NULL))
                ORDER BY o.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cria uma nova mesa.
     */
    public function createTable($restaurantId, $number) {
        $conn = Database::connect();

        // Check duplicidade
        $check = $conn->prepare("SELECT id FROM tables WHERE restaurant_id = :rid AND number = :num");
        $check->execute(['rid' => $restaurantId, 'num' => $number]);
        
        if ($check->rowCount() > 0) {
            throw new Exception('Mesa já existe!');
        }

        $stmt = $conn->prepare("INSERT INTO tables (restaurant_id, number, status) VALUES (:rid, :num, 'livre')");
        $stmt->execute(['rid' => $restaurantId, 'num' => $number]);
        
        return true;
    }

    /**
     * Deleta uma mesa.
     * Retorna array com ['success' => bool, 'occupied' => bool, 'message' => string]
     */
    public function deleteTable($restaurantId, $number, $force = false) {
        $conn = Database::connect();

        // Busca mesa
        $stmt = $conn->prepare("SELECT id, status FROM tables WHERE restaurant_id = :rid AND number = :num");
        $stmt->execute(['rid' => $restaurantId, 'num' => $number]);
        $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mesa) {
            throw new Exception('Mesa não encontrada!');
        }

        // Validação de Ocupação
        if ($mesa['status'] === 'ocupada' && !$force) {
            return [
                'success' => false,
                'occupied' => true,
                'message' => 'Mesa Ocupada!'
            ];
        }

        // Deleta
        $del = $conn->prepare("DELETE FROM tables WHERE id = :id");
        $del->execute(['id' => $mesa['id']]);

        return ['success' => true];
    }
}
