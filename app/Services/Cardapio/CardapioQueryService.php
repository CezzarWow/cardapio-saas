<?php

namespace App\Services\Cardapio;

use App\Repositories\Cardapio\BusinessHoursRepository;
use App\Repositories\Cardapio\CardapioConfigRepository;
use App\Repositories\Cardapio\CategoryRepository;
use App\Repositories\Cardapio\ProductRepository;
use App\Repositories\ComboRepository;
use App\Repositories\RestaurantRepository;

/**
 * Query Service para leituras do Cardápio
 * Consolida todas as queries do index()
 */
class CardapioQueryService
{
    private CardapioConfigRepository $configRepository;
    private BusinessHoursRepository $hoursRepository;
    private CategoryRepository $categoryRepository;
    private ProductRepository $productRepository;
    private ComboRepository $comboRepository;
    private RestaurantRepository $restaurantRepository;
    private \App\Presenters\CardapioPresenter $presenter;
    private \App\Core\SimpleCache $cache;

    public function __construct(
        CardapioConfigRepository $configRepository,
        BusinessHoursRepository $hoursRepository,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ComboRepository $comboRepository,
        RestaurantRepository $restaurantRepository,
        \App\Presenters\CardapioPresenter $presenter,
        \App\Core\SimpleCache $cache
    ) {
        $this->configRepository = $configRepository;
        $this->hoursRepository = $hoursRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->comboRepository = $comboRepository;
        $this->restaurantRepository = $restaurantRepository;
        $this->presenter = $presenter;
        $this->cache = $cache;
    }

    /**
     * Retorna todos os dados necessários para a página de configurações
     */
    public function getIndexData(int $restaurantId): array
    {
        $cacheKey = "cardapio_index_{$restaurantId}";
        $cached = $this->cache->get($cacheKey);

        if ($cached) {
            return $cached;
        }

        // 1. Fetch Raw Data
        $config = $this->configRepository->findOrCreate($restaurantId);
        $businessHours = $this->hoursRepository->findOrCreate($restaurantId);
        $this->categoryRepository->ensureSystemCategories($restaurantId);
        $categories = $this->categoryRepository->findAllOrdered($restaurantId);
        $allProducts = $this->productRepository->findAllWithCategory($restaurantId);
        $productsByCategory = $this->productRepository->groupByCategory($allProducts);
        $rawCombos = $this->comboRepository->findAllWithItems($restaurantId);
        $restaurant = $this->restaurantRepository->find($restaurantId);
        $restaurantSlug = $restaurant['slug'] ?? (string) $restaurantId;

        // 2. Format Data (Presenter)
        $whatsappData = $this->presenter->formatWhatsAppMessages($config['whatsapp_message'] ?? '[]');
        $formattedCombos = $this->presenter->formatCombos($rawCombos);
        $formattedHours = $this->presenter->formatBusinessHours($businessHours);

        $data = [
            'config' => $config,
            'businessHours' => $businessHours, // Raw data (se necessário em algum lugar)
            'categories' => $categories,
            'allProducts' => $allProducts,
            'productsByCategory' => $productsByCategory,
            'combos' => $formattedCombos,
            'restaurantSlug' => $restaurantSlug,
            'beforeList' => $whatsappData['before'],
            'afterList' => $whatsappData['after'],
            'businessHoursList' => $formattedHours
        ];

        // Cache por 5 minutos
        $this->cache->put($cacheKey, $data, 300);

        return $data;
    }

    public function getComboFormData(int $restaurantId): array
    {
        return [
            'products' => $this->productRepository->findAllSimple($restaurantId)
        ];
    }
}
