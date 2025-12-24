<?php
// LOCALIZAÇÃO ORIGINAL: app/Controllers/Admin/AdditionalController.php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

/**
 * [FASE 5.1] Controller de Adicionais - Arquitetura Global
 * - Itens são globais por loja (restaurant_id)
 * - Grupos vinculam itens via tabela pivot
 * - Padrão clássico: POST → Redirect → Reload
 */
class AdditionalController {

    // ==========================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ==========================================
    
    private function getGroupsWithItems($conn, $restaurantId) {
        // Busca grupos
        $stmt = $conn->prepare("SELECT * FROM additional_groups WHERE restaurant_id = :rid ORDER BY name ASC");
        $stmt->execute(['rid' => $restaurantId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Para cada grupo, busca itens via pivot
        foreach ($groups as &$group) {
            $stmtItems = $conn->prepare("
                SELECT ai.* FROM additional_items ai
                INNER JOIN additional_group_items agi ON ai.id = agi.item_id
                WHERE agi.group_id = :gid
                ORDER BY ai.name ASC
            ");
            $stmtItems->execute(['gid' => $group['id']]);
            $group['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($group); // Quebra referência
        
        return $groups;
    }
    
    private function getGlobalItems($conn, $restaurantId) {
        $stmt = $conn->prepare("SELECT * FROM additional_items WHERE restaurant_id = :rid ORDER BY name ASC");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // LISTAGEM PRINCIPAL (GRUPOS + ITENS)
    // ==========================================
    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        $groups = $this->getGroupsWithItems($conn, $restaurantId);
        $allItems = $this->getGlobalItems($conn, $restaurantId);

        require __DIR__ . '/../../../views/admin/additionals/index.php';
    }

    // ==========================================
    // CATÁLOGO GLOBAL DE ITENS
    // ==========================================
    public function listItems() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        $items = $this->getGlobalItems($conn, $restaurantId);
        
        // Conta em quantos grupos cada item está
        foreach ($items as &$item) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM additional_group_items WHERE item_id = :id");
            $stmt->execute(['id' => $item['id']]);
            $item['groups_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        }
        unset($item);

        require __DIR__ . '/../../../views/admin/additionals/items.php';
    }

    // ==========================================
    // GRUPOS - CRUD
    // ==========================================
    
    public function storeGroup() {
        $this->checkSession();
        
        $name = trim($_POST['name'] ?? '');
        $restaurantId = $_SESSION['loja_ativa_id'];

        if (!empty($name)) {
            $conn = Database::connect();
            $stmt = $conn->prepare("INSERT INTO additional_groups (restaurant_id, name, required) VALUES (:rid, :name, 0)");
            $stmt->execute([
                'rid' => $restaurantId,
                'name' => $name
            ]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais');
        exit;
    }

    public function deleteGroup() {
        $this->checkSession();
        
        $id = intval($_GET['id'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id > 0) {
            $conn = Database::connect();
            $stmt = $conn->prepare("DELETE FROM additional_groups WHERE id = :id AND restaurant_id = :rid");
            $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais');
        exit;
    }

    // ==========================================
    // ITENS GLOBAIS - CRUD
    // ==========================================

    public function createItem() {
        $this->checkSession();
        require __DIR__ . '/../../../views/admin/additionals/item_form.php';
    }

    public function storeItem() {
        $this->checkSession();
        
        $name = trim($_POST['name'] ?? '');
        $price = floatval(str_replace(',', '.', $_POST['price'] ?? 0));
        $restaurantId = $_SESSION['loja_ativa_id'];

        if (!empty($name)) {
            $conn = Database::connect();
            $stmt = $conn->prepare("INSERT INTO additional_items (restaurant_id, name, price) VALUES (:rid, :name, :price)");
            $stmt->execute([
                'rid' => $restaurantId,
                'name' => $name,
                'price' => $price
            ]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais/itens');
        exit;
    }

    public function editItem() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $id = intval($_GET['id'] ?? 0);
        
        $stmt = $conn->prepare("SELECT * FROM additional_items WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            header('Location: ' . BASE_URL . '/admin/loja/adicionais/itens');
            exit;
        }

        require __DIR__ . '/../../../views/admin/additionals/item_form.php';
    }

    public function updateItem() {
        $this->checkSession();
        
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = floatval(str_replace(',', '.', $_POST['price'] ?? 0));
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id > 0 && !empty($name)) {
            $conn = Database::connect();
            $stmt = $conn->prepare("UPDATE additional_items SET name = :name, price = :price WHERE id = :id AND restaurant_id = :rid");
            $stmt->execute([
                'name' => $name,
                'price' => $price,
                'id' => $id,
                'rid' => $restaurantId
            ]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais/itens');
        exit;
    }

    public function deleteItem() {
        $this->checkSession();
        
        $id = intval($_GET['id'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id > 0) {
            $conn = Database::connect();
            $stmt = $conn->prepare("DELETE FROM additional_items WHERE id = :id AND restaurant_id = :rid");
            $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais/itens');
        exit;
    }

    // ==========================================
    // VÍNCULO GRUPO ↔ ITEM (PIVOT)
    // ==========================================

    public function linkItem() {
        $this->checkSession();
        
        $groupId = intval($_POST['group_id'] ?? 0);
        $itemId = intval($_POST['item_id'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($groupId > 0 && $itemId > 0) {
            $conn = Database::connect();
            
            // Verifica se grupo pertence à loja
            $stmt = $conn->prepare("SELECT id FROM additional_groups WHERE id = :gid AND restaurant_id = :rid");
            $stmt->execute(['gid' => $groupId, 'rid' => $restaurantId]);
            
            if ($stmt->fetch()) {
                // Insere vínculo (IGNORE para não duplicar)
                $stmt = $conn->prepare("INSERT IGNORE INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)");
                $stmt->execute(['gid' => $groupId, 'iid' => $itemId]);
            }
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais');
        exit;
    }

    public function unlinkItem() {
        $this->checkSession();
        
        $groupId = intval($_GET['grupo'] ?? 0);
        $itemId = intval($_GET['item'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($groupId > 0 && $itemId > 0) {
            $conn = Database::connect();
            
            // Verifica se grupo pertence à loja
            $stmt = $conn->prepare("SELECT id FROM additional_groups WHERE id = :gid AND restaurant_id = :rid");
            $stmt->execute(['gid' => $groupId, 'rid' => $restaurantId]);
            
            if ($stmt->fetch()) {
                $stmt = $conn->prepare("DELETE FROM additional_group_items WHERE group_id = :gid AND item_id = :iid");
                $stmt->execute(['gid' => $groupId, 'iid' => $itemId]);
            }
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais');
        exit;
    }

    // ==========================================
    // SESSÃO
    // ==========================================
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
