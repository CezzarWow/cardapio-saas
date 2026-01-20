<?php

namespace App\Repositories;

use App\Core\Database;
use App\Core\Cache;
use PDO;

class ProductRepository
{
    /**
     * Lista produtos ativos com flag de adicionais (para PDV)
     * Inclui cálculo de preço efetivo considerando promoções ativas
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

        $today = date('Y-m-d');

        foreach ($products as &$p) {
            $p['has_extras'] = (bool) $p['has_extras'];
            
            // Calcular preço efetivo considerando promoção
            $isPromoValid = false;
            if (!empty($p['is_on_promotion']) && !empty($p['promotional_price'])) {
                // Promoção ativa: verificar validade
                if (empty($p['promo_expires_at'])) {
                    // Sem data limite = sempre válido
                    $isPromoValid = true;
                } else {
                    // Com data limite = verificar se não expirou
                    $isPromoValid = ($p['promo_expires_at'] >= $today);
                }
            }
            
            // Preço efetivo: promocional se válido, senão normal
            $p['is_promo_valid'] = $isPromoValid;
            $p['effective_price'] = $isPromoValid ? floatval($p['promotional_price']) : floatval($p['price']);
            $p['original_price'] = floatval($p['price']); // Sempre manter referência ao original
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

        // Invalida cache relevante
        try {
            $cache = new Cache();
            $cache->forget('products_' . $data['restaurant_id']);
            $cache->forget('combos_' . $data['restaurant_id']);
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
            // não bloquear execução em caso de falha no cache
        }
    }

    /**
     * Deleta produto
     */
    public function delete(int $id, int $restaurantId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('DELETE FROM products WHERE id = :id AND restaurant_id = :rid');
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);

        try {
            $cache = new Cache();
            $cache->forget('products_' . $restaurantId);
            $cache->forget('combos_' . $restaurantId);
            $cache->forget('product_additional_relations');
        } catch (\Exception $e) {
        }
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

        // Invalidação de cache
        try {
            $cache = new Cache();
            $cache->forget('product_additional_relations');
            // produtos cache pode refletir extras
            // Não temos restaurantId aqui; deixar apenas relations
        } catch (\Exception $e) {
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

    /**
     * Lista produtos que possuem configuração de promoção (ativa ou inativa)
     */
    public function findOnPromotion(int $restaurantId): array
    {
        $conn = Database::connect();
        $sql = '
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.restaurant_id = :rid 
              AND p.promotional_price IS NOT NULL 
              AND p.promotional_price > 0
            ORDER BY p.name
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Define promoção para um produto
     */
    public function setPromotion(int $id, int $restaurantId, array $data): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            UPDATE products SET 
                promotional_price = :promo_price,
                promo_expires_at = :expires,
                is_on_promotion = 1
            WHERE id = :id AND restaurant_id = :rid
        ');
        $stmt->execute([
            'promo_price' => $data['promotional_price'],
            'expires' => $data['promo_expires_at'] ?: null,
            'id' => $id,
            'rid' => $restaurantId
        ]);

        $this->invalidatePromoCache($restaurantId);
    }

    /**
     * Alterna status de promoção
     */
    public function togglePromotion(int $id, bool $active, int $restaurantId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('UPDATE products SET is_on_promotion = :active WHERE id = :id AND restaurant_id = :rid');
        $stmt->execute([
            'active' => $active ? 1 : 0,
            'id' => $id,
            'rid' => $restaurantId
        ]);

        $this->invalidatePromoCache($restaurantId);
    }

    /**
     * Remove promoção de um produto
     */
    public function removePromotion(int $id, int $restaurantId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            UPDATE products SET 
                promotional_price = NULL,
                promo_expires_at = NULL,
                is_on_promotion = 0
            WHERE id = :id AND restaurant_id = :rid
        ');
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);

        $this->invalidatePromoCache($restaurantId);
    }

    /**
     * Lista produtos disponíveis para promoção (que não possuem configuração de promoção)
     */
    public function findAvailableForPromotion(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            SELECT p.id, p.name, p.price, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.restaurant_id = :rid 
              AND p.is_active = 1
              AND (p.promotional_price IS NULL OR p.promotional_price = 0)
            ORDER BY c.name, p.name
        ');
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Invalida cache de promoções
     */
    private function invalidatePromoCache(int $restaurantId): void
    {
        try {
            // Usar SimpleCache diretamente para alinhar com CardapioQueryService
            require_once __DIR__ . '/../Core/SimpleCache.php';
            $cache = new \App\Core\SimpleCache(); 
            $cache->forget('products_' . $restaurantId);
            $cache->forget('cardapio_index_' . $restaurantId . '_v2');
        } catch (\Throwable $e) {
            // Logar erro de cache mas não quebrar aplicação
            file_put_contents('C:/xampp/htdocs/cardapio-saas/debug_log.txt', date('Y-m-d H:i:s') . " - CACHE ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}

