<?php
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

        // [NOVO] Busca categorias para o modal de vínculo em massa
        $stmtCat = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name ASC");
        $stmtCat->execute(['rid' => $restaurantId]);
        $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/additionals/index.php';
    }

    // ... (Métodos listItems, storeGroup, deleteGroup, createItem, storeItem, editItem, updateItem, deleteItem, linkItem, unlinkItem mantidos) ...

    // ==========================================
    // VÍNCULO EM MASSA (POR CATEGORIAS)
    // ==========================================
    
    // AJAX: Retorna categorias que possuem VÍNCULO com o grupo
    public function getLinkedCategories() {
        $this->checkSession();
        header('Content-Type: application/json');

        $groupId = intval($_GET['group_id'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($groupId <= 0) {
            echo json_encode([]);
            exit;
        }

        $conn = Database::connect();
        
        // Estratégia: Se uma categoria tem produtos vinculados a este grupo, retorna o ID dela.
        // (Pode ajustar para "Se > metada dos produtos", mas "pelo menos 1" é mais rápido e seguro pra UI)
        $sql = "SELECT DISTINCT p.category_id 
                FROM product_additional_relations par
                JOIN products p ON par.product_id = p.id
                WHERE par.group_id = :gid AND p.restaurant_id = :rid";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['gid' => $groupId, 'rid' => $restaurantId]);
        
        $linkedCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode($linkedCategories);
        exit;
    }

    // POST: Salva (Sincroniza) vínculos
    public function linkCategory() {
        $this->checkSession();
        
        $groupId = intval($_POST['group_id'] ?? 0);
        $categoryIds = $_POST['category_ids'] ?? []; // Array de IDs MARCADOS
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($groupId > 0) {
            $conn = Database::connect();

            // 1. Verifica segurança
            $stmtCheck = $conn->prepare("SELECT id FROM additional_groups WHERE id = :gid AND restaurant_id = :rid");
            $stmtCheck->execute(['gid' => $groupId, 'rid' => $restaurantId]);
            if (!$stmtCheck->fetch()) {
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=grupo_invalido');
                exit;
            }

            // 2. Busca TODAS as categorias da loja para saber quais foram DESMARCADAS
            $stmtAllCats = $conn->prepare("SELECT id FROM categories WHERE restaurant_id = :rid");
            $stmtAllCats->execute(['rid' => $restaurantId]);
            $allCategoryIds = $stmtAllCats->fetchAll(PDO::FETCH_COLUMN);

            // Prepara Statements
            $stmtGetProds = $conn->prepare("SELECT id FROM products WHERE category_id = :cid AND restaurant_id = :rid");
            $stmtIns = $conn->prepare("INSERT IGNORE INTO product_additional_relations (product_id, group_id) VALUES (:pid, :gid)");
            $stmtDel = $conn->prepare("DELETE FROM product_additional_relations WHERE product_id = :pid AND group_id = :gid");

            // 3. Itera sobre TODAS as categorias
            foreach ($allCategoryIds as $cid) {
                // A categoria está na lista de Marcados?
                if (in_array($cid, $categoryIds)) {
                    // SIM: Vincular todos os produtos
                    $stmtGetProds->execute(['cid' => $cid, 'rid' => $restaurantId]);
                    $products = $stmtGetProds->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($products as $pid) {
                        $stmtIns->execute(['pid' => $pid, 'gid' => $groupId]);
                    }
                } else {
                    // NÃO: Desvincular todos os produtos (pois foi desmarcada ou nunca marcada)
                    // AÇÃO DESTRUTIVA: Remove vínculo deste grupo para produtos desta categoria
                    $stmtGetProds->execute(['cid' => $cid, 'rid' => $restaurantId]);
                    $products = $stmtGetProds->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($products as $pid) {
                        $stmtDel->execute(['pid' => $pid, 'gid' => $groupId]);
                    }
                }
            }
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=vinculo_sincronizado');
        exit;
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
