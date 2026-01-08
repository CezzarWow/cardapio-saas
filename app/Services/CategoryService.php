<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

/**
 * CategoryService - Lógica de Negócio de Categorias
 */
class CategoryService
{
    /**
     * Lista todas as categorias de um restaurante
     */
    public function list(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :id ORDER BY name ASC");
        $stmt->execute(['id' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca categoria por ID
     */
    public function findById(int $id, int $restaurantId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Cria nova categoria
     */
    public function create(array $data, int $restaurantId): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO categories (restaurant_id, name) VALUES (:rid, :name)");
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => trim($data['name'])
        ]);
        return (int) $conn->lastInsertId();
    }

    /**
     * Atualiza categoria
     */
    public function update(int $id, array $data, int $restaurantId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("UPDATE categories SET name = :name WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute([
            'name' => trim($data['name']),
            'id' => $id,
            'rid' => $restaurantId
        ]);
    }

    /**
     * Deleta categoria (protegendo categorias de sistema)
     */
    public function delete(int $id, int $restaurantId): void
    {
        $conn = Database::connect();
        
        // Verifica se é categoria de sistema
        $stmt = $conn->prepare("SELECT category_type FROM categories WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($category && in_array($category['category_type'], ['featured', 'combos'])) {
            throw new Exception("Categorias de sistema não podem ser excluídas.");
        }

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
    }
}
