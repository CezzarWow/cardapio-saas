<?php

namespace App\Services;

use App\Core\Database;
use App\Repositories\CategoryRepository;
use App\Repositories\RestaurantRepository;
use Exception;

/**
 * RestaurantService - Lógica de Negócio de Restaurantes
 *
 * Gerencia operações CRUD de restaurantes e criação de
 * categorias de sistema padrão.
 */
class RestaurantService
{
    private RestaurantRepository $restaurantRepo;
    private CategoryRepository $categoryRepo;

    /**
     * Categorias criadas automaticamente em novos restaurantes
     */
    private const SYSTEM_CATEGORIES = [
        ['name' => 'Destaques', 'type' => 'featured', 'order' => 1],
        ['name' => 'Combos', 'type' => 'combos', 'order' => 2],
    ];

    public function __construct(RestaurantRepository $restaurantRepo, CategoryRepository $categoryRepo)
    {
        $this->restaurantRepo = $restaurantRepo;
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Cria um novo restaurante com categorias de sistema
     */
    public function create(array $data, int $userId): int
    {
        // Transaction control logic requires Database connection usually.
        // Or we assume Repo methods are atomic, but here we have a transaction spanning multiple repos.
        // We need to keep Transaction logic here, likely.
        // But Repositories use `Database::connect()` which is singleton PDO.
        // So `beginTransaction` on `Database::connect()` works across repos.

        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // Insere restaurante
            $restaurantId = $this->restaurantRepo->create(array_merge($data, ['user_id' => $userId]));

            // Cria categorias de sistema
            foreach (self::SYSTEM_CATEGORIES as $cat) {
                $this->categoryRepo->create([
                    'restaurant_id' => $restaurantId,
                    'name' => $cat['name'],
                    'category_type' => $cat['type'],
                    'sort_order' => $cat['order'],
                    'is_active' => 1
                ]);
            }

            $conn->commit();
            return $restaurantId;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Busca restaurante por ID
     */
    public function findById(int $id): ?array
    {
        return $this->restaurantRepo->find($id);
    }

    /**
     * Busca restaurantes por Usuário
     */
    public function getByUser(int $userId): array
    {
        return $this->restaurantRepo->findByUser($userId);
    }

    /**
     * Atualiza dados do restaurante
     */
    public function update(int $id, array $data): void
    {
        $this->restaurantRepo->update($id, $data);
    }

    /**
     * Remove restaurante
     */
    public function delete(int $id): void
    {
        $this->restaurantRepo->delete($id);
    }

    /**
     * Alterna status ativo/inativo
     */
    public function toggleStatus(int $id): void
    {
        $this->restaurantRepo->toggleStatus($id);
    }
}
