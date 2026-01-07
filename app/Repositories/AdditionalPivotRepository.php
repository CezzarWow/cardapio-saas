<?php

namespace App\Repositories;

use App\Core\Database;

class AdditionalPivotRepository
{
    /**
     * Vincula um item a um grupo (INSERT IGNORE)
     */
    public function link(int $groupId, int $itemId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT IGNORE INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)");
        $stmt->execute([
            'gid' => $groupId,
            'iid' => $itemId
        ]);
    }

    /**
     * Remove todos os vínculos de um item
     */
    public function unlinkAllByItem(int $itemId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM additional_group_items WHERE item_id = :iid");
        $stmt->execute(['iid' => $itemId]);
    }
}
