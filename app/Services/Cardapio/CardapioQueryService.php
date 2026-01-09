<?php

namespace App\Services\Cardapio;

use App\Repositories\Cardapio\CardapioConfigRepository;
use App\Repositories\Cardapio\BusinessHoursRepository;
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

    public function __construct(
        CardapioConfigRepository $configRepository,
        BusinessHoursRepository $hoursRepository,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ComboRepository $comboRepository,
        RestaurantRepository $restaurantRepository
    ) {
        $this->configRepository = $configRepository;
        $this->hoursRepository = $hoursRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->comboRepository = $comboRepository;
        $this->restaurantRepository = $restaurantRepository;
    }

    /**
     * Retorna todos os dados necessários para a página de configurações
     */
    public function getIndexData(int $restaurantId): array
    {
        // Config
        $config = $this->configRepository->findOrCreate($restaurantId);
        
        // Horários
        $businessHours = $this->hoursRepository->findOrCreate($restaurantId);
        
        // Categorias de sistema
        $this->categoryRepository->ensureSystemCategories($restaurantId);
        
        // Categorias ordenadas
        $categories = $this->categoryRepository->findAllOrdered($restaurantId);
        
        // Produtos com categoria
        $allProducts = $this->productRepository->findAllWithCategory($restaurantId);
        
        // Produtos agrupados por categoria
        $productsByCategory = $this->productRepository->groupByCategory($allProducts);
        
        // Combos com itens processados
        $combos = $this->getCombosWithItems($restaurantId);
        
        // Slug do restaurante
        $restaurant = $this->restaurantRepository->find($restaurantId);
        $restaurantSlug = $restaurant['slug'] ?? (string) $restaurantId;

        return [
            'config' => $config,
            'businessHours' => $businessHours,
            'categories' => $categories,
            'allProducts' => $allProducts,
            'productsByCategory' => $productsByCategory,
            'combos' => $combos,
            'restaurantSlug' => $restaurantSlug
        ];
    }

    /**
     * Retorna dados para formulário de combo
     */
    public function getComboFormData(int $restaurantId): array
    {
        return [
            'products' => $this->productRepository->findAllSimple($restaurantId)
        ];
    }

    /**
     * Busca combos com itens e calcula desconto
     */
    private function getCombosWithItems(int $restaurantId): array
    {
        // Usa Repository otimizado
        $combos = $this->comboRepository->findAllWithItems($restaurantId);

        // Processar dados para a view (lógica de apresentação)
        foreach ($combos as &$combo) {
            $items = $combo['items'] ?? [];
            unset($combo['items']); // Limpa array bruto se não for necessário na view, ou mantem.

            $counts = [];
            $originalPrice = 0;

            foreach ($items as $it) {
                $name = $it['name'];
                $counts[$name] = ($counts[$name] ?? 0) + 1;
                $originalPrice += floatval($it['price']);
            }

            // Formatar lista: "2 X-Burger + 1 Coca"
            $descParts = [];
            foreach ($counts as $name => $qty) {
                $descParts[] = ($qty > 1 ? "{$qty} " : "") . $name;
            }
            
            $combo['items_description'] = implode(" + ", $descParts);
            $combo['original_price'] = $originalPrice;
            
            // Calcular desconto
            if ($originalPrice > 0) {
                $discount = (($originalPrice - $combo['price']) / $originalPrice) * 100;
                $combo['discount_percent'] = round($discount);
            } else {
                $combo['discount_percent'] = 0;
            }
        }
        
        return $combos;
    }
}
