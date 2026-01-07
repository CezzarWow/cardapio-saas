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
    public function show($restaurantId) {
        $queryService = new CardapioPublicoQueryService();
        $data = $queryService->getCardapioData((int) $restaurantId);
        
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

        // Extrair variáveis explicitamente para compatibilidade com view
        $restaurant = $data['restaurant'];
        $categories = $data['categories'];
        $allProducts = $data['allProducts'];
        $featuredProducts = $data['featuredProducts'];
        $productsByCategory = $data['productsByCategory'];
        $combos = $data['combos'];
        $additionalGroups = $data['additionalGroups'];
        $additionalItems = $data['additionalItems'];
        $productRelations = $data['productRelations'];
        $cardapioConfig = $data['cardapioConfig'];
        $todayHour = $data['todayHour'] ?? null;
        
        // Renderizar view pública
        require __DIR__ . '/../../views/cardapio_publico.php';
    }

    /**
     * Exibe o cardápio público buscando pelo slug
     * @param string $slug Slug do restaurante
     */
    public function showBySlug($slug) {
        $queryService = new CardapioPublicoQueryService();
        $restaurantId = $queryService->findRestaurantBySlug($slug);
        
        if (!$restaurantId) {
            echo "<h1>404 - Restaurante não encontrado 😢</h1>";
            return;
        }
        
        // Reutiliza o método show()
        $this->show($restaurantId);
    }
}
