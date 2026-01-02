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
    // ==========================================
    // CATÁLOGO GLOBAL DE ITENS (Descontinuado - Redirecionado para index)
    // ==========================================
    // public function listItems() { ... } Removido em favor da view unificada
        


    // ==========================================
    // GRUPOS - CRUD
    // ==========================================
    
    public function storeGroup() {
        $this->checkSession();
        
        $name = trim($_POST['name'] ?? '');
        $itemIds = $_POST['item_ids'] ?? []; // Array de IDs de itens para vincular
        $restaurantId = $_SESSION['loja_ativa_id'];

        if (!empty($name)) {
            $conn = Database::connect();
            
            try {
                $conn->beginTransaction();

                // 1. Criar Grupo
                $stmt = $conn->prepare("INSERT INTO additional_groups (restaurant_id, name, required) VALUES (:rid, :name, 0)");
                $stmt->execute([
                    'rid' => $restaurantId,
                    'name' => $name
                ]);
                $groupId = $conn->lastInsertId();

                // 2. Vincular Itens Selecionados
                if (!empty($itemIds) && is_array($itemIds)) {
                    $sqlLink = "INSERT INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)";
                    $stmtLink = $conn->prepare($sqlLink);

                    foreach ($itemIds as $iid) {
                        // Verifica se o item pertence à loja
                        $checkItem = $conn->prepare("SELECT id FROM additionals WHERE id = :iid AND restaurant_id = :rid");
                        $checkItem->execute(['iid' => $iid, 'rid' => $restaurantId]);
                        
                        if ($checkItem->fetch()) {
                            $stmtLink->execute(['gid' => $groupId, 'iid' => $iid]);
                        }
                    }
                }

                $conn->commit();
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=grupo_criado');
                exit;

            } catch (Exception $e) {
                $conn->rollBack();
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=erro_criar_grupo');
                exit;
            }
        } else {
             header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=nome_obrigatorio');
             exit;
        }
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

    // [NOVO] Vincular múltiplos itens de uma vez
    public function linkMultipleItems() {
        $this->checkSession();
        
        $groupId = intval($_POST['group_id'] ?? 0);
        $itemIds = $_POST['item_ids'] ?? []; // Array de IDs selecionados
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($groupId > 0 && !empty($itemIds)) {
            $conn = Database::connect();
            
            // Verifica se grupo pertence à loja
            $stmt = $conn->prepare("SELECT id FROM additional_groups WHERE id = :gid AND restaurant_id = :rid");
            $stmt->execute(['gid' => $groupId, 'rid' => $restaurantId]);
            
            if ($stmt->fetch()) {
                $stmtIns = $conn->prepare("INSERT IGNORE INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)");
                
                foreach ($itemIds as $itemId) {
                    $itemId = intval($itemId);
                    if ($itemId > 0) {
                        $stmtIns->execute(['gid' => $groupId, 'iid' => $itemId]);
                    }
                }
            }
        }

        header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=itens_vinculados');
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
    // MÉTODO PARA SALVAR ITEM + VÍNCULOS (MODAL)
    // ==========================================
    public function storeItemWithGroups() {
        $this->checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            // Tratamento de Moeda BR (1.200,50 -> 1200.50)
            $priceRaw = $_POST['price'] ?? '0';
            $priceRaw = str_replace('.', '', $priceRaw); // Remove ponto de milhar
            $priceRaw = str_replace(',', '.', $priceRaw); // Troca vírgula por ponto
            $price = floatval($priceRaw);

            $groupIds = $_POST['group_ids'] ?? []; // Array de IDs de grupos
            $restaurantId = $_SESSION['loja_ativa_id'];
            
            if (empty($name)) {
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=nome_obrigatorio');
                exit;
            }

            $conn = Database::connect();
            
            try {
                // Iniciar transação para garantir integridade
                $conn->beginTransaction();

                // 1. Inserir Item
                $stmt = $conn->prepare("INSERT INTO additional_items (restaurant_id, name, price) VALUES (:rid, :name, :price)");
                $stmt->execute([
                    'rid' => $restaurantId,
                    'name' => $name,
                    'price' => $price
                ]);
                $itemId = $conn->lastInsertId();

                // 2. Vincular aos Grupos Selecionados
                if (!empty($groupIds) && is_array($groupIds)) {
                    $sqlLink = "INSERT INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)";
                    $stmtLink = $conn->prepare($sqlLink);

                    foreach ($groupIds as $gid) {
                        // Verificar se grupo pertence à loja (segurança)
                        $checkGroup = $conn->prepare("SELECT id FROM additional_groups WHERE id = :gid AND restaurant_id = :rid");
                        $checkGroup->execute(['gid' => $gid, 'rid' => $restaurantId]);
                        
                        if ($checkGroup->fetch()) {
                            $stmtLink->execute(['gid' => $gid, 'iid' => $itemId]);
                        }
                    }
                }

                $conn->commit();
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=item_criado');
                exit;

            } catch (Exception $e) {
                $conn->rollBack();
                // Logar erro se necessário
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=erro_banco');
                exit;
            }
        }
    }

    // ==========================================
    // MÉTODO PARA ATUALIZAR ITEM + VÍNCULOS (MODAL)
    // ==========================================
    public function updateItemWithGroups() {
        $this->checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            
            // Tratamento de Moeda BR
            $priceRaw = $_POST['price'] ?? '0';
            $priceRaw = str_replace('.', '', $priceRaw);
            $priceRaw = str_replace(',', '.', $priceRaw);
            $price = floatval($priceRaw);

            $groupIds = $_POST['group_ids'] ?? [];
            $restaurantId = $_SESSION['loja_ativa_id'];
            
            if ($id <= 0 || empty($name)) {
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=dados_invalidos');
                exit;
            }

            $conn = Database::connect();
            
            try {
                $conn->beginTransaction();

                // 1. Atualizar Item
                $stmt = $conn->prepare("UPDATE additional_items SET name = :name, price = :price WHERE id = :id AND restaurant_id = :rid");
                $stmt->execute([
                    'name' => $name,
                    'price' => $price,
                    'id' => $id,
                    'rid' => $restaurantId
                ]);

                // 2. Sincronizar Grupos (Remove todos e reinsere os selecionados)
                // Remove atuais
                $stmtDel = $conn->prepare("DELETE FROM additional_group_items WHERE item_id = :iid");
                $stmtDel->execute(['iid' => $id]);

                // Insere novos
                if (!empty($groupIds) && is_array($groupIds)) {
                    $sqlLink = "INSERT INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)";
                    $stmtLink = $conn->prepare($sqlLink);

                    foreach ($groupIds as $gid) {
                        $checkGroup = $conn->prepare("SELECT id FROM additional_groups WHERE id = :gid AND restaurant_id = :rid");
                        $checkGroup->execute(['gid' => $gid, 'rid' => $restaurantId]);
                        
                        if ($checkGroup->fetch()) {
                            $stmtLink->execute(['gid' => $gid, 'iid' => $id]);
                        }
                    }
                }

                $conn->commit();
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=item_atualizado');
                exit;

            } catch (Exception $e) {
                $conn->rollBack();
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=erro_atualizar_item');
                exit;
            }
        }
    }

    // ==========================================
    // API: DADOS DO ITEM (AJAX)
    // ==========================================
    public function getItemData() {
        header('Content-Type: application/json');

        try {
            $this->checkSession();
            $id = intval($_GET['id'] ?? 0);
            $restaurantId = $_SESSION['loja_ativa_id'];

            if ($id <= 0) {
                echo json_encode(['error' => 'ID inválido']);
                exit;
            }

            $conn = Database::connect();
            
            // Dados do Item
            $stmt = $conn->prepare("SELECT * FROM additional_items WHERE id = :id AND restaurant_id = :rid");
            $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                echo json_encode(['error' => 'Item não encontrado']);
                exit;
            }

            // Grupos Vinculados
            $stmtGroups = $conn->prepare("SELECT group_id FROM additional_group_items WHERE item_id = :id");
            $stmtGroups->execute(['id' => $id]);
            $groupIds = $stmtGroups->fetchAll(PDO::FETCH_COLUMN);

            echo json_encode([
                'item' => $item,
                'groups' => $groupIds
            ]);
            exit;

        } catch (\Throwable $e) {
            // Captura Erros Fatais e Exceptions
            http_response_code(500);
            echo json_encode(['error' => 'Erro no Servidor: ' . $e->getMessage()]);
            exit;
        }
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
