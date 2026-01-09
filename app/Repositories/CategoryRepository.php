<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class CategoryRepository
{
    /**
     * Lista todas as categorias de um restaurante
     */
    public function findAll(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Cria nova categoria
     */
    public function create(array $data): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            INSERT INTO categories (restaurant_id, name, category_type, sort_order, is_active) 
            VALUES (:rid, :name, :type, :sort, :active)
        ");
        
        $stmt->execute([
            'rid' => $data['restaurant_id'],
            'name' => $data['name'],
            'type' => $data['category_type'] ?? 'standard',
            'sort' => $data['sort_order'] ?? 0,
            'active' => $data['is_active'] ?? 1
        ]);
        
        return (int) $conn->lastInsertId();
    }
}
