<?php

namespace App\Services\CardapioPublico;

use App\Repositories\CardapioPublico\CardapioPublicoRepository;

/**
 * Query Service para o Cardápio Público
 * Consolida leituras e aplica lógica de horário
 */
class CardapioPublicoQueryService
{
    private CardapioPublicoRepository $repository;

    public function __construct(CardapioPublicoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Busca restaurante por slug
     * @return int|null ID do restaurante
     */
    public function findRestaurantBySlug(string $slug): ?int
    {
        return $this->repository->findRestaurantBySlug($slug);
    }

    /**
     * Retorna todos os dados necessários para exibir o cardápio público
     */
    public function getCardapioData(int $restaurantId): ?array
    {
        // Restaurante
        $restaurant = $this->repository->findRestaurantById($restaurantId);
        if (!$restaurant) {
            return null;
        }

        // Categorias
        $categories = $this->repository->getCategories($restaurantId);

        // Produtos
        $allProducts = $this->repository->getProducts($restaurantId);
        
        // Separar produtos destacados
        $featuredProducts = array_filter($allProducts, fn($p) => !empty($p['is_featured']));
        
        // Agrupar por categoria
        $productsByCategory = $this->groupProductsByCategory($allProducts);

        // Combos com itens
        $combos = $this->repository->getCombosWithItems($restaurantId);

        // Adicionais
        $additionalsData = $this->repository->getAdditionalsWithItems($restaurantId);
        $additionalGroups = $additionalsData['groups'];
        $additionalItems = $additionalsData['items'];

        // Relações produto <-> grupo
        $productRelations = $this->repository->getProductAdditionalRelations();

        // Config
        $cardapioConfig = $this->repository->getConfig($restaurantId);

        // Horários e status de abertura
        $hours = $this->repository->getBusinessHours($restaurantId);
        $openStatus = $this->calculateOpenStatus($cardapioConfig, $hours);

        // Merge status no config
        $cardapioConfig = array_merge($cardapioConfig, $openStatus);

        return [
            'restaurant' => $restaurant,
            'categories' => $categories,
            'allProducts' => $allProducts,
            'featuredProducts' => array_values($featuredProducts),
            'productsByCategory' => $productsByCategory,
            'combos' => $combos,
            'additionalGroups' => $additionalGroups,
            'additionalItems' => $additionalItems,
            'productRelations' => $productRelations,
            'cardapioConfig' => $cardapioConfig,
            'todayHour' => $hours['today']
        ];
    }

    /**
     * Agrupa produtos por nome de categoria
     */
    private function groupProductsByCategory(array $products): array
    {
        $grouped = [];
        foreach ($products as $product) {
            $catName = $product['category_name'] ?? 'Sem Categoria';
            if (!isset($grouped[$catName])) {
                $grouped[$catName] = [];
            }
            $grouped[$catName][] = $product;
        }
        return $grouped;
    }

    /**
     * Calcula se está aberto agora (lógica de horário com suporte a madrugada)
     */
    private function calculateOpenStatus(array $config, array $hours): array
    {
        $currentTime = date('H:i:s');
        $todayHour = $hours['today'];
        $yesterdayHour = $hours['yesterday'];

        // Override manual (is_open = 0 no config)
        if (!($config['is_open'] ?? 1)) {
            return [
                'is_open_now' => false,
                'closed_reason' => 'manual_closed',
                'today_hours' => $todayHour
            ];
        }

        $isOpenByToday = false;
        $isOpenByYesterday = false;

        // A) Checa horário de HOJE
        if ($todayHour && $todayHour['is_open']) {
            $open = $todayHour['open_time'];
            $close = $todayHour['close_time'];
            
            if ($close < $open) {
                // Vira a noite (ex: 18:00 às 02:00)
                if ($currentTime >= $open) $isOpenByToday = true;
            } else {
                // Normal (ex: 08:00 às 22:00)
                if ($currentTime >= $open && $currentTime <= $close) $isOpenByToday = true;
            }
        }

        // B) Checa horário de ONTEM (madrugada)
        if ($yesterdayHour && $yesterdayHour['is_open']) {
            $yOpen = $yesterdayHour['open_time'];
            $yClose = $yesterdayHour['close_time'];
            
            if ($yClose < $yOpen) {
                if ($currentTime <= $yClose) $isOpenByYesterday = true;
            }
        }

        $isOpenNow = $isOpenByToday || $isOpenByYesterday;
        $closedReason = '';
        
        if (!$isOpenNow) {
            $closedReason = ($todayHour && !$todayHour['is_open']) ? 'day_closed' : 'outside_hours';
        }

        return [
            'is_open_now' => $isOpenNow,
            'closed_reason' => $closedReason,
            'today_hours' => $todayHour
        ];
    }
}
