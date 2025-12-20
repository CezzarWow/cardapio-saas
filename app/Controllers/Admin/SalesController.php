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

        // 2. Busca as Vendas (Orders) mais recentes primeiro
        $sql = "SELECT * FROM orders WHERE restaurant_id = :rid ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurant_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Manda para a tela (View)
        require __DIR__ . '/../../../views/admin/sales/index.php';
    }
}
