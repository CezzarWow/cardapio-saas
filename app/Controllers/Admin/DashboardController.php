<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class DashboardController {
    
    public function index() {
        // 1. Conecta no banco
        $conn = Database::connect();

        // 2. Busca todos os restaurantes cadastrados
        $stmt = $conn->query("SELECT * FROM restaurants ORDER BY id DESC");
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Carrega a tela (enviando a variável $restaurants junto)
        require __DIR__ . '/../../../views/admin/dashboard.php';
    }
}