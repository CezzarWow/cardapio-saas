<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ComboRepository {

    /**
     * Cria novo combo
     */
    public function create(array $data): int {
        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO combos (restaurant_id, name, description, price, display_order, is_active) VALUES (:rid, :name, :desc, :price, :order, :active)");
        $stmt->execute([
            'rid' => $data['restaurant_id'],
            'name' => $data['name'],
            'desc' => $data['description'],
            'price' => $data['price'],
            'order' => $data['display_order'],
            'active' => $data['is_active']
        ]);
        return (int) $conn->lastInsertId();
    }

    /**
     * Salva itens do combo
     */
    public function saveItems(int $comboId, array $products, array $allowAdditionals): void {
        $conn = Database::connect();
        // Remove existing
        $conn->prepare("DELETE FROM combo_items WHERE combo_id = :cid")->execute(['cid' => $comboId]);

        if (!empty($products)) {
            $stmt = $conn->prepare("INSERT INTO combo_items (combo_id, product_id, allow_additionals) VALUES (:cid, :pid, :allow)");
            foreach ($products as $pid) {
                $allow = isset($allowAdditionals[$pid]) ? 1 : 0;
                $stmt->execute(['cid' => $comboId, 'pid' => $pid, 'allow' => $allow]);
            }
        }
    }

    /**
     * Busca combo por ID
     */
    public function find(int $id, int $restaurantId): ?array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM combos WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca itens do combo com detalhes do produto
     */
    public function findItemsWithDetails(int $comboId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT ci.product_id, ci.allow_additionals, p.name as product_name, p.price as product_price
            FROM combo_items ci
            JOIN products p ON p.id = ci.product_id
            WHERE ci.combo_id = :cid
        ");
        $stmt->execute(['cid' => $comboId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista todos os combos de um restaurante
     */
    public function findAll(int $restaurantId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM combos WHERE restaurant_id = :rid ORDER BY display_order, name");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza combo
     */
    public function update(int $id, array $data): void {
        $conn = Database::connect();
        $sql = "UPDATE combos SET name = :name, description = :desc, price = :price, display_order = :order, is_active = :active WHERE id = :id AND restaurant_id = :rid";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'desc' => $data['description'],
            'price' => $data['price'],
            'order' => $data['display_order'],
            'active' => $data['is_active'],
            'id' => $id,
            'rid' => $data['restaurant_id']
        ]);
    }

    /**
     * Deleta combo
     */
    public function delete(int $id, int $restaurantId): void {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM combos WHERE id = :id AND restaurant_id = :rid")->execute(['id' => $id, 'rid' => $restaurantId]);
    }

    /**
     * Alterna status
     */
    public function toggleStatus(int $id, int $status, int $restaurantId): void {
        $conn = Database::connect();
        $conn->prepare("UPDATE combos SET is_active = :status WHERE id = :id AND restaurant_id = :rid")
             ->execute(['status' => $status, 'id' => $id, 'rid' => $restaurantId]);
    }

    /**
     * Busca todos os combos com seus itens (Eager Loading otimizado)
     */
    public function findAllWithItems(int $restaurantId): array {
        $conn = Database::connect();
        
        // 1. Busca Combos
        $stmtCombos = $conn->prepare("SELECT * FROM combos WHERE restaurant_id = :rid AND is_active = 1 ORDER BY display_order, name");
        $stmtCombos->execute(['rid' => $restaurantId]);
        $combos = $stmtCombos->fetchAll(PDO::FETCH_ASSOC);

        if (empty($combos)) {
            return [];
        }

        // 2. Busca Itens
        $comboIds = array_column($combos, 'id');
        $inQuery = implode(',', array_fill(0, count($comboIds), '?'));
        
        $stmtItems = $conn->prepare("
            SELECT ci.combo_id, p.name, p.price 
            FROM combo_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.combo_id IN ($inQuery)
        ");
        $stmtItems->execute($comboIds);
        $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // 3. Agrupa
        $itemsByCombo = [];
        foreach ($allItems as $item) {
            $itemsByCombo[$item['combo_id']][] = $item;
        }

        // 4. Monta resultado
        foreach ($combos as &$combo) {
            $combo['items'] = $itemsByCombo[$combo['id']] ?? [];
        }

        return $combos;
    }
}
