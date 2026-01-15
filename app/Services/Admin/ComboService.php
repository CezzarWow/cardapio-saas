<?php

/**
 * ============================================
 * COMBO SERVICE
 * Gerencia operações de combos (CRUD)
 * ============================================
 */

namespace App\Services\Admin;

use App\Repositories\ComboRepository;

class ComboService
{
    private ComboRepository $repo;

    public function __construct(ComboRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Salva novo combo
     */
    public function store(array $data, int $restaurantId): int
    {
        $price = str_replace(',', '.', $data['price'] ?? '0');
        $price = preg_replace('/[^\d.]/', '', $price);

        $comboId = $this->repo->create([
            'restaurant_id' => $restaurantId,
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'price' => floatval($price),
            'display_order' => intval($data['display_order'] ?? 0),
            'is_active' => isset($data['is_active']) ? 1 : 0
        ]);

        $this->repo->saveItems($comboId, $data['products'] ?? [], $data['allow_additionals'] ?? []);

        return $comboId;
    }

    /**
     * Busca combo para edição
     */
    public function getForEdit(int $comboId, int $restaurantId): ?array
    {
        $combo = $this->repo->find($comboId, $restaurantId);

        if (!$combo) {
            return null;
        }

        $rawItems = $this->repo->findItemsWithDetails($comboId);

        $comboProducts = [];
        $comboItemsSettings = [];
        $comboItemsDetails = [];

        foreach ($rawItems as $item) {
            $pid = $item['product_id'];
            $comboProducts[] = $pid;
            $comboItemsSettings[$pid] = [
                'allow_additionals' => $item['allow_additionals']
            ];

            if (!isset($comboItemsDetails[$pid])) {
                $comboItemsDetails[$pid] = [
                    'id' => $pid,
                    'name' => $item['product_name'],
                    'price' => $item['product_price'],
                    'qty' => 0,
                    'allow_additionals' => $item['allow_additionals']
                ];
            }
            $comboItemsDetails[$pid]['qty']++;
        }

        return [
            'combo' => $combo,
            'comboProducts' => $comboProducts,
            'comboItemsSettings' => $comboItemsSettings,
            'items' => array_values($comboItemsDetails)
        ];
    }

    /**
     * Atualiza combo
     */
    public function update(int $comboId, array $data, int $restaurantId): void
    {
        $price = str_replace(',', '.', $data['price'] ?? '0');
        $price = preg_replace('/[^\d.]/', '', $price);

        $this->repo->update($comboId, [
            'restaurant_id' => $restaurantId,
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'price' => floatval($price),
            'display_order' => intval($data['display_order'] ?? 0),
            'is_active' => isset($data['is_active']) ? 1 : 0
        ]);

        $this->repo->saveItems($comboId, $data['products'] ?? [], $data['allow_additionals'] ?? []);
    }

    /**
     * Deleta combo
     */
    public function delete(int $comboId, int $restaurantId): void
    {
        $this->repo->delete($comboId, $restaurantId);
    }

    /**
     * Alterna status do combo
     */
    public function toggleStatus(int $comboId, bool $active, int $restaurantId): bool
    {
        $this->repo->toggleStatus($comboId, $active ? 1 : 0, $restaurantId);
        return true;
    }
}
