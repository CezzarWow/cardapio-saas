<?php
namespace App\Services\Stock;

use App\Core\Database;
use PDO;

/**
 * StockService - Gerenciamento de Estoque e Reposição
 * Focado EXCLUSIVAMENTE em quantidades e movimentações.
 * (CRUD de Produtos movido para ProductService)
 */
class StockService {

    /**
     * Lista produtos PARA VISUALIZAÇÃO DE ESTOQUE (Read-Only context)
     */
    public function getProducts(int $restaurantId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.restaurant_id = :rid ORDER BY p.name
        ");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista categorias (Read-Only context para filtros)
     */
    public function getCategories(int $restaurantId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajusta estoque (reposição/saída)
     */
    public function adjustStock(int $restaurantId, int $productId, int $amount): array {
        $conn = Database::connect();

        try {
            // Verifica se produto pertence à loja
            $stmt = $conn->prepare("SELECT id, stock, name FROM products WHERE id = :id AND restaurant_id = :rid");
            $stmt->execute(['id' => $productId, 'rid' => $restaurantId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new \Exception('Produto não encontrado');
            }

            // Guarda estoque antes do ajuste
            $stockBefore = intval($product['stock']);
            $stockAfter = $stockBefore + $amount;

            // Ajuste INCREMENTAL
            $stmtUpdate = $conn->prepare("UPDATE products SET stock = stock + :amount WHERE id = :id AND restaurant_id = :rid");
            $stmtUpdate->execute([
                'amount' => $amount,
                'id' => $productId,
                'rid' => $restaurantId
            ]);

            // Registra movimentação
            $movementType = $amount > 0 ? 'entrada' : 'saida';
            $movementQty = abs($amount);
            
            $stmtMov = $conn->prepare("INSERT INTO stock_movements 
                (restaurant_id, product_id, type, quantity, stock_before, stock_after, source) 
                VALUES (:rid, :pid, :type, :qty, :before, :after, 'reposicao')");
            $stmtMov->execute([
                'rid' => $restaurantId,
                'pid' => $productId,
                'type' => $movementType,
                'qty' => $movementQty,
                'before' => $stockBefore,
                'after' => $stockAfter
            ]);

            return [
                'success' => true,
                'new_stock' => $stockAfter,
                'product_name' => $product['name']
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Lista movimentações de estoque com filtros
     */
    public function getMovements(int $restaurantId, array $filters = []): array {
        $conn = Database::connect();
        
        $sql = "SELECT m.*, p.name as product_name, p.image as product_image, c.name as category_name
                FROM stock_movements m
                INNER JOIN products p ON m.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE m.restaurant_id = :rid";
        
        $params = ['rid' => $restaurantId];

        // Filtro por produto
        if (!empty($filters['product'])) {
            $sql .= " AND p.id = :pid";
            $params['pid'] = $filters['product'];
        }

        // Filtro por categoria
        if (!empty($filters['category'])) {
            $sql .= " AND c.name = :cat";
            $params['cat'] = $filters['category'];
        }

        // Filtro por Data (Início)
        if (!empty($filters['start_date'])) {
            $sql .= " AND m.created_at >= :start";
            $params['start'] = $filters['start_date'] . ' 00:00:00';
        }

        // Filtro por Data (Fim)
        if (!empty($filters['end_date'])) {
            $sql .= " AND m.created_at <= :end";
            $params['end'] = $filters['end_date'] . ' 23:59:59';
        }

        $sql .= " ORDER BY m.created_at DESC LIMIT 100";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcula estatísticas de movimentações
     */
    public function getMovementStats(array $movements): array {
        $entradas = 0;
        $saidas = 0;
        foreach ($movements as $m) {
            if ($m['type'] == 'entrada') $entradas++;
            else $saidas++;
        }
        return ['entradas' => $entradas, 'saidas' => $saidas];
    }
}
