<?php

namespace App\Services\Cardapio;

use App\Core\Database;
use App\Repositories\Cardapio\CardapioConfigRepository;
use App\Repositories\Cardapio\BusinessHoursRepository;
use App\Repositories\Cardapio\CategoryRepository;
use App\Repositories\Cardapio\ProductRepository;
use PDO;

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

    public function __construct()
    {
        $this->configRepository = new CardapioConfigRepository();
        $this->hoursRepository = new BusinessHoursRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->productRepository = new ProductRepository();
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
        $restaurantSlug = $this->getRestaurantSlug($restaurantId);

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
        $conn = Database::connect();
        
        // Buscar combos
        $stmtCombos = $conn->prepare("SELECT * FROM combos WHERE restaurant_id = :rid ORDER BY display_order, name");
        $stmtCombos->execute(['rid' => $restaurantId]);
        $combos = $stmtCombos->fetchAll(PDO::FETCH_ASSOC);

        if (empty($combos)) {
            return [];
        }

        // Buscar itens dos combos
        $comboIds = array_column($combos, 'id');
        $inQuery = implode(',', array_fill(0, count($comboIds), '?'));
        
        $stmtItems = $conn->prepare("
            SELECT ci.combo_id, p.name, p.price 
            FROM combo_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.combo_id IN ($inQuery)
        ");
        $stmtItems->execute($comboIds);
        $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar itens por combo
        $itemsByCombo = [];
        foreach ($allItems as $item) {
            $itemsByCombo[$item['combo_id']][] = $item;
        }

        // Processar dados para a view
        foreach ($combos as &$combo) {
            $items = $itemsByCombo[$combo['id']] ?? [];
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
        unset($combo);

        return $combos;
    }

    /**
     * Busca slug do restaurante
     */
    private function getRestaurantSlug(int $restaurantId): string
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT slug FROM restaurants WHERE id = :rid");
        $stmt->execute(['rid' => $restaurantId]);
        
        return $stmt->fetchColumn() ?: (string) $restaurantId;
    }
}
