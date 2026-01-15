<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class AdditionalCategoryRepository
{
    public function findAllCategories(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM additional_categories WHERE restaurant_id = :rid ORDER BY name ASC');
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLinkedCategories(int $groupId, int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            SELECT c.*
            FROM additional_categories c
            JOIN additional_group_categories agc ON agc.category_id = c.id
            WHERE agc.group_id = :gid AND c.restaurant_id = :rid
        ');
        $stmt->execute(['gid' => $groupId, 'rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function unlinkAll(int $groupId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('DELETE FROM additional_group_categories WHERE group_id = :gid');
        $stmt->execute(['gid' => $groupId]);
    }

    public function link(int $groupId, int $categoryId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('INSERT INTO additional_group_categories (group_id, category_id) VALUES (:gid, :cid)');
        $stmt->execute(['gid' => $groupId, 'cid' => $categoryId]);
    }
}
