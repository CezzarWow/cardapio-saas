<?php

namespace App\Repositories;

use App\Core\Database;
use App\Core\Cache;

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
        try {
            $cache = new Cache();
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
        }
    }

    /**
     * Remove todos os vínculos de um item
     */
    public function unlinkAllByItem(int $itemId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('DELETE FROM additional_group_items WHERE item_id = :iid');
        $stmt->execute(['iid' => $itemId]);
        try {
            $cache = new Cache();
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
        }
    }

    /**
     * Remove um vínculo específico grupo-item
     */
    public function unlink(int $groupId, int $itemId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('DELETE FROM additional_group_items WHERE group_id = :gid AND item_id = :iid');
        $stmt->execute(['gid' => $groupId, 'iid' => $itemId]);
        try {
            $cache = new Cache();
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
        }
    }

    /**
     * Sincroniza os itens de um grupo (remove todos e reinsere apenas os selecionados)
     * Usado quando o modal de vincular itens é submetido
     */
    public function syncItemsForGroup(int $groupId, array $itemIds): void
    {
        $conn = Database::connect();

        // Remove todos os vínculos atuais do grupo
        $stmt = $conn->prepare('DELETE FROM additional_group_items WHERE group_id = :gid');
        $stmt->execute(['gid' => $groupId]);

        // Insere os novos vínculos
        if (!empty($itemIds)) {
            $stmtLink = $conn->prepare('INSERT INTO additional_group_items (group_id, item_id) VALUES (:gid, :iid)');
            foreach ($itemIds as $iid) {
                $iid = intval($iid);
                if ($iid > 0) {
                    $stmtLink->execute(['gid' => $groupId, 'iid' => $iid]);
                }
            }
        }
        try {
            $cache = new Cache();
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
        }
    }
}
