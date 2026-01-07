<?php

namespace App\Services\Additional;

use App\Repositories\AdditionalPivotRepository;
use App\Repositories\AdditionalGroupRepository;

/**
 * Service para vincular itens a grupos
 * Suporta link simples e link múltiplo (batch)
 */
class LinkItemService
{
    private AdditionalPivotRepository $pivotRepository;
    private AdditionalGroupRepository $groupRepository;

    public function __construct()
    {
        $this->pivotRepository = new AdditionalPivotRepository();
        $this->groupRepository = new AdditionalGroupRepository();
    }

    /**
     * Vincula um único item a um grupo
     * 
     * @param int $groupId ID do grupo
     * @param int $itemId ID do item
     * @param int $restaurantId ID do restaurante (validação de segurança)
     * @return bool true se vinculou, false se grupo não pertence ao restaurante
     */
    public function linkSingle(int $groupId, int $itemId, int $restaurantId): bool
    {
        // Verifica se grupo pertence ao restaurante
        if (!$this->groupRepository->findById($groupId, $restaurantId)) {
            return false;
        }

        $this->pivotRepository->link($groupId, $itemId);
        return true;
    }

    /**
     * Vincula múltiplos itens a um grupo de uma vez
     * 
     * @param int $groupId ID do grupo
     * @param array $itemIds Array de IDs dos itens
     * @param int $restaurantId ID do restaurante (validação de segurança)
     * @return bool true se vinculou, false se grupo não pertence ao restaurante
     */
    public function linkMultiple(int $groupId, array $itemIds, int $restaurantId): bool
    {
        // Verifica se grupo pertence ao restaurante
        if (!$this->groupRepository->findById($groupId, $restaurantId)) {
            return false;
        }

        // Filtra IDs válidos
        $validIds = array_filter(array_map('intval', $itemIds), fn($id) => $id > 0);
        
        if (!empty($validIds)) {
            $this->pivotRepository->linkMultiple($groupId, $validIds);
        }
        
        return true;
    }
}
