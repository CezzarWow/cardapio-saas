<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class SalesController {

    public function index() {
        // 1. Segurança
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ../../admin');
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $conn = Database::connect();

        // 2. Busca as Vendas (Orders) mais recentes primeiro, CALCULANDO o total real pelos itens
        $sql = "SELECT o.*, 
                COALESCE((SELECT SUM(i.price * i.quantity) FROM order_items i WHERE i.order_id = o.id), 0) as calculated_total
                FROM orders o 
                WHERE o.restaurant_id = :rid 
                ORDER BY o.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurant_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Manda para a tela (View)
        require __DIR__ . '/../../../views/admin/sales/index.php';
    }
    // NOVA FUNÇÃO: Busca os itens de um pedido específico (AJAX)
    public function getItems() {
        // Define que vai devolver JSON
        header('Content-Type: application/json');

        // Verifica sessão
        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }

        $order_id = $_GET['id'] ?? 0;
        $conn = Database::connect();

        // Busca os itens, mas SÓ se o pedido pertencer à loja logada (Segurança)
        $sql = "SELECT i.* FROM order_items i
                JOIN orders o ON o.id = i.order_id
                WHERE i.order_id = :oid AND o.restaurant_id = :rid";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'oid' => $order_id,
            'rid' => $_SESSION['loja_ativa_id']
        ]);
        
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($items);
    }
}
