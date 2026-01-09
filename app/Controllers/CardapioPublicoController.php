<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * CARDÁPIO PÚBLICO - DDD Lite
 * Rota: /cardapio/{slug} ou /c/{id}
 * ═══════════════════════════════════════════════════════════════════════════
 */

namespace App\Controllers;

use App\Services\CardapioPublico\CardapioPublicoQueryService;

class CardapioPublicoController {

    /**
     * Exibe o cardápio público de um restaurante
     * @param int $restaurantId ID do restaurante
     */
    private CardapioPublicoQueryService $queryService;

    public function __construct(CardapioPublicoQueryService $queryService) {
        $this->queryService = $queryService;
    }

    /**
     * Exibe o cardápio público de um restaurante
     * @param int $restaurantId ID do restaurante
     */
    public function show($restaurantId) {
        $data = $this->queryService->getCardapioData((int) $restaurantId);
        
        if (!$data) {
            echo "Restaurante não encontrado ou inativo.";
            exit;
        }

        // Verificar bloqueio manual (tela de loja fechada)
        if (!($data['cardapioConfig']['is_open'] ?? 1)) {
            $restaurant = $data['restaurant'];
            $cardapioConfig = $data['cardapioConfig'];
            require __DIR__ . '/../../views/loja_fechada.php';
            return;
        }

        // Extrair variáveis explicitamente
        $restaurant = $data['restaurant'];
        $categories = $data['categories'];
        $allProducts = $data['allProducts'];
        $featuredProducts = $data['featuredProducts'];
        $productsByCategory = $data['productsByCategory']; // Usado no loop PHP da view
        $combos = $data['combos'];
        $additionalGroups = $data['additionalGroups'];
        $additionalItems = $data['additionalItems'];
        $productRelations = $data['productRelations'];
        $cardapioConfig = $data['cardapioConfig'];
        $todayHour = $data['todayHour'] ?? null;

        // --- LÓGICA DE VIEW MOVIDA PARA CÁ ---
        // Prepara JSON seguro para o Front-end
        
        // 1. Achata produtos
        $flatProducts = [];
        if (!empty($productsByCategory)) {
            foreach ($productsByCategory as $cat => $prods) {
                foreach ($prods as $p) {
                    if (empty($p['additionals'])) $p['additionals'] = [];
                    // Sanitiza strings para não quebrar JS
                    if (isset($p['description'])) $p['description'] = preg_replace('/[\r\n]+/', ' ', $p['description']);
                    if (isset($p['name'])) $p['name'] = preg_replace('/[\r\n]+/', ' ', $p['name']);
                    $flatProducts[] = $p;
                }
            }
        }

        // 2. Sanitiza Combos
        $safeCombos = [];
        if (!empty($combos)) {
            foreach ($combos as $c) {
                if (isset($c['description'])) $c['description'] = preg_replace('/[\r\n]+/', ' ', $c['description']);
                $safeCombos[] = $c;
            }
        }

        // 3. Configurações (CamelCase para JS)
        $jsConfigArray = [
            'isOpen' => (bool)($cardapioConfig['is_open'] ?? 1),
            'deliveryEnabled' => (bool)($cardapioConfig['delivery_enabled'] ?? 1),
            'pickupEnabled' => (bool)($cardapioConfig['pickup_enabled'] ?? 1),
            'dineInEnabled' => (bool)($cardapioConfig['dine_in_enabled'] ?? 1),
            'deliveryFee' => (float)($cardapioConfig['delivery_fee'] ?? 5),
            'minOrderValue' => (float)($cardapioConfig['min_order_value'] ?? 20),
            'acceptCash' => (bool)($cardapioConfig['accept_cash'] ?? 1),
            'acceptCredit' => (bool)($cardapioConfig['accept_credit'] ?? 1),
            'acceptDebit' => (bool)($cardapioConfig['accept_debit'] ?? 1),
            'acceptPix' => (bool)($cardapioConfig['accept_pix'] ?? 1),
            'whatsappNumber' => $cardapioConfig['whatsapp_number'] ?? '',
            'closedMessage' => $cardapioConfig['closed_message'] ?? 'Estamos fechados no momento'
        ];

        // 4. Encoda JSON
        $jsProducts = json_encode($flatProducts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '[]';
        $jsCombos = json_encode($safeCombos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '[]';
        $jsRelations = json_encode($productRelations ?? []) ?: '[]';
        $jsConfig = json_encode($jsConfigArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '{}';
        // Raw config for checkout-order.js (keeps snake_case)
        $jsConfigRaw = json_encode($cardapioConfig ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '{}';

        // Renderizar view pública
        require __DIR__ . '/../../views/cardapio_publico.php';
    }

    /**
     * Exibe o cardápio público buscando pelo slug
     * @param string $slug Slug do restaurante
     */
    public function showBySlug($slug) {
        $restaurantId = $this->queryService->findRestaurantBySlug($slug);
        
        if (!$restaurantId) {
            echo "<h1>404 - Restaurante não encontrado 😢</h1>";
            return;
        }
        
        // Reutiliza o método show()
        $this->show($restaurantId);
    }
}
