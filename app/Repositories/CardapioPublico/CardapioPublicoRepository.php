<?php

namespace App\Repositories\CardapioPublico;

use App\Core\Database;
use App\Core\Cache;
use PDO;

/**
 * Repository para o Cardápio Público (Read-Only)
 *
 * Busca dados para renderização do cardápio público.
 */
class CardapioPublicoRepository
{
    /** Configuração padrão quando não existe registro */
    private const DEFAULT_CONFIG = [
        'is_open' => 1,
        'closed_message' => 'Estamos fechados no momento',
        'delivery_enabled' => 1,
        'pickup_enabled' => 1,
        'dine_in_enabled' => 1,
        'delivery_fee' => 5.00,
        'min_order_value' => 20.00,
        'accept_cash' => 1,
        'accept_credit' => 1,
        'accept_debit' => 1,
        'accept_pix' => 1,
        'whatsapp_number' => '',
        'whatsapp_message' => '',
    ];

    // =========================================================================
    // RESTAURANTE
    // =========================================================================

    public function findRestaurantById(int $id): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM restaurants WHERE id = :rid');
        $stmt->execute(['rid' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findRestaurantBySlug(string $slug): ?int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT id FROM restaurants WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (int) $result : null;
    }

    // =========================================================================
    // CATEGORIAS E PRODUTOS
    // =========================================================================

    public function getCategories(int $restaurantId): array
    {
        $cache = new Cache();
        $key = 'categories_' . $restaurantId;
        $cached = $cache->get($key);
        if ($cached !== null) return $cached;

        $conn = Database::connect();
        $stmt = $conn->prepare('
            SELECT * FROM categories 
            WHERE restaurant_id = :rid AND is_active = 1
            ORDER BY COALESCE(sort_order, 999) ASC, name ASC
        ');
        $stmt->execute(['rid' => $restaurantId]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cache->put($key, $res, 3600);
        return $res;
    }

    public function getProducts(int $restaurantId): array
    {
        $cache = new Cache();
        $key = 'products_' . $restaurantId;
        $cached = $cache->get($key);
        if ($cached !== null) return $cached;

        $conn = Database::connect();
        $stmt = $conn->prepare('
            SELECT 
                p.id, p.name, p.description, p.price, p.image, p.stock,
                p.is_featured, p.icon, p.icon_as_photo,
                c.id as category_id, c.name as category_name,
                c.category_type, c.sort_order as category_order
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.restaurant_id = :rid
            ORDER BY COALESCE(c.sort_order, 999) ASC, c.name ASC, p.display_order ASC, p.name ASC
        ');
        $stmt->execute(['rid' => $restaurantId]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cache->put($key, $res, 300);
        return $res;
    }

    public function getProductAdditionalRelations(): array
    {
        $cache = new Cache();
        $key = 'product_additional_relations';
        $cached = $cache->get($key);
        if ($cached !== null) return $cached;

        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT product_id, group_id FROM product_additional_relations');
        $stmt->execute();
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $relations = [];
        foreach ($raw as $rel) {
            $relations[$rel['product_id']][] = $rel['group_id'];
        }
        $cache->put($key, $relations, 3600);
        return $relations;
    }

    // =========================================================================
    // COMBOS
    // =========================================================================

    public function getCombosWithItems(int $restaurantId): array
    {
        $cache = new Cache();
        $key = 'combos_' . $restaurantId;
        $cached = $cache->get($key);
        if ($cached !== null) return $cached;

        $conn = Database::connect();

        // Buscar combos
        $stmt = $conn->prepare('
            SELECT * FROM combos 
            WHERE restaurant_id = :rid AND is_active = 1
            ORDER BY display_order ASC, name ASC
        ');
        $stmt->execute(['rid' => $restaurantId]);
        $combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($combos)) {
            return [];
        }

        // Buscar itens dos combos
        $comboIds = array_column($combos, 'id');
        $inQuery = implode(',', array_fill(0, count($comboIds), '?'));

        $stmtItems = $conn->prepare("
            SELECT ci.combo_id, ci.allow_additionals,
                   p.id as product_id, p.name as product_name, p.image as product_image
            FROM combo_items ci
            JOIN products p ON p.id = ci.product_id
            WHERE ci.combo_id IN ($inQuery)
            ORDER BY p.name ASC
        ");
        $stmtItems->execute($comboIds);
        $comboItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar itens por combo
        $itemsByCombo = [];
        foreach ($comboItems as $item) {
            $itemsByCombo[$item['combo_id']][] = $item;
        }

        // Injetar itens nos combos
        foreach ($combos as &$combo) {
            $combo['items'] = $itemsByCombo[$combo['id']] ?? [];
            $combo['products_list'] = implode(', ', array_column($combo['items'], 'product_name'));
        }

        $cache->put($key, $combos, 600);
        return $combos;
    }

    // =========================================================================
    // ADICIONAIS
    // =========================================================================

    public function getAdditionalsWithItems(int $restaurantId): array
    {
        $cache = new Cache();
        $key = 'additionals_' . $restaurantId;
        $cached = $cache->get($key);
        if ($cached !== null) return $cached;

        $conn = Database::connect();

        // Buscar grupos
        $stmt = $conn->prepare('
            SELECT * FROM additional_groups 
            WHERE restaurant_id = :rid 
            ORDER BY name ASC
        ');
        $stmt->execute(['rid' => $restaurantId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($groups)) {
            return ['groups' => [], 'items' => []];
        }

        // Buscar itens
        $groupIds = array_column($groups, 'id');
        $inQuery = implode(',', array_fill(0, count($groupIds), '?'));

        $stmtItems = $conn->prepare("
            SELECT ai.*, agi.group_id 
            FROM additional_items ai
            INNER JOIN additional_group_items agi ON ai.id = agi.item_id
            WHERE agi.group_id IN ($inQuery)
            ORDER BY ai.name ASC
        ");
        $stmtItems->execute($groupIds);
        $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar items
        $itemsByGroup = [];
        foreach ($allItems as $item) {
            $itemsByGroup[$item['group_id']][] = $item;
        }

        $res = ['groups' => $groups, 'items' => $itemsByGroup];
        $cache->put($key, $res, 600);
        return $res;
    }

    // =========================================================================
    // CONFIGURAÇÃO E HORÁRIOS
    // =========================================================================

    public function getConfig(int $restaurantId): array
    {
        $cache = new Cache();
        $key = 'config_' . $restaurantId;
        $cached = $cache->get($key);
        if ($cached !== null) return array_merge(self::DEFAULT_CONFIG, $cached ?: []);

        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM cardapio_config WHERE restaurant_id = :rid');
        $stmt->execute(['rid' => $restaurantId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        $cfg = $config ?: self::DEFAULT_CONFIG;
        $cache->put($key, $cfg, 300);
        return $cfg;
    }

    public function getBusinessHours(int $restaurantId): array
    {
        $cache = new Cache();
        $key = 'hours_' . $restaurantId;
        $cached = $cache->get($key);
        if ($cached !== null) return $cached;

        $conn = Database::connect();

        $dayOfWeek = (int) date('w');
        $yesterdayDay = ($dayOfWeek - 1 < 0) ? 6 : $dayOfWeek - 1;

        $stmt = $conn->prepare('SELECT * FROM business_hours WHERE restaurant_id = :rid AND day_of_week = :day');

        $stmt->execute(['rid' => $restaurantId, 'day' => $dayOfWeek]);
        $today = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->execute(['rid' => $restaurantId, 'day' => $yesterdayDay]);
        $yesterday = $stmt->fetch(PDO::FETCH_ASSOC);

        $res = ['today' => $today ?: null, 'yesterday' => $yesterday ?: null];
        $cache->put($key, $res, 60);
        return $res;
    }
}
