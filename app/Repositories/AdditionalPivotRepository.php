<?php

namespace App\Repositories;

use App\Core\Database;
use App\Events\CardapioChangedEvent;
use App\Events\EventDispatcher;

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
        
        $this->dispatchGroupEvent($groupId);
    }

    /**
     * Remove todos os vínculos de um item
     */
    public function unlinkAllByItem(int $itemId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('DELETE FROM additional_group_items WHERE item_id = :iid');
        $stmt->execute(['iid' => $itemId]);
        
        // Buscamos restaurantId pelo item (vínculos já foram apagados, mas item ainda deve existir)
        // Se item também for apagado em seguida, o repo de item disparará o evento. 
        // Mas se for só desvínculo, precisamos disparar aqui.
        $stmtInfo = $conn->prepare('SELECT restaurant_id FROM additional_items WHERE id = :id');
        $stmtInfo->execute(['id' => $itemId]);
        $rid = $stmtInfo->fetchColumn();
        
        if ($rid) {
            EventDispatcher::dispatch(new CardapioChangedEvent((int)$rid));
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
        
        $this->dispatchGroupEvent($groupId);
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
        
        $this->dispatchGroupEvent($groupId);
    }
    
    private function dispatchGroupEvent(int $groupId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT restaurant_id FROM additional_groups WHERE id = :id');
        $stmt->execute(['id' => $groupId]);
        $rid = $stmt->fetchColumn();
        
        if ($rid) {
            EventDispatcher::dispatch(new CardapioChangedEvent((int)$rid));
        }
    }
}
