<?php

namespace App\Services\Additional;

use App\Repositories\AdditionalPivotRepository;
use App\Repositories\AdditionalGroupRepository;

/**
 * Service para desvincular item de um grupo
 */
class UnlinkItemService
{
    private AdditionalPivotRepository $pivotRepository;
    private AdditionalGroupRepository $groupRepository;

    public function __construct()
    {
        $this->pivotRepository = new AdditionalPivotRepository();
        $this->groupRepository = new AdditionalGroupRepository();
    }

    /**
     * Remove o vínculo de um item com um grupo específico
     * 
     * @param int $groupId ID do grupo
     * @param int $itemId ID do item
     * @param int $restaurantId ID do restaurante (validação de segurança)
     * @return bool true se desvinculou, false se grupo não pertence ao restaurante
     */
    public function execute(int $groupId, int $itemId, int $restaurantId): bool
    {
        // Verifica se grupo pertence ao restaurante
        if (!$this->groupRepository->findById($groupId, $restaurantId)) {
            return false;
        }

        $this->pivotRepository->unlink($groupId, $itemId);
        return true;
    }
}
