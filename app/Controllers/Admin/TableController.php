<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class TableController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->checkSession();

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $conn = Database::connect();

        // 1. BUSCA AS MESAS (Andar de Cima)
        // Traz também o total gasto se estiver ocupada
        $sqlTables = "SELECT t.*, o.total as current_total 
                      FROM tables t 
                      LEFT JOIN orders o ON t.current_order_id = o.id 
                      WHERE t.restaurant_id = :rid 
                      ORDER BY t.number ASC";
        $stmt = $conn->prepare($sqlTables);
        $stmt->execute(['rid' => $restaurant_id]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. BUSCA COMANDAS DE CLIENTES/DELIVERY (Andar de Baixo)
        // São pedidos EM ABERTO que NÃO têm mesa vinculada (table_id IS NULL)
        // Trazemos o nome do cliente e o ID do pedido
        $sqlClients = "SELECT o.id as order_id, o.total, o.created_at, o.is_paid, c.name as client_name, c.id as client_id 
                       FROM orders o 
                       JOIN clients c ON o.client_id = c.id 
                       WHERE o.restaurant_id = :rid 
                       AND o.status = 'aberto' 
                       AND (o.id NOT IN (SELECT current_order_id FROM tables WHERE restaurant_id = :rid AND current_order_id IS NOT NULL))
                       ORDER BY o.created_at DESC";
        
        $stmt2 = $conn->prepare($sqlClients);
        $stmt2->execute(['rid' => $restaurant_id]);
        $clientOrders = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/tables/index.php';
    }

    // --- SALVAR NOVA MESA ---
    public function store() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $number = $data['number'] ?? null;
        $rid = $_SESSION['loja_ativa_id'];

        if (!$number && $number !== '0') { echo json_encode(['success' => false, 'message' => 'Número obrigatório']); exit; }
        if ($number < 0) { echo json_encode(['success' => false, 'message' => 'O número deve ser 0 ou maior']); exit; }

        $conn = Database::connect();
        
        // Verifica duplicidade
        $check = $conn->prepare("SELECT id FROM tables WHERE restaurant_id = :rid AND number = :num");
        $check->execute(['rid' => $rid, 'num' => $number]);
        if($check->rowCount() > 0) { echo json_encode(['success' => false, 'message' => 'Mesa já existe!']); exit; }

        try {
            $stmt = $conn->prepare("INSERT INTO tables (restaurant_id, number, status) VALUES (:rid, :num, 'livre')");
            $stmt->execute(['rid' => $rid, 'num' => $number]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- REMOVER MESA POR NÚMERO (COM VERIFICAÇÃO) ---
    public function deleteByNumber() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $number = $data['number'] ?? null;
        $force = $data['force'] ?? false; // Confirmação extra
        $rid = $_SESSION['loja_ativa_id'];

        if (!$number) { echo json_encode(['success' => false, 'message' => 'Número obrigatório']); exit; }

        $conn = Database::connect();

        // 1. Verifica se a mesa existe e pega o status
        $stmt = $conn->prepare("SELECT id, status FROM tables WHERE restaurant_id = :rid AND number = :num");
        $stmt->execute(['rid' => $rid, 'num' => $number]);
        $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mesa) {
            echo json_encode(['success' => false, 'message' => 'Mesa não encontrada!']);
            exit;
        }

        // 2. Se estiver OCUPADA e não tiver "força bruta", avisa o frontend
        if ($mesa['status'] == 'ocupada' && !$force) {
            echo json_encode([
                'success' => false, 
                'occupied' => true, // Flag para o JS pedir a segunda confirmação
                'message' => 'Mesa Ocupada!'
            ]);
            exit;
        }

        // 3. Deleta
        try {
            $del = $conn->prepare("DELETE FROM tables WHERE id = :id");
            $del->execute(['id' => $mesa['id']]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- BUSCA JSON (Para o PDV) ---
    public function search() {
        header('Content-Type: application/json');
        $this->checkSession();
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT id, number, status FROM tables WHERE restaurant_id = :rid ORDER BY number ASC");
        $stmt->execute(['rid' => $_SESSION['loja_ativa_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) { header('Location: ../../admin'); exit; }
    }
}
