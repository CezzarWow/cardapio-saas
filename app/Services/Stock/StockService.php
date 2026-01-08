<?php
namespace App\Services\Stock;

use App\Core\Database;
use PDO;

require_once __DIR__ . '/../../Helpers/ImageConverter.php';

/**
 * StockService - Lógica de Negócio de Produtos/Estoque
 */
class StockService {

    /**
     * Lista produtos com categoria
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
     * Busca produto por ID
     */
    public function getProduct(int $id, int $restaurantId): ?array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Lista categorias
     */
    public function getCategories(int $restaurantId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista grupos de adicionais
     */
    public function getAdditionalGroups(int $restaurantId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM additional_groups WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista grupos vinculados a um produto
     */
    public function getLinkedGroups(int $productId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT group_id FROM product_additional_relations WHERE product_id = :pid");
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Cria novo produto
     */
    public function create(int $restaurantId, array $data, ?string $imageName = null): int {
        $conn = Database::connect();
        
        // Próximo item_number
        $stmtMax = $conn->prepare("SELECT COALESCE(MAX(item_number), 0) + 1 AS next_num FROM products WHERE restaurant_id = :rid");
        $stmtMax->execute(['rid' => $restaurantId]);
        $nextNumber = $stmtMax->fetch(PDO::FETCH_ASSOC)['next_num'];
        
        $stmt = $conn->prepare("
            INSERT INTO products (restaurant_id, category_id, name, description, price, image, icon, icon_as_photo, item_number, stock) 
            VALUES (:rid, :cid, :name, :desc, :price, :img, :icon, :iap, :inum, :stock)
        ");
        $stmt->execute([
            'rid' => $restaurantId,
            'cid' => $data['category_id'],
            'name' => $data['name'],
            'desc' => $data['description'],
            'price' => $data['price'],
            'img' => $imageName,
            'icon' => $data['icon'],
            'iap' => $data['icon_as_photo'],
            'inum' => $nextNumber,
            'stock' => $data['stock']
        ]);
        
        $productId = $conn->lastInsertId();
        $this->syncAdditionalGroups($productId, $data['additional_groups']);
        
        return $productId;
    }

    /**
     * Atualiza produto existente
     */
    public function update(int $restaurantId, array $data, ?string $newImageName = null): void {
        $conn = Database::connect();
        $id = $data['id'];
        
        // Verifica se pertence à loja
        $product = $this->getProduct($id, $restaurantId);
        if (!$product) {
            throw new \Exception('Produto não encontrado');
        }
        
        $imageName = $newImageName ?? $product['image'];
        
        $stmt = $conn->prepare("
            UPDATE products SET 
                name = :name, price = :price, category_id = :cid, description = :desc, 
                stock = :stock, image = :img, icon = :icon, icon_as_photo = :iap
            WHERE id = :id AND restaurant_id = :rid
        ");
        $stmt->execute([
            'name' => $data['name'],
            'price' => $data['price'],
            'cid' => $data['category_id'],
            'desc' => $data['description'],
            'stock' => $data['stock'],
            'img' => $imageName,
            'icon' => $data['icon'],
            'iap' => $data['icon_as_photo'],
            'id' => $id,
            'rid' => $restaurantId
        ]);
        
        $this->syncAdditionalGroups($id, $data['additional_groups']);
    }

    /**
     * Deleta produto
     */
    public function delete(int $id, int $restaurantId): void {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM products WHERE id = :id AND restaurant_id = :rid")
             ->execute(['id' => $id, 'rid' => $restaurantId]);
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
     * Sincroniza grupos de adicionais
     */
    private function syncAdditionalGroups(int $productId, array $groupIds): void {
        $conn = Database::connect();
        
        // Limpa anteriores
        $conn->prepare("DELETE FROM product_additional_relations WHERE product_id = :pid")
             ->execute(['pid' => $productId]);
        
        // Insere novos
        if (!empty($groupIds)) {
            $stmt = $conn->prepare("INSERT INTO product_additional_relations (product_id, group_id) VALUES (:pid, :gid)");
            foreach ($groupIds as $gid) {
                $stmt->execute(['pid' => $productId, 'gid' => $gid]);
            }
        }
    }

    /**
     * Processa upload de imagem
     */
    public function handleImageUpload(?array $file): ?string {
        if (empty($file['name'])) {
            return null;
        }
        
        $uploadDir = __DIR__ . '/../../../public/uploads/';
        return \ImageConverter::uploadAndConvert($file, $uploadDir, 85);
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
}
