<?php

namespace App\Services\Additional;

use App\Repositories\AdditionalCategoryRepository;
use App\Repositories\AdditionalGroupRepository;

/**
 * Service para vincular/desvincular categorias inteiras a um grupo
 * Vincula todos os produtos de categorias selecionadas ao grupo
 */
class LinkCategoryService
{
    private AdditionalCategoryRepository $categoryRepository;
    private AdditionalGroupRepository $groupRepository;

    public function __construct()
    {
        $this->categoryRepository = new AdditionalCategoryRepository();
        $this->groupRepository = new AdditionalGroupRepository();
    }

    /**
     * Sincroniza as categorias vinculadas a um grupo
     * - Categorias selecionadas: todos os produtos recebem vínculo
     * - Categorias desmarcadas: produtos perdem vínculo
     * 
     * @param int $groupId ID do grupo
     * @param array $categoryIds IDs das categorias selecionadas
     * @param int $restaurantId ID do restaurante
     * @return bool true se sincronizou, false se grupo não pertence ao restaurante
     */
    public function execute(int $groupId, array $categoryIds, int $restaurantId): bool
    {
        // Verifica se grupo pertence ao restaurante
        if (!$this->groupRepository->findById($groupId, $restaurantId)) {
            return false;
        }

        // Filtra IDs válidos
        $validIds = array_filter(array_map('intval', $categoryIds), fn($id) => $id > 0);
        
        // Delega para o repository
        $this->categoryRepository->syncCategories($groupId, $validIds, $restaurantId);
        
        return true;
    }

    /**
     * Retorna IDs das categorias que têm produtos vinculados ao grupo
     */
    public function getLinkedCategories(int $groupId, int $restaurantId): array
    {
        return $this->categoryRepository->getLinkedCategories($groupId, $restaurantId);
    }
}
