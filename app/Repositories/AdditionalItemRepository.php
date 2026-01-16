<?php

namespace App\Repositories;

use App\Core\Database;
use App\Core\Cache;
use PDO;

/**
 * Repository para Itens de Adicionais
 * SQL puro, sem lógica de negócio
 */
class AdditionalItemRepository
{
    /**
     * Insere um novo item de adicional
     */
    public function save(int $restaurantId, string $name, float $price): int
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('INSERT INTO additional_items (restaurant_id, name, price) VALUES (:rid, :name, :price)');
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $name,
            'price' => $price
        ]);

        $id = (int) $conn->lastInsertId();
        try {
            $cache = new Cache();
            $cache->forget('additionals_' . $restaurantId);
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
        }
        return $id;
    }

    /**
     * Atualiza um item existente
     */
    public function update(int $id, int $restaurantId, string $name, float $price): void
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('UPDATE additional_items SET name = :name, price = :price WHERE id = :id AND restaurant_id = :rid');
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'id' => $id,
            'rid' => $restaurantId
        ]);
        try {
            $cache = new Cache();
            $cache->forget('additionals_' . $restaurantId);
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
        }
    }

    /**
     * Deleta um item
     * Nota: vínculos em additional_group_items serão removidos por CASCADE ou manualmente
     */
    public function delete(int $id, int $restaurantId): void
    {
        $conn = Database::connect();

        // Remove vínculos primeiro (se não tiver CASCADE)
        $stmt = $conn->prepare('DELETE FROM additional_group_items WHERE item_id = :id');
        $stmt->execute(['id' => $id]);

        // Remove o item
        $stmt = $conn->prepare('DELETE FROM additional_items WHERE id = :id AND restaurant_id = :rid');
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        try {
            $cache = new Cache();
            $cache->forget('additionals_' . $restaurantId);
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
        }
    }

    /**
     * Busca um item por ID
     */
    public function findById(int $id, int $restaurantId): ?array
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('SELECT * FROM additional_items WHERE id = :id AND restaurant_id = :rid');
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca todos os itens de um restaurante
     */
    public function findAll(int $restaurantId): array
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('SELECT * FROM additional_items WHERE restaurant_id = :rid ORDER BY name ASC');
        $stmt->execute(['rid' => $restaurantId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca IDs dos grupos vinculados a um item
     */
    public function getGroupsForItem(int $itemId): array
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('SELECT group_id FROM additional_group_items WHERE item_id = :id');
        $stmt->execute(['id' => $itemId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Busca grupos (com itens) vinculados a um produto específico
     * Usado pela API do PDV para exibir adicionais disponíveis
     */
    public function findByProduct(int $productId, int $restaurantId): array
    {
        $conn = Database::connect();

        // 1. Busca Grupos vinculados ao produto
        $sqlGroups = 'SELECT DISTINCT ag.id, ag.name, ag.required 
                      FROM additional_groups ag
                      JOIN product_additional_relations par ON par.group_id = ag.id
                      WHERE par.product_id = :pid AND ag.restaurant_id = :rid
                      ORDER BY ag.id ASC';

        $stmtGroups = $conn->prepare($sqlGroups);
        $stmtGroups->execute(['pid' => $productId, 'rid' => $restaurantId]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        // 2. Para cada grupo, busca os itens
        foreach ($groups as &$group) {
            $sqlItems = 'SELECT DISTINCT ai.id, ai.name, ai.price 
                         FROM additional_items ai
                         JOIN additional_group_items agi ON agi.item_id = ai.id
                         WHERE agi.group_id = :gid AND ai.restaurant_id = :rid
                         ORDER BY ai.price ASC, ai.name ASC';

            $stmtItems = $conn->prepare($sqlItems);
            $stmtItems->execute(['gid' => $group['id'], 'rid' => $restaurantId]);
            $group['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($group);

        return $groups;
    }
}
