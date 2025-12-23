<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

/**
 * [FASE 5] Controller de Adicionais
 * CRUD para Grupos e Itens de Adicionais
 * Sem vínculo com produtos nesta fase
 */
class AdditionalController {

    // ==========================================
    // LISTAGEM GERAL (GRUPOS + ITENS)
    // ==========================================
    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        // Busca grupos com seus itens
        $sqlGroups = "SELECT * FROM additional_groups WHERE restaurant_id = :rid ORDER BY name ASC";
        $stmt = $conn->prepare($sqlGroups);
        $stmt->execute(['rid' => $restaurantId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Para cada grupo, busca seus itens
        foreach ($groups as &$group) {
            $stmtItems = $conn->prepare("SELECT * FROM additional_items WHERE group_id = :gid ORDER BY name ASC");
            $stmtItems->execute(['gid' => $group['id']]);
            $group['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }

        require __DIR__ . '/../../../views/admin/additionals/index.php';
    }

    // ==========================================
    // GRUPOS
    // ==========================================
    
    // Criar grupo
    public function storeGroup() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $required = intval($data['required'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Nome do grupo é obrigatório']);
            return;
        }

        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO additional_groups (restaurant_id, name, required) VALUES (:rid, :name, :req)");
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $name,
            'req' => $required
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Grupo criado com sucesso',
            'group_id' => $conn->lastInsertId()
        ]);
    }

    // Atualizar grupo
    public function updateGroup() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $required = intval($data['required'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id <= 0 || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        $conn = Database::connect();
        $stmt = $conn->prepare("UPDATE additional_groups SET name = :name, required = :req WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute([
            'name' => $name,
            'req' => $required,
            'id' => $id,
            'rid' => $restaurantId
        ]);

        echo json_encode(['success' => true, 'message' => 'Grupo atualizado']);
    }

    // Excluir grupo (e todos os itens)
    public function deleteGroup() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $conn = Database::connect();
        // CASCADE vai deletar os itens automaticamente
        $stmt = $conn->prepare("DELETE FROM additional_groups WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);

        echo json_encode(['success' => true, 'message' => 'Grupo excluído']);
    }

    // ==========================================
    // ITENS
    // ==========================================

    // Criar item
    public function storeItem() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $groupId = intval($data['group_id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $price = floatval(str_replace(',', '.', $data['price'] ?? 0));
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($groupId <= 0 || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        $conn = Database::connect();

        // Verifica se grupo pertence à loja
        $stmtCheck = $conn->prepare("SELECT id FROM additional_groups WHERE id = :gid AND restaurant_id = :rid");
        $stmtCheck->execute(['gid' => $groupId, 'rid' => $restaurantId]);
        if (!$stmtCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Grupo não encontrado']);
            return;
        }

        $stmt = $conn->prepare("INSERT INTO additional_items (group_id, name, price) VALUES (:gid, :name, :price)");
        $stmt->execute([
            'gid' => $groupId,
            'name' => $name,
            'price' => $price
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Item criado com sucesso',
            'item_id' => $conn->lastInsertId()
        ]);
    }

    // Atualizar item
    public function updateItem() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $price = floatval(str_replace(',', '.', $data['price'] ?? 0));
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id <= 0 || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        $conn = Database::connect();

        // Verifica se item pertence a um grupo da loja (multi-tenant via JOIN)
        $stmtCheck = $conn->prepare("SELECT i.id FROM additional_items i 
            INNER JOIN additional_groups g ON i.group_id = g.id 
            WHERE i.id = :id AND g.restaurant_id = :rid");
        $stmtCheck->execute(['id' => $id, 'rid' => $restaurantId]);
        if (!$stmtCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Item não encontrado']);
            return;
        }

        $stmt = $conn->prepare("UPDATE additional_items SET name = :name, price = :price WHERE id = :id");
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'id' => $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Item atualizado']);
    }

    // Excluir item
    public function deleteItem() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $conn = Database::connect();

        // Verifica se item pertence a um grupo da loja
        $stmtCheck = $conn->prepare("SELECT i.id FROM additional_items i 
            INNER JOIN additional_groups g ON i.group_id = g.id 
            WHERE i.id = :id AND g.restaurant_id = :rid");
        $stmtCheck->execute(['id' => $id, 'rid' => $restaurantId]);
        if (!$stmtCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Item não encontrado']);
            return;
        }

        $stmt = $conn->prepare("DELETE FROM additional_items WHERE id = :id");
        $stmt->execute(['id' => $id]);

        echo json_encode(['success' => true, 'message' => 'Item excluído']);
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
