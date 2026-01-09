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
    private \App\Core\SimpleCache $cache;

    public function __construct(
        CardapioConfigRepository $configRepository,
        BusinessHoursRepository $hoursRepository,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ComboRepository $comboRepository,
        RestaurantRepository $restaurantRepository,
        \App\Core\SimpleCache $cache
    ) {
        $this->configRepository = $configRepository;
        $this->hoursRepository = $hoursRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->comboRepository = $comboRepository;
        $this->restaurantRepository = $restaurantRepository;
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

        // Processar mensagens do WhatsApp
        $whatsappData = $this->prepareWhatsAppMessages($config['whatsapp_message'] ?? '[]');

        $data = [
            'config' => $config,
            'businessHours' => $businessHours,
            'categories' => $categories,
            'allProducts' => $allProducts,
            'productsByCategory' => $productsByCategory,
            'combos' => $combos,
            'restaurantSlug' => $restaurantSlug,
            'beforeList' => $whatsappData['before'],
            'afterList' => $whatsappData['after'],
            'businessHoursList' => $this->prepareBusinessHours($businessHours)
        ];

        // Cache por 5 minutos (300s)
        $this->cache->put($cacheKey, $data, 300);

        return $data;
    }

    /**
     * Prepara a lista de horários (Merge de defaults com DB)
     */
    private function prepareBusinessHours(array $dbHours): array
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado'
        ];

        $list = [];
        foreach ($days as $dayNum => $dayName) {
            // Default config: Segunda a Sexta aberto, Sáb/Dom fechado (ou ajustável)
            // Aqui mantemos a lógica que estava na View, mas centralizada
            $defaultOpen = ($dayNum > 0 && $dayNum < 6);
            
            $current = $dbHours[$dayNum] ?? [];
            
            $list[$dayNum] = [
                'name' => $dayName,
                'is_open' => $current['is_open'] ?? $defaultOpen,
                'open_time' => $current['open_time'] ?? '09:00',
                'close_time' => $current['close_time'] ?? '22:00'
            ];
        }

        return $list;
    }

    /**
     * Processa e prepara as mensagens do WhatsApp (Lógica movida da View)
     */
    private function prepareWhatsAppMessages(string $json): array
    {
        $data = json_decode($json, true);

        $beforeList = [];
        $afterList = [];

        if (isset($data['before']) || isset($data['after'])) {
             // Formato Novo
             $beforeList = $data['before'] ?? [];
             $afterList = $data['after'] ?? [];
        } else if (is_array($data)) {
             // Formato Legado (posicional)
             if (count($data) >= 1) $beforeList[] = $data[0];
             if (count($data) >= 2) $afterList[] = $data[1];
        }

        // Defaults se vazio
        if (empty($beforeList)) $beforeList[] = 'Olá! Gostaria de fazer um pedido:';
        if (empty($afterList)) $afterList[] = 'Aguardo a confirmação.';

        return [
            'before' => $beforeList,
            'after' => $afterList
        ];
    }

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
