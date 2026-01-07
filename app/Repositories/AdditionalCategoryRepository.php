<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Repository para vínculos entre Grupos de Adicionais e Categorias
 * Gerencia a tabela product_additional_relations (grupo ↔ produto via categoria)
 */
class AdditionalCategoryRepository
{
    /**
     * Retorna IDs das categorias que têm pelo menos um produto vinculado ao grupo
     */
    public function getLinkedCategories(int $groupId, int $restaurantId): array
    {
        $conn = Database::connect();
        
        $sql = "SELECT DISTINCT p.category_id 
                FROM product_additional_relations par
                JOIN products p ON par.product_id = p.id
                WHERE par.group_id = :gid AND p.restaurant_id = :rid";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['gid' => $groupId, 'rid' => $restaurantId]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Sincroniza vínculos: aplica grupo a produtos das categorias selecionadas,
     * remove de produtos de categorias desmarcadas
     */
    public function syncCategories(int $groupId, array $selectedCategoryIds, int $restaurantId): void
    {
        $conn = Database::connect();

        // 1. Busca TODAS as categorias da loja
        $stmtAllCats = $conn->prepare("SELECT id FROM categories WHERE restaurant_id = :rid");
        $stmtAllCats->execute(['rid' => $restaurantId]);
        $allCategoryIds = $stmtAllCats->fetchAll(PDO::FETCH_COLUMN);

        // 2. Prepara statements
        $stmtGetProds = $conn->prepare("SELECT id FROM products WHERE category_id = :cid AND restaurant_id = :rid");
        $stmtIns = $conn->prepare("INSERT IGNORE INTO product_additional_relations (product_id, group_id) VALUES (:pid, :gid)");
        $stmtDel = $conn->prepare("DELETE FROM product_additional_relations WHERE product_id = :pid AND group_id = :gid");

        // 3. Itera sobre todas as categorias
        foreach ($allCategoryIds as $cid) {
            // Busca produtos da categoria
            $stmtGetProds->execute(['cid' => $cid, 'rid' => $restaurantId]);
            $products = $stmtGetProds->fetchAll(PDO::FETCH_COLUMN);

            if (in_array($cid, $selectedCategoryIds)) {
                // MARCADA: vincular todos os produtos
                foreach ($products as $pid) {
                    $stmtIns->execute(['pid' => $pid, 'gid' => $groupId]);
                }
            } else {
                // DESMARCADA: desvincular todos os produtos
                foreach ($products as $pid) {
                    $stmtDel->execute(['pid' => $pid, 'gid' => $groupId]);
                }
            }
        }
    }

    /**
     * Busca todas as categorias de um restaurante (para exibição no modal)
     */
    public function findAllCategories(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT id, name FROM categories WHERE restaurant_id = :rid ORDER BY name ASC");
        $stmt->execute(['rid' => $restaurantId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
