<?php

namespace App\Services\Additional;

use App\Repositories\AdditionalGroupRepository;

/**
 * Service para deletar Grupo de Adicionais
 * O repository já cuida de remover vínculos em cascade
 */
class DeleteGroupService
{
    private AdditionalGroupRepository $groupRepository;

    public function __construct()
    {
        $this->groupRepository = new AdditionalGroupRepository();
    }

    /**
     * Deleta um grupo de adicionais e seus vínculos
     * 
     * @param int $id ID do grupo
     * @param int $restaurantId ID do restaurante (segurança)
     * @return bool true se deletou, false se não encontrou
     */
    public function execute(int $id, int $restaurantId): bool
    {
        if ($id <= 0) {
            return false;
        }

        // Verifica se grupo existe e pertence ao restaurante
        $existingGroup = $this->groupRepository->findById($id, $restaurantId);
        if (!$existingGroup) {
            return false;
        }

        // Deleta (repository remove vínculos automaticamente)
        $this->groupRepository->delete($id);
        return true;
    }
}
