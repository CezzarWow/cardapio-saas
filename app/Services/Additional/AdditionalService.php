<?php
namespace App\Services\Additional;

use App\Repositories\AdditionalItemRepository;
use App\Repositories\AdditionalGroupRepository;
use App\Repositories\AdditionalPivotRepository;
use App\Repositories\AdditionalCategoryRepository;
use App\Core\Database;
use Exception;

/**
 * AdditionalService - Lógica de Negócio de Adicionais
 * Centraliza todas as operações de Grupos, Itens e Vínculos
 */
class AdditionalService
{
    private AdditionalItemRepository $itemRepo;
    private AdditionalGroupRepository $groupRepo;
    private AdditionalPivotRepository $pivotRepo;
    private AdditionalCategoryRepository $categoryRepo;

    public function __construct()
    {
        $this->itemRepo = new AdditionalItemRepository();
        $this->groupRepo = new AdditionalGroupRepository();
        $this->pivotRepo = new AdditionalPivotRepository();
        $this->categoryRepo = new AdditionalCategoryRepository();
    }

    // ====================================================
    // QUERIES (LEITURA)
    // ====================================================

    public function getAllGroupsWithItems(int $rid): array
    {
        return $this->groupRepo->findAllWithItems($rid);
    }

    public function getAllItems(int $rid): array
    {
        return $this->itemRepo->findAll($rid);
    }

    public function getItemData(int $id, int $rid): ?array
    {
        $item = $this->itemRepo->findById($id, $rid);
        return $item ? ['item' => $item, 'groups' => $this->itemRepo->getGroupsForItem($id)] : null;
    }

    public function getProductExtras(int $productId, int $rid): array
    {
        return $this->itemRepo->findByProduct($productId, $rid);
    }

    public function getLinkedCategories(int $groupId, int $rid): array
    {
        return $this->categoryRepo->getLinkedCategories($groupId, $rid);
    }

    // ====================================================
    // GRUPOS
    // ====================================================

    public function createGroup(int $rid, array $data): int
    {
        return $this->groupRepo->save($rid, $data);
    }

    public function deleteGroup(int $id, int $rid): void
    {
        $this->groupRepo->delete($id, $rid);
    }

    // ====================================================
    // ITENS
    // ====================================================

    public function createItem(int $rid, array $data): int
    {
        $conn = Database::connect();
        try {
            $conn->beginTransaction();
            
            $itemId = $this->itemRepo->save($rid, $data['name'], $this->parsePrice($data['price'] ?? '0'));
            
            if (!empty($data['group_ids'])) {
                $this->linkMultipleGroups($itemId, $data['group_ids'], $rid);
            }
            
            $conn->commit();
            return $itemId;
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public function updateItem(int $rid, array $data): void
    {
        $conn = Database::connect();
        try {
            $conn->beginTransaction();
            
            $this->itemRepo->update($data['id'], $rid, $data['name'], $this->parsePrice($data['price'] ?? '0'));
            
            // Sincroniza grupos (limpa e insere)
            $this->pivotRepo->unlinkAllGroups($data['id']);
            if (!empty($data['group_ids'])) {
                $this->linkMultipleGroups($data['id'], $data['group_ids'], $rid);
            }
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public function deleteItem(int $id, int $rid): void
    {
        $this->itemRepo->delete($id, $rid);
    }

    // ====================================================
    // VÍNCULOS
    // ====================================================

    public function linkItem(int $groupId, int $itemId, int $rid): void
    {
        if ($this->groupRepo->findById($groupId, $rid) && $this->itemRepo->findById($itemId, $rid)) {
            $this->pivotRepo->link($groupId, $itemId);
        }
    }

    public function unlinkItem(int $groupId, int $itemId, int $rid): void
    {
        if ($this->groupRepo->findById($groupId, $rid) && $this->itemRepo->findById($itemId, $rid)) {
            $this->pivotRepo->unlink($groupId, $itemId);
        }
    }

    public function linkMultipleItems(int $groupId, array $itemIds, int $rid): void
    {
        if (!$this->groupRepo->findById($groupId, $rid)) return;
        
        foreach ($itemIds as $itemId) {
            if ($this->itemRepo->findById($itemId, $rid)) {
                $this->pivotRepo->link($groupId, $itemId);
            }
        }
    }

    public function updateCategoryLinks(int $groupId, array $categoryIds, int $rid): void
    {
        if (!$this->groupRepo->findById($groupId, $rid)) return;
        
        $conn = Database::connect();
        try {
            $conn->beginTransaction();
            
            $this->categoryRepo->unlinkAll($groupId);
            foreach ($categoryIds as $catId) {
                // Verifica validação se necessário
                $this->categoryRepo->link($groupId, $catId);
            }
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    // ====================================================
    // HELPERS
    // ====================================================

    private function parsePrice(string $priceRaw): float
    {
        $priceRaw = str_replace('.', '', $priceRaw);
        $priceRaw = str_replace(',', '.', $priceRaw);
        return floatval($priceRaw);
    }

    private function linkMultipleGroups(int $itemId, array $groupIds, int $rid): void
    {
        foreach ($groupIds as $gid) {
            if ($gid > 0 && $this->groupRepo->findById($gid, $rid)) {
                $this->pivotRepo->link($gid, $itemId);
            }
        }
    }
}
