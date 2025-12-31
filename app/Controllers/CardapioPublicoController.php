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
            ORDER BY p.display_order ASC, c.name ASC, p.name ASC
        ");
        $stmtProducts->execute(['rid' => $restaurantId]);
        $allProducts = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
        
        // [ETAPA 3] Separar produtos destacados
        $featuredProducts = [];
        foreach ($allProducts as $product) {
            if (!empty($product['is_featured'])) {
                $featuredProducts[] = $product;
            }
        }

        // Organizar por categoria
        $productsByCategory = [];
        foreach ($allProducts as $product) {
            $catName = $product['category_name'] ?? 'Sem Categoria';
            if (!isset($productsByCategory[$catName])) {
                $productsByCategory[$catName] = [];
            }
            $productsByCategory[$catName][] = $product;
        }

        // [ETAPA 3] Buscar combos ativos
        $stmtCombos = $conn->prepare("
            SELECT c.*, 
                   GROUP_CONCAT(p.name SEPARATOR ', ') as products_list
            FROM combos c
            LEFT JOIN combo_items ci ON ci.combo_id = c.id
            LEFT JOIN products p ON p.id = ci.product_id
            WHERE c.restaurant_id = :rid AND c.is_active = 1
            GROUP BY c.id
            ORDER BY c.display_order ASC, c.name ASC
        ");
        $stmtCombos->execute(['rid' => $restaurantId]);
        $combos = $stmtCombos->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        // [NOVO] Buscar relações Produto <-> Grupo de Adicionais
        $stmtRelations = $conn->prepare("SELECT product_id, group_id FROM product_additional_relations");
        $stmtRelations->execute();
        $rawRelations = $stmtRelations->fetchAll(PDO::FETCH_ASSOC);

        $productRelations = [];
        foreach ($rawRelations as $rel) {
            $productRelations[$rel['product_id']][] = $rel['group_id'];
        }

        // [ETAPA 1.1] Buscar configurações do cardápio
        $stmtConfig = $conn->prepare("SELECT * FROM cardapio_config WHERE restaurant_id = :rid");
        $stmtConfig->execute(['rid' => $restaurantId]);
        $cardapioConfig = $stmtConfig->fetch(PDO::FETCH_ASSOC);
        
        // Se não existir config, usa valores padrão
        if (!$cardapioConfig) {
            $cardapioConfig = [
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

        // [ETAPA 2] Buscar horário do dia atual e verificar se está aberto
        $dayOfWeek = (int) date('w'); // 0=Dom, 1=Seg, ..., 6=Sab
        $currentTime = date('H:i:s');
        
        $stmtHour = $conn->prepare("SELECT * FROM business_hours WHERE restaurant_id = :rid AND day_of_week = :day");
        $stmtHour->execute(['rid' => $restaurantId, 'day' => $dayOfWeek]);
        $todayHour = $stmtHour->fetch(PDO::FETCH_ASSOC);

        // Determinar se está aberto (lógica automática)
        $isOpenNow = true; // Assume aberto por padrão
        $closedReason = '';

        // 1. Override manual (is_open = 0)
        if (!($cardapioConfig['is_open'] ?? 1)) {
            $isOpenNow = false;
            $closedReason = 'manual';
        }
        // 2. Dia fechado na configuração
        elseif ($todayHour && !$todayHour['is_open']) {
            $isOpenNow = false;
            $closedReason = 'day_closed';
        }
        // 3. Fora do horário de funcionamento
        elseif ($todayHour && ($currentTime < $todayHour['open_time'] || $currentTime > $todayHour['close_time'])) {
            $isOpenNow = false;
            $closedReason = 'outside_hours';
        }

        // Sobrescreve is_open no config para a view usar
        $cardapioConfig['is_open_now'] = $isOpenNow;
        $cardapioConfig['closed_reason'] = $closedReason;
        $cardapioConfig['today_hours'] = $todayHour;
        
        // Renderizar view pública
        require __DIR__ . '/../../views/cardapio_publico.php';
    }

    /**
     * Exibe o cardápio público buscando pelo slug
     * @param string $slug Slug do restaurante
     */
    public function showBySlug($slug) {
        $conn = Database::connect();
        
        // Buscar ID do restaurante pelo slug
        $stmt = $conn->prepare("SELECT id FROM restaurants WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        $restaurantId = $stmt->fetchColumn();
        
        if (!$restaurantId) {
            echo "<h1>404 - Restaurante não encontrado 😢</h1>";
            return;
        }
        
        // Reutiliza o método show() passando o ID
        $this->show($restaurantId);
    }
}
