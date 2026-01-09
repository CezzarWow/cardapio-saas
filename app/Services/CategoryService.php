<?php
namespace App\Services;

use App\Repositories\CategoryRepository;
use Exception;

/**
 * CategoryService - Lógica de Negócio de Categorias
 */
class CategoryService
{
    private CategoryRepository $repo;

    public function __construct(CategoryRepository $repo) {
        $this->repo = $repo;
    }

    /**
     * Lista todas as categorias de um restaurante
     */
    public function list(int $restaurantId): array
    {
        return $this->repo->findAll($restaurantId);
    }

    /**
     * Busca categoria por ID
     */
    public function findById(int $id, int $restaurantId): ?array
    {
        return $this->repo->find($id, $restaurantId);
    }

    /**
     * Cria nova categoria
     */
    public function create(array $data, int $restaurantId): int
    {
        return $this->repo->create(array_merge($data, ['restaurant_id' => $restaurantId]));
    }

    /**
     * Atualiza categoria
     */
    public function update(int $id, array $data, int $restaurantId): void
    {
        $this->repo->update($id, $data);
    }

    /**
     * Deleta categoria (protegendo categorias de sistema)
     */
    public function delete(int $id, int $restaurantId): void
    {
        // Verifica se é categoria de sistema
        $category = $this->repo->find($id, $restaurantId);

        if ($category && in_array($category['category_type'], ['featured', 'combos'])) {
            throw new Exception("Categorias de sistema não podem ser excluídas.");
        }

        $this->repo->delete($id, $restaurantId);
    }
}
