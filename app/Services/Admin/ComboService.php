<?php
/**
 * ============================================
 * COMBO SERVICE
 * Gerencia operações de combos (CRUD)
 * ============================================
 */

namespace App\Services\Admin;

use App\Core\Database;
use PDO;
use Exception;

class ComboService {

    /**
     * Salva novo combo
     */
    public function store(array $data, int $restaurantId): int {
        $conn = Database::connect();

        $price = str_replace(',', '.', $data['price'] ?? '0');
        $price = preg_replace('/[^\d.]/', '', $price);

        // Inserir combo
        $stmt = $conn->prepare("INSERT INTO combos (restaurant_id, name, description, price, display_order, is_active) VALUES (:rid, :name, :desc, :price, :order, :active)");
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => trim($data['name']),
            'desc' => trim($data['description'] ?? ''),
            'price' => floatval($price),
            'order' => intval($data['display_order'] ?? 0),
            'active' => isset($data['is_active']) ? 1 : 0
        ]);

        $comboId = $conn->lastInsertId();

        // Inserir produtos do combo
        $this->syncComboItems($conn, $comboId, $data['products'] ?? [], $data['allow_additionals'] ?? []);

        return $comboId;
    }

    /**
     * Busca combo para edição
     */
    public function getForEdit(int $comboId, int $restaurantId): ?array {
        $conn = Database::connect();

        // Buscar combo
        $stmt = $conn->prepare("SELECT * FROM combos WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $comboId, 'rid' => $restaurantId]);
        $combo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$combo) {
            return null;
        }

        // Buscar produtos
        $stmtItems = $conn->prepare("
            SELECT ci.product_id, ci.allow_additionals, p.name as product_name, p.price as product_price
            FROM combo_items ci
            JOIN products p ON p.id = ci.product_id
            WHERE ci.combo_id = :cid
        ");
        $stmtItems->execute(['cid' => $comboId]);
        $rawItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        
        $comboProducts = [];
        $comboItemsSettings = [];
        $comboItemsDetails = [];
        
        foreach ($rawItems as $item) {
            $pid = $item['product_id'];
            $comboProducts[] = $pid;
            $comboItemsSettings[$pid] = [
                'allow_additionals' => $item['allow_additionals']
            ];
            
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

        return [
            'combo' => $combo,
            'comboProducts' => $comboProducts,
            'comboItemsSettings' => $comboItemsSettings,
            'items' => array_values($comboItemsDetails)
        ];
    }

    /**
     * Atualiza combo
     */
    public function update(int $comboId, array $data, int $restaurantId): void {
        $conn = Database::connect();

        $price = str_replace(',', '.', $data['price'] ?? '0');
        $price = preg_replace('/[^\d.]/', '', $price);

        // Atualizar combo
        $stmt = $conn->prepare("UPDATE combos SET name = :name, description = :desc, price = :price, display_order = :order, is_active = :active WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute([
            'name' => trim($data['name']),
            'desc' => trim($data['description'] ?? ''),
            'price' => floatval($price),
            'order' => intval($data['display_order'] ?? 0),
            'active' => isset($data['is_active']) ? 1 : 0,
            'id' => $comboId,
            'rid' => $restaurantId
        ]);

        // Atualizar produtos
        $this->syncComboItems($conn, $comboId, $data['products'] ?? [], $data['allow_additionals'] ?? []);
    }

    /**
     * Deleta combo
     */
    public function delete(int $comboId, int $restaurantId): void {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM combos WHERE id = :id AND restaurant_id = :rid")
             ->execute(['id' => $comboId, 'rid' => $restaurantId]);
    }

    /**
     * Alterna status do combo
     */
    public function toggleStatus(int $comboId, bool $active, int $restaurantId): bool {
        $conn = Database::connect();
        $stmt = $conn->prepare("UPDATE combos SET is_active = :active WHERE id = :id AND restaurant_id = :rid");
        return $stmt->execute([
            'active' => $active ? 1 : 0,
            'id' => $comboId,
            'rid' => $restaurantId
        ]);
    }

    /**
     * Helper para sincronizar itens do combo
     */
    private function syncComboItems(PDO $conn, int $comboId, array $products, array $allowAdditionals): void {
        $conn->prepare("DELETE FROM combo_items WHERE combo_id = :cid")->execute(['cid' => $comboId]);
        
        if (!empty($products)) {
            $stmtItem = $conn->prepare("INSERT INTO combo_items (combo_id, product_id, allow_additionals) VALUES (:cid, :pid, :allow)");
            foreach ($products as $pid) {
                $allow = isset($allowAdditionals[$pid]) ? 1 : 0;
                $stmtItem->execute(['cid' => $comboId, 'pid' => $pid, 'allow' => $allow]);
            }
        }
    }
}
