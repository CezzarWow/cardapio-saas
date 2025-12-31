<?php
/**
 * ============================================
 * CARDÁPIO CONTROLLER
 * Gerencia configurações do cardápio web
 * ============================================
 */

namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class CardapioController {

    /**
     * Exibe a página de configurações
     */
    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        // Busca configuração atual (ou cria padrão)
        $stmt = $conn->prepare("SELECT * FROM cardapio_config WHERE restaurant_id = :rid");
        $stmt->execute(['rid' => $restaurantId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não existe, cria registro padrão
        if (!$config) {
            $conn->prepare("INSERT INTO cardapio_config (restaurant_id) VALUES (:rid)")
                 ->execute(['rid' => $restaurantId]);
            
            $stmt->execute(['rid' => $restaurantId]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // [ETAPA 2] Buscar horários de funcionamento
        $stmtHours = $conn->prepare("SELECT * FROM business_hours WHERE restaurant_id = :rid");
        $stmtHours->execute(['rid' => $restaurantId]);
        $hoursRaw = $stmtHours->fetchAll(PDO::FETCH_ASSOC);

        // Organizar por dia da semana
        $businessHours = [];
        foreach ($hoursRaw as $h) {
            $businessHours[$h['day_of_week']] = $h;
        }

        // Se não existem horários, criar os 7 dias padrão
        if (empty($businessHours)) {
            $this->createDefaultHours($conn, $restaurantId);
            $stmtHours->execute(['rid' => $restaurantId]);
            $hoursRaw = $stmtHours->fetchAll(PDO::FETCH_ASSOC);
            foreach ($hoursRaw as $h) {
                $businessHours[$h['day_of_week']] = $h;
            }
        }

        // [ETAPA 3] Buscar combos
        $stmtCombos = $conn->prepare("SELECT * FROM combos WHERE restaurant_id = :rid ORDER BY display_order, name");
        $stmtCombos->execute(['rid' => $restaurantId]);
        $combos = $stmtCombos->fetchAll(PDO::FETCH_ASSOC);

        // [ETAPA 3] Buscar todos os produtos para destaques
        $stmtProducts = $conn->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.restaurant_id = :rid 
            ORDER BY c.sort_order ASC, c.name ASC, p.display_order ASC, p.name ASC
        ");
        $stmtProducts->execute(['rid' => $restaurantId]);
        $allProducts = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

        // [ETAPA 5] Buscar slug do restaurante para o link "Ver Cardápio"
        $stmtRestaurant = $conn->prepare("SELECT slug FROM restaurants WHERE id = :rid");
        $stmtRestaurant->execute(['rid' => $restaurantId]);
        $restaurantSlug = $stmtRestaurant->fetchColumn() ?: $restaurantId;

        // [DESTAQUES] Buscar categorias ordenadas por sort_order
        $stmtCategories = $conn->prepare("
            SELECT * FROM categories 
            WHERE restaurant_id = :rid 
            ORDER BY COALESCE(sort_order, 999) ASC, name ASC
        ");
        $stmtCategories->execute(['rid' => $restaurantId]);
        $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

        // [DESTAQUES] Agrupar produtos por categoria
        $productsByCategory = [];
        foreach ($allProducts as $product) {
            $catName = $product['category_name'] ?? 'Sem categoria';
            if (!isset($productsByCategory[$catName])) {
                $productsByCategory[$catName] = [];
            }
            $productsByCategory[$catName][] = $product;
        }

        // Renderiza a view
        require __DIR__ . '/../../../views/admin/cardapio/index.php';
    }

    /**
     * Cria horários padrão para os 7 dias da semana
     */
    private function createDefaultHours($conn, $restaurantId) {
        $defaults = [
            0 => ['is_open' => 0, 'open' => '09:00', 'close' => '22:00'], // Domingo
            1 => ['is_open' => 1, 'open' => '09:00', 'close' => '22:00'],
            2 => ['is_open' => 1, 'open' => '09:00', 'close' => '22:00'],
            3 => ['is_open' => 1, 'open' => '09:00', 'close' => '22:00'],
            4 => ['is_open' => 1, 'open' => '09:00', 'close' => '22:00'],
            5 => ['is_open' => 1, 'open' => '09:00', 'close' => '23:00'], // Sexta
            6 => ['is_open' => 1, 'open' => '09:00', 'close' => '23:00'], // Sábado
        ];

        $stmt = $conn->prepare("INSERT INTO business_hours (restaurant_id, day_of_week, is_open, open_time, close_time) VALUES (:rid, :day, :is_open, :open, :close)");
        
        foreach ($defaults as $day => $h) {
            $stmt->execute([
                'rid' => $restaurantId,
                'day' => $day,
                'is_open' => $h['is_open'],
                'open' => $h['open'],
                'close' => $h['close']
            ]);
        }
    }

    /**
     * Salva as configurações
     */
    public function update() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        // Processa valores monetários (troca vírgula por ponto)
        $deliveryFee = str_replace(',', '.', $_POST['delivery_fee'] ?? '5');
        $deliveryFee = preg_replace('/[^\d.]/', '', $deliveryFee);
        
        $minOrderValue = str_replace(',', '.', $_POST['min_order_value'] ?? '20');
        $minOrderValue = preg_replace('/[^\d.]/', '', $minOrderValue);

        // WhatsApp (Ajuste [ETAPA 6]: Listas Dinâmicas Antes/Depois)
        $whatsappData = $_POST['whatsapp_data'] ?? null;
        
        if ($whatsappData && is_array($whatsappData)) {
            // Estrutura Nova: {before: [], after: []}
            $finalMessages = [
                'before' => array_values(array_filter($whatsappData['before'] ?? [], fn($m) => !empty(trim($m)))),
                'after' => array_values(array_filter($whatsappData['after'] ?? [], fn($m) => !empty(trim($m))))
            ];
            $jsonMessages = json_encode($finalMessages, JSON_UNESCAPED_UNICODE);
        } else {
            // Legado (Array Simples ou String)
            $whatsappMessages = $_POST['whatsapp_messages'] ?? [];
            if (!is_array($whatsappMessages)) {
                $whatsappMessages = [$whatsappMessages];
            }
            $whatsappMessages = array_values(array_filter($whatsappMessages, fn($m) => !empty(trim($m))));
            $jsonMessages = json_encode($whatsappMessages, JSON_UNESCAPED_UNICODE);
        }

        // Coleta dados do formulário
        $data = [
            // WhatsApp
            'whatsapp_enabled' => isset($_POST['whatsapp_enabled']) ? 1 : 0,
            'whatsapp_number' => preg_replace('/\D/', '', $_POST['whatsapp_number'] ?? ''),
            'whatsapp_message' => $jsonMessages,
            
            // Operação
            'is_open' => isset($_POST['is_open']) ? 1 : 0,
            'opening_time' => $_POST['opening_time'] ?? '08:00',
            'closing_time' => $_POST['closing_time'] ?? '22:00',
            'closed_message' => trim($_POST['closed_message'] ?? 'Estamos fechados no momento'),
            
            // Delivery
            'delivery_enabled' => isset($_POST['delivery_enabled']) ? 1 : 0,
            'delivery_fee' => floatval($deliveryFee),
            'min_order_value' => floatval($minOrderValue),
            'delivery_time_min' => intval($_POST['delivery_time_min'] ?? 30),
            'delivery_time_max' => intval($_POST['delivery_time_max'] ?? 45),
            
            // Retirada e Local
            'pickup_enabled' => isset($_POST['pickup_enabled']) ? 1 : 0,
            'dine_in_enabled' => isset($_POST['dine_in_enabled']) ? 1 : 0,
            
            // Pagamentos (accept_card grava em credit e debit para compatibilidade)
            'accept_cash' => isset($_POST['accept_cash']) ? 1 : 0,
            'accept_credit' => isset($_POST['accept_card']) ? 1 : 0,
            'accept_debit' => isset($_POST['accept_card']) ? 1 : 0,
            'accept_pix' => isset($_POST['accept_pix']) ? 1 : 0,
            'pix_key' => trim($_POST['pix_key'] ?? ''),
            'pix_key_type' => $_POST['pix_key_type'] ?? 'telefone',
        ];

        // Atualiza no banco
        $sql = "UPDATE cardapio_config SET 
                    whatsapp_enabled = :whatsapp_enabled,
                    whatsapp_number = :whatsapp_number,
                    whatsapp_message = :whatsapp_message,
                    is_open = :is_open,
                    opening_time = :opening_time,
                    closing_time = :closing_time,
                    closed_message = :closed_message,
                    delivery_enabled = :delivery_enabled,
                    delivery_fee = :delivery_fee,
                    min_order_value = :min_order_value,
                    delivery_time_min = :delivery_time_min,
                    delivery_time_max = :delivery_time_max,
                    pickup_enabled = :pickup_enabled,
                    dine_in_enabled = :dine_in_enabled,
                    accept_cash = :accept_cash,
                    accept_credit = :accept_credit,
                    accept_debit = :accept_debit,
                    accept_pix = :accept_pix,
                    pix_key = :pix_key,
                    pix_key_type = :pix_key_type
                WHERE restaurant_id = :rid";

        $data['rid'] = $restaurantId;
        $conn->prepare($sql)->execute($data);

        // [ETAPA 2] Salvar horários de funcionamento
        $hours = $_POST['hours'] ?? [];
        $stmtHour = $conn->prepare("
            INSERT INTO business_hours (restaurant_id, day_of_week, is_open, open_time, close_time)
            VALUES (:rid, :day, :is_open, :open, :close)
            ON DUPLICATE KEY UPDATE 
                is_open = VALUES(is_open),
                open_time = VALUES(open_time),
                close_time = VALUES(close_time)
        ");

        for ($day = 0; $day <= 6; $day++) {
            $stmtHour->execute([
                'rid' => $restaurantId,
                'day' => $day,
                'is_open' => isset($hours[$day]['is_open']) ? 1 : 0,
                'open' => $hours[$day]['open_time'] ?? '09:00',
                'close' => $hours[$day]['close_time'] ?? '22:00'
            ]);
        }

        // [ETAPA 3] Salvar destaques de produtos
        $featured = $_POST['featured'] ?? [];
        
        // Primeiro, remove todos os destaques do restaurante
        $conn->prepare("UPDATE products SET is_featured = 0 WHERE restaurant_id = :rid")
             ->execute(['rid' => $restaurantId]);
        
        // Depois, marca os selecionados
        if (!empty($featured)) {
            $stmtFeat = $conn->prepare("UPDATE products SET is_featured = 1 WHERE id = :pid AND restaurant_id = :rid");
            foreach (array_keys($featured) as $productId) {
                $stmtFeat->execute(['pid' => $productId, 'rid' => $restaurantId]);
            }
        }

        // [DESTAQUES] Salvar prioridade/ordem das categorias
        $categoryOrder = $_POST['category_order'] ?? [];
        if (!empty($categoryOrder)) {
            $stmtCatOrder = $conn->prepare("UPDATE categories SET sort_order = :order WHERE id = :cid AND restaurant_id = :rid");
            foreach ($categoryOrder as $catId => $order) {
                $stmtCatOrder->execute([
                    'order' => intval($order),
                    'cid' => intval($catId),
                    'rid' => $restaurantId
                ]);
            }
        }

        // [DESTAQUES] Salvar estado de habilitação das categorias
        // Primeiro, desabilita todas as categorias do restaurante
        $conn->prepare("UPDATE categories SET is_active = 0 WHERE restaurant_id = :rid")
             ->execute(['rid' => $restaurantId]);
        
        // Depois, habilita as selecionadas
        $categoryEnabled = $_POST['category_enabled'] ?? [];
        if (!empty($categoryEnabled)) {
            $stmtCatEnabled = $conn->prepare("UPDATE categories SET is_active = 1 WHERE id = :cid AND restaurant_id = :rid");
            foreach (array_keys($categoryEnabled) as $catId) {
                $stmtCatEnabled->execute(['cid' => intval($catId), 'rid' => $restaurantId]);
            }
        }

        // [DESTAQUES] Salvar ordem dos produtos
        $productOrder = $_POST['product_order'] ?? [];
        
        // DEBUG: SEMPRE salva o que está sendo recebido
        $debugContent = "product_order recebido:\n";
        $debugContent .= empty($productOrder) ? "VAZIO!" : print_r($productOrder, true);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cardapio-saas/debug_order.txt', $debugContent);
        
        if (!empty($productOrder)) {
            $stmtProdOrder = $conn->prepare("UPDATE products SET display_order = :order WHERE id = :pid AND restaurant_id = :rid");
            foreach ($productOrder as $prodId => $order) {
                $stmtProdOrder->execute([
                    'order' => intval($order),
                    'pid' => intval($prodId),
                    'rid' => $restaurantId
                ]);
            }
        }

        // Log da ação
        if (class_exists('\App\Core\Logger')) {
            \App\Core\Logger::info('Configurações do cardápio atualizadas', [
                'restaurant_id' => $restaurantId
            ]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=salvo#destaques');
        exit;
    }

    /**
     * [ETAPA 3] Exibe formulário de novo combo
     */
    public function comboForm() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $combo = null;
        $comboProducts = [];

        // Buscar produtos
        $stmt = $conn->prepare("SELECT * FROM products WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/cardapio/combo_form.php';
    }

    /**
     * [ETAPA 3] Salva novo combo
     */
    public function storeCombo() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $price = str_replace(',', '.', $_POST['price'] ?? '0');
        $price = preg_replace('/[^\d.]/', '', $price);

        // Inserir combo
        $stmt = $conn->prepare("INSERT INTO combos (restaurant_id, name, description, price, display_order, is_active) VALUES (:rid, :name, :desc, :price, :order, :active)");
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => trim($_POST['name']),
            'desc' => trim($_POST['description'] ?? ''),
            'price' => floatval($price),
            'order' => intval($_POST['display_order'] ?? 0),
            'active' => isset($_POST['is_active']) ? 1 : 0
        ]);

        $comboId = $conn->lastInsertId();

        // Inserir produtos do combo
        $products = $_POST['products'] ?? [];
        if (!empty($products)) {
            $stmtItem = $conn->prepare("INSERT INTO combo_items (combo_id, product_id) VALUES (:cid, :pid)");
            foreach ($products as $pid) {
                $stmtItem->execute(['cid' => $comboId, 'pid' => $pid]);
            }
        }

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=combo_criado');
        exit;
    }

    /**
     * [ETAPA 3] Exibe formulário de edição
     */
    public function editCombo() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $id = intval($_GET['id'] ?? 0);

        // Buscar combo
        $stmt = $conn->prepare("SELECT * FROM combos WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        $combo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$combo) {
            header('Location: ' . BASE_URL . '/admin/loja/cardapio?error=combo_nao_encontrado');
            exit;
        }

        // Buscar produtos do combo
        $stmtItems = $conn->prepare("SELECT product_id FROM combo_items WHERE combo_id = :cid");
        $stmtItems->execute(['cid' => $id]);
        $comboProducts = $stmtItems->fetchAll(PDO::FETCH_COLUMN);

        // Buscar produtos
        $stmt = $conn->prepare("SELECT * FROM products WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/cardapio/combo_form.php';
    }

    /**
     * [ETAPA 3] Atualiza combo
     */
    public function updateCombo() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $id = intval($_POST['id'] ?? 0);
        $price = str_replace(',', '.', $_POST['price'] ?? '0');
        $price = preg_replace('/[^\d.]/', '', $price);

        // Atualizar combo
        $stmt = $conn->prepare("UPDATE combos SET name = :name, description = :desc, price = :price, display_order = :order, is_active = :active WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute([
            'name' => trim($_POST['name']),
            'desc' => trim($_POST['description'] ?? ''),
            'price' => floatval($price),
            'order' => intval($_POST['display_order'] ?? 0),
            'active' => isset($_POST['is_active']) ? 1 : 0,
            'id' => $id,
            'rid' => $restaurantId
        ]);

        // Atualizar produtos do combo
        $conn->prepare("DELETE FROM combo_items WHERE combo_id = :cid")->execute(['cid' => $id]);
        
        $products = $_POST['products'] ?? [];
        if (!empty($products)) {
            $stmtItem = $conn->prepare("INSERT INTO combo_items (combo_id, product_id) VALUES (:cid, :pid)");
            foreach ($products as $pid) {
                $stmtItem->execute(['cid' => $id, 'pid' => $pid]);
            }
        }

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=combo_atualizado');
        exit;
    }

    /**
     * [ETAPA 3] Deleta combo
     */
    public function deleteCombo() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $id = intval($_GET['id'] ?? 0);

        $conn->prepare("DELETE FROM combos WHERE id = :id AND restaurant_id = :rid")
             ->execute(['id' => $id, 'rid' => $restaurantId]);

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=combo_deletado');
        exit;
    }

    /**
     * Verifica sessão ativa
     */
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
