<?php

namespace App\Repositories\Cardapio;

use App\Core\Database;
use PDO;

/**
 * Repository para Categorias do Cardápio
 */
class CategoryRepository
{
    /**
     * Busca todas as categorias ordenadas
     */
    public function findAllOrdered(int $restaurantId): array
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('
            SELECT * FROM categories 
            WHERE restaurant_id = :rid 
            ORDER BY COALESCE(sort_order, 999) ASC, name ASC
        ');
        $stmt->execute(['rid' => $restaurantId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza ordem de exibição de uma categoria
     */
    public function updateOrder(int $id, int $order, int $restaurantId): void
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('UPDATE categories SET sort_order = :order WHERE id = :cid AND restaurant_id = :rid');
        $stmt->execute(['order' => $order, 'cid' => $id, 'rid' => $restaurantId]);
    }

    /**
     * Atualiza estado de ativação (is_active) de uma categoria
     */
    public function updateActive(int $id, bool $active, int $restaurantId): void
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('UPDATE categories SET is_active = :active WHERE id = :cid AND restaurant_id = :rid');
        $stmt->execute(['active' => $active ? 1 : 0, 'cid' => $id, 'rid' => $restaurantId]);
    }

    /**
     * Sincroniza ordem e estado de ativação para múltiplas categorias
     */
    public function syncOrderAndActive(int $restaurantId, array $orderData, array $enabledIds): void
    {
        $conn = Database::connect();

        // Primeiro, desabilita todas
        $conn->prepare('UPDATE categories SET is_active = 0 WHERE restaurant_id = :rid')
             ->execute(['rid' => $restaurantId]);

        // Atualiza ordem
        if (!empty($orderData)) {
            $stmtOrder = $conn->prepare('UPDATE categories SET sort_order = :order WHERE id = :cid AND restaurant_id = :rid');
            foreach ($orderData as $catId => $order) {
                $stmtOrder->execute([
                    'order' => intval($order),
                    'cid' => intval($catId),
                    'rid' => $restaurantId
                ]);
            }
        }

        // Habilita as selecionadas
        if (!empty($enabledIds)) {
            $stmtEnable = $conn->prepare('UPDATE categories SET is_active = 1 WHERE id = :cid AND restaurant_id = :rid');
            foreach (array_keys($enabledIds) as $catId) {
                $stmtEnable->execute(['cid' => intval($catId), 'rid' => $restaurantId]);
            }
        }
    }

    /**
     * Garante que as categorias de sistema existam (Combos e Destaques)
     */
    public function ensureSystemCategories(int $restaurantId): void
    {
        $conn = Database::connect();

        $systemTypes = [
            'combos' => ['name' => '🔥 Combos', 'default_order' => -10],
            'featured' => ['name' => '⭐ Destaques', 'default_order' => -5]
        ];

        $stmtCheck = $conn->prepare('SELECT id FROM categories WHERE restaurant_id = :rid AND category_type = :type');
        $stmtInsert = $conn->prepare('
            INSERT INTO categories (restaurant_id, name, category_type, sort_order, is_active) 
            VALUES (:rid, :name, :type, :order, 1)
        ');

        foreach ($systemTypes as $type => $info) {
            $stmtCheck->execute(['rid' => $restaurantId, 'type' => $type]);
            if (!$stmtCheck->fetch()) {
                $stmtInsert->execute([
                    'rid' => $restaurantId,
                    'name' => $info['name'],
                    'type' => $type,
                    'order' => $info['default_order']
                ]);
            }
        }
    }
}
