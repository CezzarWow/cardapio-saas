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
        $stmt = $conn->prepare('INSERT IGNORE INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)');
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
        $stmt = $conn->prepare('DELETE FROM additional_group_items WHERE item_id = :iid');
        $stmt->execute(['iid' => $itemId]);
    }

    /**
     * Remove um vínculo específico grupo-item
     */
    public function unlink(int $groupId, int $itemId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('DELETE FROM additional_group_items WHERE group_id = :gid AND item_id = :iid');
        $stmt->execute(['gid' => $groupId, 'iid' => $itemId]);
    }

    /**
     * Vincula múltiplos itens a um grupo de uma vez (INSERT IGNORE)
     */
    public function linkMultiple(int $groupId, array $itemIds): void
    {
        if (empty($itemIds)) {
            return;
        }

        $conn = Database::connect();
        $stmt = $conn->prepare('INSERT IGNORE INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)');

        foreach ($itemIds as $itemId) {
            $itemId = intval($itemId);
            if ($itemId > 0) {
                $stmt->execute(['gid' => $groupId, 'iid' => $itemId]);
            }
        }
    }

    /**
     * Sincroniza os grupos de um item (remove e reinsere)
     * Usado ao atualizar um item para alterar seus vínculos
     */
    public function syncGroupsForItem(int $itemId, array $groupIds): void
    {
        $conn = Database::connect();

        // Remove todos os vínculos atuais
        $stmt = $conn->prepare('DELETE FROM additional_group_items WHERE item_id = :iid');
        $stmt->execute(['iid' => $itemId]);

        // Insere os novos vínculos
        if (!empty($groupIds)) {
            $stmtLink = $conn->prepare('INSERT INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)');
            foreach ($groupIds as $gid) {
                $gid = intval($gid);
                if ($gid > 0) {
                    $stmtLink->execute(['gid' => $gid, 'iid' => $itemId]);
                }
            }
        }
    }
}
