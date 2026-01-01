<?php
/**
 * ============================================
 * COMBO SERVICE
 * Gerencia operações de combos (CRUD)
 * 
 * NOTA: Este service acessa $_POST, $_SESSION e Database::connect()
 * diretamente, mantendo o mesmo padrão do controller original.
 * ============================================
 */

namespace App\Services\Admin;

use App\Core\Database;
use PDO;

class ComboService {

    /**
     * [ETAPA 3] Salva novo combo
     */
    public function store() {
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $price = str_replace(',', '.', $_POST['price'] ?? '0');
        $price = preg_replace('/[^\d.]/', '', $price);

        // Inserir combo
        $stmt = $conn->prepare("INSERT INTO combos (restaurant_id, name, description, price, display_order, is_active) VALUES (:rid, :name, :desc, :price, :order, :active)");
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => trim($_POST['name']),
            'desc' => trim($_POST['description'] ?? ''),
            'price' => floatval($price),
            'order' => intval($_POST['display_order'] ?? 0),
            'active' => isset($_POST['is_active']) ? 1 : 0
        ]);

        $comboId = $conn->lastInsertId();

        // Inserir produtos do combo
        $products = $_POST['products'] ?? [];
        $allowAdditionals = $_POST['allow_additionals'] ?? [];
        
        if (!empty($products)) {
            $stmtItem = $conn->prepare("INSERT INTO combo_items (combo_id, product_id, allow_additionals) VALUES (:cid, :pid, :allow)");
            foreach ($products as $pid) {
                $allow = isset($allowAdditionals[$pid]) ? 1 : 0;
                $stmtItem->execute(['cid' => $comboId, 'pid' => $pid, 'allow' => $allow]);
            }
        }

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=combo_criado');
        exit;
    }

    /**
     * [ETAPA 3] Busca combo para edição
     * Suporta resposta JSON para edição in-place via AJAX
     */
    public function getForEdit() {
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $id = intval($_GET['id'] ?? 0);
        $isAjax = isset($_GET['json']) && $_GET['json'] == '1';

        // Buscar combo
        $stmt = $conn->prepare("SELECT * FROM combos WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        $combo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$combo) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Combo não encontrado']);
                exit;
            }
            header('Location: ' . BASE_URL . '/admin/loja/cardapio?error=combo_nao_encontrado');
            exit;
        }

        // Buscar produtos do combo com configurações e nomes
        $stmtItems = $conn->prepare("
            SELECT ci.product_id, ci.allow_additionals, p.name as product_name, p.price as product_price
            FROM combo_items ci
            JOIN products p ON p.id = ci.product_id
            WHERE ci.combo_id = :cid
        ");
        $stmtItems->execute(['cid' => $id]);
        $rawItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        
        $comboProducts = [];
        $comboItemsSettings = [];
        $comboItemsDetails = []; // Para a lista resumo com nomes
        
        foreach ($rawItems as $item) {
            $pid = $item['product_id'];
            $comboProducts[] = $pid;
            $comboItemsSettings[$pid] = [
                'allow_additionals' => $item['allow_additionals']
            ];
            
            // Contabiliza quantidade por produto (pode haver duplicatas)
            if (!isset($comboItemsDetails[$pid])) {
                $comboItemsDetails[$pid] = [
                    'id' => $pid,
                    'name' => $item['product_name'],
                    'price' => $item['product_price'],
                    'qty' => 0,
                    'allow_additionals' => $item['allow_additionals']
                ];
            }
            $comboItemsDetails[$pid]['qty']++;
        }

        // Se for requisição AJAX, retorna JSON
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'combo' => $combo,
                'items' => array_values($comboItemsDetails), // Lista com qty agrupada
                'settings' => $comboItemsSettings
            ]);
            exit;
        }

        // Retorna dados para uso no controller (renderização de view)
        return [
            'combo' => $combo,
            'comboProducts' => $comboProducts,
            'comboItemsSettings' => $comboItemsSettings
        ];
    }

    /**
     * [ETAPA 3] Atualiza combo
     */
    public function update() {
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $id = intval($_POST['id'] ?? 0);
        $price = str_replace(',', '.', $_POST['price'] ?? '0');
        $price = preg_replace('/[^\d.]/', '', $price);

        // Atualizar combo
        $stmt = $conn->prepare("UPDATE combos SET name = :name, description = :desc, price = :price, display_order = :order, is_active = :active WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute([
            'name' => trim($_POST['name']),
            'desc' => trim($_POST['description'] ?? ''),
            'price' => floatval($price),
            'order' => intval($_POST['display_order'] ?? 0),
            'active' => isset($_POST['is_active']) ? 1 : 0,
            'id' => $id,
            'rid' => $restaurantId
        ]);

        // Atualizar produtos do combo
        $conn->prepare("DELETE FROM combo_items WHERE combo_id = :cid")->execute(['cid' => $id]);
        
        $products = $_POST['products'] ?? [];
        $allowAdditionals = $_POST['allow_additionals'] ?? [];
        
        if (!empty($products)) {
            $stmtItem = $conn->prepare("INSERT INTO combo_items (combo_id, product_id, allow_additionals) VALUES (:cid, :pid, :allow)");
            foreach ($products as $pid) {
                $allow = isset($allowAdditionals[$pid]) ? 1 : 0;
                $stmtItem->execute(['cid' => $id, 'pid' => $pid, 'allow' => $allow]);
            }
        }

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=combo_atualizado');
        exit;
    }

    /**
     * [ETAPA 3] Deleta combo
     */
    public function delete() {
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $id = intval($_GET['id'] ?? 0);

        $conn->prepare("DELETE FROM combos WHERE id = :id AND restaurant_id = :rid")
             ->execute(['id' => $id, 'rid' => $restaurantId]);

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=combo_deletado');
        exit;
    }

    /**
     * [AJAX] Alterna status do combo
     */
    public function toggleStatus() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $active = !empty($data['active']) ? 1 : 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }

        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $stmt = $conn->prepare("UPDATE combos SET is_active = :active WHERE id = :id AND restaurant_id = :rid");
        $result = $stmt->execute([
            'active' => $active,
            'id' => $id,
            'rid' => $restaurantId
        ]);

        echo json_encode(['success' => $result]);
        exit;
    }
}
