<?php

namespace App\Repositories\CardapioPublico;

use App\Core\Database;
use PDO;

/**
 * Repository para o Cardápio Público (leituras)
 */
class CardapioPublicoRepository
{
    /**
     * Busca restaurante por ID
     */
    public function findRestaurantById(int $id): ?array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = :rid");
        $stmt->execute(['rid' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca restaurante por slug
     */
    public function findRestaurantBySlug(string $slug): ?int
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT id FROM restaurants WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Busca categorias ativas ordenadas
     */
    public function getCategories(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT * FROM categories 
            WHERE restaurant_id = :rid AND is_active = 1
            ORDER BY COALESCE(sort_order, 999) ASC, name ASC
        ");
        $stmt->execute(['rid' => $restaurantId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca produtos com categoria
     */
    public function getProducts(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT 
                p.id,
                p.name,
                p.description,
                p.price,
                p.image,
                p.stock,
                p.is_featured,
                p.icon,
                p.icon_as_photo,
                c.id as category_id,
                c.name as category_name,
                c.category_type,
                c.sort_order as category_order
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.restaurant_id = :rid
            ORDER BY COALESCE(c.sort_order, 999) ASC, c.name ASC, p.display_order ASC, p.name ASC
        ");
        $stmt->execute(['rid' => $restaurantId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca combos ativos com seus itens
     */
    public function getCombosWithItems(int $restaurantId): array
    {
        $conn = Database::connect();
        
        // Buscar combos
        $stmt = $conn->prepare("
            SELECT * FROM combos 
            WHERE restaurant_id = :rid AND is_active = 1
            ORDER BY display_order ASC, name ASC
        ");
        $stmt->execute(['rid' => $restaurantId]);
        $combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($combos)) {
            return [];
        }

        // Buscar itens dos combos
        $comboIds = array_column($combos, 'id');
        $inQuery = implode(',', array_fill(0, count($comboIds), '?'));
        
        $stmtItems = $conn->prepare("
            SELECT 
                ci.combo_id,
                ci.allow_additionals,
                p.id as product_id,
                p.name as product_name,
                p.image as product_image
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
            $cid = $item['combo_id'];
            if (!isset($itemsByCombo[$cid])) $itemsByCombo[$cid] = [];
            $itemsByCombo[$cid][] = $item;
        }
        
        // Injetar itens nos combos
        foreach ($combos as &$combo) {
            $combo['items'] = $itemsByCombo[$combo['id']] ?? [];
            $combo['products_list'] = implode(', ', array_column($combo['items'], 'product_name'));
        }

        return $combos;
    }

    /**
     * Busca grupos de adicionais com itens
     */
    public function getAdditionalsWithItems(int $restaurantId): array
    {
        $conn = Database::connect();
        
        // Buscar grupos
        $stmt = $conn->prepare("
            SELECT * FROM additional_groups 
            WHERE restaurant_id = :rid 
            ORDER BY name ASC
        ");
        $stmt->execute(['rid' => $restaurantId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($groups)) {
            return ['groups' => [], 'items' => []];
        }

        // Buscar itens de todos os grupos
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
            $gid = $item['group_id'];
            if (!isset($itemsByGroup[$gid])) $itemsByGroup[$gid] = [];
            $itemsByGroup[$gid][] = $item;
        }

        return ['groups' => $groups, 'items' => $itemsByGroup];
    }

    /**
     * Busca relações produto <-> grupo de adicionais
     */
    public function getProductAdditionalRelations(): array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT product_id, group_id FROM product_additional_relations");
        $stmt->execute();
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $relations = [];
        foreach ($raw as $rel) {
            $relations[$rel['product_id']][] = $rel['group_id'];
        }
        
        return $relations;
    }

    /**
     * Busca configuração do cardápio
     */
    public function getConfig(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT * FROM cardapio_config WHERE restaurant_id = :rid");
        $stmt->execute(['rid' => $restaurantId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Valores padrão
        if (!$config) {
            return [
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
        }
        
        return $config;
    }

    /**
     * Busca horários de funcionamento de hoje e ontem
     */
    public function getBusinessHours(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $dayOfWeek = (int) date('w');
        $yesterdayDay = ($dayOfWeek - 1 < 0) ? 6 : $dayOfWeek - 1;
        
        // Hoje
        $stmt = $conn->prepare("SELECT * FROM business_hours WHERE restaurant_id = :rid AND day_of_week = :day");
        $stmt->execute(['rid' => $restaurantId, 'day' => $dayOfWeek]);
        $today = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Ontem
        $stmt->execute(['rid' => $restaurantId, 'day' => $yesterdayDay]);
        $yesterday = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ['today' => $today, 'yesterday' => $yesterday];
    }
}
