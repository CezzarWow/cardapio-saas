<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * CARDÁPIO PÚBLICO - Acesso sem login
 * Rota: /cardapio/{slug} ou /c/{id}
 * ═══════════════════════════════════════════════════════════════════════════
 */

namespace App\Controllers;

use App\Core\Database;
use PDO;

class CardapioPublicoController {

    /**
     * Exibe o cardápio público de um restaurante
     * @param int $restaurantId ID do restaurante
     */
    public function show($restaurantId) {
        $conn = Database::connect();
        
        // Buscar dados do restaurante
        $stmtRestaurant = $conn->prepare("SELECT * FROM restaurants WHERE id = :rid");
        $stmtRestaurant->execute(['rid' => $restaurantId]);
        $restaurant = $stmtRestaurant->fetch(PDO::FETCH_ASSOC);
        
        if (!$restaurant) {
            echo "Restaurante não encontrado ou inativo.";
            exit;
        }
        
        // Buscar categorias com produtos
        $stmtCategories = $conn->prepare("
            SELECT DISTINCT c.* 
            FROM categories c
            INNER JOIN products p ON p.category_id = c.id
            WHERE c.restaurant_id = :rid
            ORDER BY c.name ASC
        ");
        $stmtCategories->execute(['rid' => $restaurantId]);
        $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar produtos
        $stmtProducts = $conn->prepare("
            SELECT 
                p.id,
                p.name,
                p.description,
                p.price,
                p.image,
                p.stock,
                c.id as category_id,
                c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.restaurant_id = :rid
            ORDER BY c.name ASC, p.name ASC
        ");
        $stmtProducts->execute(['rid' => $restaurantId]);
        $allProducts = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
        
        // Organizar por categoria
        $productsByCategory = [];
        foreach ($allProducts as $product) {
            $catName = $product['category_name'] ?? 'Sem Categoria';
            if (!isset($productsByCategory[$catName])) {
                $productsByCategory[$catName] = [];
            }
            $productsByCategory[$catName][] = $product;
        }
        
        // Buscar grupos de adicionais
        $stmtAdditionalGroups = $conn->prepare("
            SELECT * FROM additional_groups 
            WHERE restaurant_id = :rid 
            ORDER BY name ASC
        ");
        $stmtAdditionalGroups->execute(['rid' => $restaurantId]);
        $additionalGroups = $stmtAdditionalGroups->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar itens via pivot
        $additionalItems = [];
        foreach ($additionalGroups as $group) {
            $stmtItems = $conn->prepare("
                SELECT ai.* FROM additional_items ai
                INNER JOIN additional_group_items agi ON ai.id = agi.item_id
                WHERE agi.group_id = :gid 
                ORDER BY ai.name ASC
            ");
            $stmtItems->execute(['gid' => $group['id']]);
            $additionalItems[$group['id']] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Renderizar view pública
        require __DIR__ . '/../../views/cardapio_publico.php';
    }
}
