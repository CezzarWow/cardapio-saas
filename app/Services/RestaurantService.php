<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

/**
 * RestaurantService - Lógica de Negócio de Restaurantes
 * 
 * Gerencia operações CRUD de restaurantes e criação de
 * categorias de sistema padrão.
 */
class RestaurantService
{
    /**
     * Categorias criadas automaticamente em novos restaurantes
     */
    private const SYSTEM_CATEGORIES = [
        ['name' => 'Destaques', 'type' => 'featured', 'order' => 1],
        ['name' => 'Combos', 'type' => 'combos', 'order' => 2],
    ];

    /**
     * Cria um novo restaurante com categorias de sistema
     */
    public function create(array $data, int $userId): int
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // Insere restaurante
            $stmt = $conn->prepare(
                "INSERT INTO restaurants (user_id, name, slug) VALUES (:uid, :name, :slug)"
            );
            $stmt->execute([
                'uid' => $userId,
                'name' => trim($data['name']),
                'slug' => trim($data['slug'])
            ]);
            $restaurantId = (int) $conn->lastInsertId();

            // Cria categorias de sistema
            $this->createSystemCategories($conn, $restaurantId);

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
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca restaurantes por Usuário
     */
    public function getByUser(int $userId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE user_id = :uid ORDER BY id DESC");
        $stmt->execute(['uid' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza dados do restaurante
     */
    public function update(int $id, array $data): void
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare(
            "UPDATE restaurants SET name = :name, slug = :slug WHERE id = :id"
        );
        $stmt->execute([
            'name' => trim($data['name']),
            'slug' => trim($data['slug']),
            'id' => $id
        ]);
    }

    /**
     * Remove restaurante
     */
    public function delete(int $id): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    /**
     * Alterna status ativo/inativo
     */
    public function toggleStatus(int $id): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "UPDATE restaurants SET is_active = NOT is_active WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * Cria categorias de sistema para um restaurante
     */
    private function createSystemCategories(PDO $conn, int $restaurantId): void
    {
        $stmt = $conn->prepare(
            "INSERT INTO categories (restaurant_id, name, category_type, sort_order, is_active) 
             VALUES (:rid, :name, :type, :sort_order, 1)"
        );

        foreach (self::SYSTEM_CATEGORIES as $cat) {
            $stmt->execute([
                'rid' => $restaurantId,
                'name' => $cat['name'],
                'type' => $cat['type'],
                'sort_order' => $cat['order']
            ]);
        }
    }
}
