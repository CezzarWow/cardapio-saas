<?php

namespace App\Services\Additional;

use App\Core\Database;
use App\Repositories\AdditionalItemRepository;
use App\Repositories\AdditionalPivotRepository;
use App\Repositories\AdditionalGroupRepository;
use Exception;

/**
 * Service para atualizar Item de Adicional + Sincronizar Vínculos
 * Remove vínculos antigos e insere os novos (sync)
 */
class UpdateItemService
{
    private AdditionalItemRepository $itemRepository;
    private AdditionalPivotRepository $pivotRepository;
    private AdditionalGroupRepository $groupRepository;

    public function __construct()
    {
        $this->itemRepository = new AdditionalItemRepository();
        $this->pivotRepository = new AdditionalPivotRepository();
        $this->groupRepository = new AdditionalGroupRepository();
    }

    /**
     * Atualiza um item e sincroniza seus vínculos com grupos
     * 
     * @param int $restaurantId ID do restaurante
     * @param array $data ['id' => int, 'name' => string, 'price' => float, 'group_ids' => array]
     * @throws Exception Se dados inválidos ou item não encontrado
     */
    public function execute(int $restaurantId, array $data): void
    {
        $id = intval($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $price = $this->parsePrice($data['price'] ?? '0');
        $groupIds = $data['group_ids'] ?? [];

        // Validação básica
        if ($id <= 0) {
            throw new Exception('ID do item é obrigatório');
        }
        if (empty($name)) {
            throw new Exception('Nome do item é obrigatório');
        }

        // Verifica se item existe e pertence ao restaurante
        $existingItem = $this->itemRepository->findById($id, $restaurantId);
        if (!$existingItem) {
            throw new Exception('Item não encontrado ou não pertence a este restaurante');
        }

        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Atualizar dados do item
            $this->itemRepository->update($id, $restaurantId, $name, $price);

            // 2. Sincronizar grupos (remove todos e insere os novos)
            $validGroupIds = $this->filterValidGroups($groupIds, $restaurantId);
            $this->pivotRepository->syncGroupsForItem($id, $validGroupIds);

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Converte preço em formato BR (1.200,50) para float (1200.50)
     */
    private function parsePrice(string $priceRaw): float
    {
        $priceRaw = str_replace('.', '', $priceRaw);
        $priceRaw = str_replace(',', '.', $priceRaw);
        return floatval($priceRaw);
    }

    /**
     * Filtra apenas grupos que pertencem ao restaurante
     */
    private function filterValidGroups(array $groupIds, int $restaurantId): array
    {
        $valid = [];
        foreach ($groupIds as $gid) {
            $gid = intval($gid);
            if ($gid > 0 && $this->groupRepository->findById($gid, $restaurantId)) {
                $valid[] = $gid;
            }
        }
        return $valid;
    }
}
