<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ProductRepository
{
    /**
     * Lista produtos ativos com flag de adicionais (para PDV)
     */
    public function findActiveWithExtras(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            SELECT p.*, 
                   (SELECT 1 FROM product_additional_relations par WHERE par.product_id = p.id LIMIT 1) as has_extras
            FROM products p 
            WHERE p.restaurant_id = :rid AND p.is_active = 1
            ORDER BY p.name
        ');
        $stmt->execute(['rid' => $restaurantId]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cast has_extras to bool
        foreach ($products as &$p) {
            $p['has_extras'] = (bool) $p['has_extras'];
        }

        return $products;
    }

    /**
     * Lista produtos com nome da categoria
     */
    public function findAll(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.restaurant_id = :rid ORDER BY p.name
        ');
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca produto por ID e RestaurantID
     */
    public function find(int $id, int $restaurantId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM products WHERE id = :id AND restaurant_id = :rid');
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Cria novo produto
     */
    public function create(array $data): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            INSERT INTO products (restaurant_id, category_id, name, description, price, image, icon, icon_as_photo, item_number, stock) 
            VALUES (:rid, :cid, :name, :desc, :price, :img, :icon, :iap, :inum, :stock)
        ');

        $stmt->execute([
            'rid' => $data['restaurant_id'],
            'cid' => $data['category_id'],
            'name' => $data['name'],
            'desc' => $data['description'],
            'price' => $data['price'],
            'img' => $data['image'],
            'icon' => $data['icon'],
            'iap' => $data['icon_as_photo'],
            'inum' => $data['item_number'],
            'stock' => $data['stock']
        ]);

        return (int) $conn->lastInsertId();
    }

    /**
     * Atualiza produto existente
     */
    public function update(array $data): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            UPDATE products SET 
                name = :name, price = :price, category_id = :cid, description = :desc, 
                stock = :stock, image = :img, icon = :icon, icon_as_photo = :iap
            WHERE id = :id AND restaurant_id = :rid
        ');

        $stmt->execute([
            'name' => $data['name'],
            'price' => $data['price'],
            'cid' => $data['category_id'],
            'desc' => $data['description'],
            'stock' => $data['stock'],
            'img' => $data['image'],
            'icon' => $data['icon'],
            'iap' => $data['icon_as_photo'],
            'id' => $data['id'],
            'rid' => $data['restaurant_id']
        ]);
    }

    /**
     * Deleta produto
     */
    public function delete(int $id, int $restaurantId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('DELETE FROM products WHERE id = :id AND restaurant_id = :rid');
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
    }

    /**
     * Obtém o próximo item_number para o restaurante
     */
    public function getNextItemNumber(int $restaurantId): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT COALESCE(MAX(item_number), 0) + 1 AS next_num FROM products WHERE restaurant_id = :rid');
        $stmt->execute(['rid' => $restaurantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['next_num'];
    }

    /**
     * Sincroniza grupos de adicionais vinculados ao produto
     */
    public function syncAdditionalGroups(int $productId, array $groupIds): void
    {
        $conn = Database::connect();

        // Remove vínculos anteriores
        $stmtDel = $conn->prepare('DELETE FROM product_additional_relations WHERE product_id = :pid');
        $stmtDel->execute(['pid' => $productId]);

        // Insere novos
        if (!empty($groupIds)) {
            $stmtIns = $conn->prepare('INSERT INTO product_additional_relations (product_id, group_id) VALUES (:pid, :gid)');
            foreach ($groupIds as $gid) {
                $stmtIns->execute(['pid' => $productId, 'gid' => $gid]);
            }
        }
    }

    /**
     * Retorna IDs dos grupos vinculados
     */
    public function getLinkedGroups(int $productId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT group_id FROM product_additional_relations WHERE product_id = :pid');
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
