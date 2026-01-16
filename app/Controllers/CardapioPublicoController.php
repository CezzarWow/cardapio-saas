<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * CARDÁPIO PÚBLICO - DDD Lite
 * Rota: /cardapio/{slug} ou /c/{id}
 * ═══════════════════════════════════════════════════════════════════════════
 */

namespace App\Controllers;

use App\Core\View;
use App\Services\CardapioPublico\CardapioPublicoQueryService;

class CardapioPublicoController
{
    /**
     * Exibe o cardápio público de um restaurante
     * @param int $restaurantId ID do restaurante
     */
    private CardapioPublicoQueryService $queryService;

    public function __construct(CardapioPublicoQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Exibe o cardápio público de um restaurante
     * @param int $restaurantId ID do restaurante
     */
    public function show($restaurantId)
    {
        $data = $this->queryService->getCardapioData((int) $restaurantId);

        if (!$data) {
            echo 'Restaurante não encontrado ou inativo.';
            exit;
        }

        // Verificar bloqueio manual (tela de loja fechada)
        if (!($data['cardapioConfig']['is_open'] ?? 1)) {
            $restaurant = $data['restaurant'];
            $cardapioConfig = $data['cardapioConfig'];
            View::render('loja_fechada', [
                'restaurant' => $restaurant,
                'cardapioConfig' => $cardapioConfig
            ]);
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

        // 1. Achata produtos — criar versão mínima para envio ao cliente (reduz payload)
        $flatProducts = [];
        if (!empty($productsByCategory)) {
            foreach ($productsByCategory as $cat => $prods) {
                foreach ($prods as $p) {
                    // Garante estrutura mínima
                    $additionals = $p['additionals'] ?? [];

                    // Sanitiza e encurta strings para evitar payloads enormes
                    $name = isset($p['name']) ? preg_replace('/[\r\n]+/', ' ', $p['name']) : '';
                    $description = isset($p['description']) ? preg_replace('/[\r\n]+/', ' ', $p['description']) : '';
                    if (mb_strlen($description) > 140) {
                        $description = mb_substr($description, 0, 137) . '...';
                    }

                    $flatProducts[] = [
                        'id' => $p['id'] ?? null,
                        'name' => $name,
                        'description' => $description,
                        'price' => isset($p['price']) ? (float)$p['price'] : 0.0,
                        'image' => $p['image'] ?? null,
                        'icon' => $p['icon'] ?? null,
                        'icon_as_photo' => $p['icon_as_photo'] ?? false,
                        'has_additionals' => !empty($additionals),
                        'category' => $cat,
                    ];
                }
            }
        }

        // 2. Sanitiza Combos — versão reduzida para frontend
        $safeCombos = [];
        if (!empty($combos)) {
            foreach ($combos as $c) {
                $desc = isset($c['description']) ? preg_replace('/[\r\n]+/', ' ', $c['description']) : '';
                if (mb_strlen($desc) > 140) $desc = mb_substr($desc, 0, 137) . '...';

                $safeCombos[] = [
                    'id' => $c['id'] ?? null,
                    'name' => $c['name'] ?? '',
                    'description' => $desc,
                    'price' => isset($c['price']) ? (float)$c['price'] : 0.0,
                    'image' => $c['image'] ?? null,
                    'products_list' => $c['products_list'] ?? null,
                ];
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

        // Renderizar view pública via View renderer
        View::render('cardapio_publico', [
            'restaurant' => $restaurant,
            'categories' => $categories,
            'allProducts' => $allProducts,
            'featuredProducts' => $featuredProducts,
            'productsByCategory' => $productsByCategory,
            'combos' => $combos,
            'additionalGroups' => $additionalGroups,
            'additionalItems' => $additionalItems,
            'productRelations' => $productRelations,
            'cardapioConfig' => $cardapioConfig,
            'todayHour' => $todayHour,
            'jsProducts' => $jsProducts,
            'jsCombos' => $jsCombos,
            'jsRelations' => $jsRelations,
            'jsConfig' => $jsConfig,
            'jsConfigRaw' => $jsConfigRaw
        ]);
    }

    /**
     * Exibe o cardápio público buscando pelo slug
     * @param string $slug Slug do restaurante
     */
    public function showBySlug($slug)
    {
        $restaurantId = $this->queryService->findRestaurantBySlug($slug);

        if (!$restaurantId) {
            echo '<h1>404 - Restaurante não encontrado 😢</h1>';
            return;
        }

        // Reutiliza o método show()
        $this->show($restaurantId);
    }
}
