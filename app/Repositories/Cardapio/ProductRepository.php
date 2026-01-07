<?php

namespace App\Repositories\Cardapio;

use App\Core\Database;
use PDO;

/**
 * Repository para Produtos do Cardápio (leituras e atualizações de display)
 */
class ProductRepository
{
    /**
     * Busca todos os produtos com nome da categoria
     */
    public function findAllWithCategory(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.restaurant_id = :rid 
            ORDER BY c.sort_order ASC, c.name ASC, p.display_order ASC, p.name ASC
        ");
        $stmt->execute(['rid' => $restaurantId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca produtos para formulário de combo (lista simples)
     */
    public function findAllSimple(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza os produtos em destaque (is_featured)
     */
    public function syncFeatured(int $restaurantId, array $featuredIds): void
    {
        $conn = Database::connect();
        
        // Primeiro, remove todos os destaques do restaurante
        $conn->prepare("UPDATE products SET is_featured = 0 WHERE restaurant_id = :rid")
             ->execute(['rid' => $restaurantId]);
        
        // Depois, marca os selecionados
        if (!empty($featuredIds)) {
            $stmt = $conn->prepare("UPDATE products SET is_featured = 1 WHERE id = :pid AND restaurant_id = :rid");
            foreach (array_keys($featuredIds) as $productId) {
                $stmt->execute(['pid' => $productId, 'rid' => $restaurantId]);
            }
        }
    }

    /**
     * Atualiza ordem de exibição dos produtos
     */
    public function syncDisplayOrder(int $restaurantId, array $orderData): void
    {
        if (empty($orderData)) return;
        
        $conn = Database::connect();
        
        $stmt = $conn->prepare("UPDATE products SET display_order = :order WHERE id = :pid AND restaurant_id = :rid");
        foreach ($orderData as $prodId => $order) {
            $stmt->execute([
                'order' => intval($order),
                'pid' => intval($prodId),
                'rid' => $restaurantId
            ]);
        }
    }

    /**
     * Agrupa produtos por categoria (helper para views)
     */
    public function groupByCategory(array $products): array
    {
        $grouped = [];
        foreach ($products as $product) {
            $catName = $product['category_name'] ?? 'Sem categoria';
            if (!isset($grouped[$catName])) {
                $grouped[$catName] = [];
            }
            $grouped[$catName][] = $product;
        }
        return $grouped;
    }
}
