<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class TableController {

    public function index() {
        // 1. Segurança
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ../../admin');
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $conn = Database::connect();

        // 2. Busca as mesas da loja
        $stmt = $conn->prepare("SELECT * FROM tables WHERE restaurant_id = :rid ORDER BY number ASC");
        $stmt->execute(['rid' => $restaurant_id]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Carrega a visualização
        require __DIR__ . '/../../../views/admin/tables/index.php';
    }
    // --- BUSCA DE MESAS (JSON para o PDV) ---
    public function search() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $restaurant_id = $_SESSION['loja_ativa_id'] ?? null;
        if (!$restaurant_id) {
            echo json_encode([]);
            exit;
        }

        $conn = Database::connect();
        
        // Retorna todas as mesas ordenadas pelo número
        // O front-end filtra ou mostra tudo se quiser.
        // Traz o status para pintar de verde/vermelho.
        $stmt = $conn->prepare("SELECT id, number, status FROM tables WHERE restaurant_id = :rid ORDER BY number ASC");
        $stmt->execute(['rid' => $restaurant_id]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($tables);
    }
}
