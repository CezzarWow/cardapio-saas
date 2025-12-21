<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class ClientController {

    // BUSCA RÁPIDA (Para o Autocomplete do PDV)
    public function search() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $term = $_GET['q'] ?? '';
        $rid = $_SESSION['loja_ativa_id'];

        if (strlen($term) < 2) {
            echo json_encode([]);
            return;
        }

        $conn = Database::connect();
        // Busca por Nome OU Telefone
        $sql = "SELECT id, name, phone FROM clients 
                WHERE restaurant_id = :rid 
                AND (name LIKE :term OR phone LIKE :term) 
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'rid' => $rid,
            'term' => "%$term%"
        ]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // CADASTRO RÁPIDO (Modal do PDV)
    public function store() {
        header('Content-Type: application/json');
        $this->checkSession();

        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';
        $rid = $_SESSION['loja_ativa_id'];

        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Nome obrigatório']);
            return;
        }

        $conn = Database::connect();
        
        try {
            $stmt = $conn->prepare("INSERT INTO clients (restaurant_id, name, phone) VALUES (:rid, :name, :phone)");
            $stmt->execute(['rid' => $rid, 'name' => $name, 'phone' => $phone]);
            
            echo json_encode([
                'success' => true,
                'client' => [
                    'id' => $conn->lastInsertId(),
                    'name' => $name
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()]);
        }
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) exit;
    }
}
