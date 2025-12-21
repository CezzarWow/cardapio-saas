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
        
        // Dados Obrigatórios
        $name = trim($data['name'] ?? '');
        $rid = $_SESSION['loja_ativa_id'];

        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
            return;
        }

        // Dados Opcionais
        $type = $data['type'] ?? 'PF';
        $document = preg_replace('/\D/', '', $data['document'] ?? '');
        $phone = preg_replace('/\D/', '', $data['phone'] ?? '');
        $zip = preg_replace('/\D/', '', $data['zip_code'] ?? '');
        $address = trim($data['address'] ?? '');
        $num = trim($data['address_number'] ?? '');
        $neigh = trim($data['neighborhood'] ?? '');
        $city = trim($data['city'] ?? '');
        
        // Financeiro
        $credit = isset($data['credit_limit']) ? floatval($data['credit_limit']) : 0.00;
        $due = isset($data['due_day']) ? intval($data['due_day']) : null;

        $conn = Database::connect();
        
        try {
            // Verifica duplicidade de CPF/CNPJ se informado
            if ($document) {
                $check = $conn->prepare("SELECT id FROM clients WHERE restaurant_id = :rid AND document = :doc");
                $check->execute(['rid' => $rid, 'doc' => $document]);
                if ($check->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'CPF/CNPJ já cadastrado neste restaurante!']);
                    return;
                }
            }

            $sql = "INSERT INTO clients 
                    (restaurant_id, name, type, document, phone, zip_code, address, address_number, neighborhood, city, credit_limit, due_day) 
                    VALUES 
                    (:rid, :name, :type, :doc, :phone, :zip, :addr, :num, :neigh, :city, :credit, :due)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'rid' => $rid,
                'name' => $name,
                'type' => $type,
                'doc' => $document,
                'phone' => $phone,
                'zip' => $zip,
                'addr' => $address,
                'num' => $num,
                'neigh' => $neigh,
                'city' => $city,
                'credit' => $credit,
                'due' => $due
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso!',
                'client' => [
                    'id' => $conn->lastInsertId(),
                    'name' => $name,
                    'phone' => $phone
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) exit;
    }
}
