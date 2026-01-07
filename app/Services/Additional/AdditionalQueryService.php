<?php

namespace App\Services\Additional;

use App\Repositories\AdditionalItemRepository;
use App\Repositories\AdditionalGroupRepository;

/**
 * Query Service para leituras de Adicionais
 * Centraliza consultas usadas por Views e APIs
 */
class AdditionalQueryService
{
    private AdditionalItemRepository $itemRepository;
    private AdditionalGroupRepository $groupRepository;

    public function __construct()
    {
        $this->itemRepository = new AdditionalItemRepository();
        $this->groupRepository = new AdditionalGroupRepository();
    }

    /**
     * Retorna dados de um item específico + grupos vinculados
     * Usado pela API de edição de item
     */
    public function getItemData(int $id, int $restaurantId): ?array
    {
        $item = $this->itemRepository->findById($id, $restaurantId);
        
        if (!$item) {
            return null;
        }

        return [
            'item' => $item,
            'groups' => $this->itemRepository->getGroupsForItem($id)
        ];
    }

    /**
     * Retorna adicionais disponíveis para um produto (API do PDV)
     * Retorna grupos com seus itens ordenados
     */
    public function getProductExtras(int $productId, int $restaurantId): array
    {
        return $this->itemRepository->findByProduct($productId, $restaurantId);
    }

    /**
     * Retorna todos os grupos com seus itens (para listagem)
     */
    public function getAllGroupsWithItems(int $restaurantId): array
    {
        return $this->groupRepository->findAllWithItems($restaurantId);
    }

    /**
     * Retorna todos os itens (para listagem e selects)
     */
    public function getAllItems(int $restaurantId): array
    {
        return $this->itemRepository->findAll($restaurantId);
    }
}
