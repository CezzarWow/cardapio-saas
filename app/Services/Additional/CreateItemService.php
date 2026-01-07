<?php

namespace App\Services\Additional;

use App\Core\Database;
use App\Repositories\AdditionalItemRepository;
use App\Repositories\AdditionalPivotRepository;
use App\Repositories\AdditionalGroupRepository;
use Exception;

/**
 * Service para criar Item de Adicional + Vínculos com Grupos
 * Orquestra transação, valida segurança (grupo pertence ao restaurante)
 */
class CreateItemService
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
     * Cria um item e vincula aos grupos selecionados
     * 
     * @param int $restaurantId ID do restaurante
     * @param array $data ['name' => string, 'price' => float, 'group_ids' => array]
     * @return int ID do item criado
     * @throws Exception Se nome vazio ou erro de banco
     */
    public function execute(int $restaurantId, array $data): int
    {
        $name = trim($data['name'] ?? '');
        $price = $this->parsePrice($data['price'] ?? '0');
        $groupIds = $data['group_ids'] ?? [];

        // Validação básica
        if (empty($name)) {
            throw new Exception('Nome do item é obrigatório');
        }

        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Criar o item
            $itemId = $this->itemRepository->save($restaurantId, $name, $price);

            // 2. Vincular aos grupos (com validação de segurança)
            if (!empty($groupIds) && is_array($groupIds)) {
                $validGroupIds = $this->filterValidGroups($groupIds, $restaurantId);
                
                foreach ($validGroupIds as $gid) {
                    $this->pivotRepository->link($gid, $itemId);
                }
            }

            $conn->commit();
            return $itemId;

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
        $priceRaw = str_replace('.', '', $priceRaw); // Remove ponto de milhar
        $priceRaw = str_replace(',', '.', $priceRaw); // Troca vírgula por ponto
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
