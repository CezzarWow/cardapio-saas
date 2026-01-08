<?php
namespace App\Services\Product;

use App\Core\Database;
use PDO;

require_once __DIR__ . '/../../Helpers/ImageConverter.php';

/**
 * ProductService - Gerenciamento do Catálogo de Produtos
 * Responsável por CRUD, Imagens e Vinculação de Adicionais
 */
class ProductService {

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
     * Cria novo produto
     */
    public function create(int $restaurantId, array $data, ?string $imageName = null): int {
        $conn = Database::connect();
        
        // Próximo item_number
        $stmtMax = $conn->prepare("SELECT COALESCE(MAX(item_number), 0) + 1 AS next_num FROM products WHERE restaurant_id = :rid");
        $stmtMax->execute(['rid' => $restaurantId]);
        $nextNumber = $stmtMax->fetch(PDO::FETCH_ASSOC)['next_num'];
        
        $stockValue = isset($data['stock']) ? $data['stock'] : 0;

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
            'stock' => $stockValue
        ]);
        
        $productId = $conn->lastInsertId();
        
        if (isset($data['additional_groups'])) {
            $this->syncAdditionalGroups($productId, $data['additional_groups']);
        }
        
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
        
        if (isset($data['additional_groups'])) {
            $this->syncAdditionalGroups($id, $data['additional_groups']);
        }
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
     * Sincroniza grupos de adicionais
     */
    private function syncAdditionalGroups(int $productId, array $groupIds): void {
        $conn = Database::connect();
        
        $conn->prepare("DELETE FROM product_additional_relations WHERE product_id = :pid")
             ->execute(['pid' => $productId]);
        
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
        // Helper global ImageConverter
        return \ImageConverter::uploadAndConvert($file, $uploadDir, 85);
    }
    
    /**
     * Lista categorias (Helper para Forms de Produto)
     */
    public function getCategories(int $restaurantId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista grupos de adicionais (Helper para Forms de Produto)
     */
    public function getAdditionalGroups(int $restaurantId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM additional_groups WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista grupos vinculados a um produto (Helper para Edição)
     */
    public function getLinkedGroups(int $productId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT group_id FROM product_additional_relations WHERE product_id = :pid");
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
