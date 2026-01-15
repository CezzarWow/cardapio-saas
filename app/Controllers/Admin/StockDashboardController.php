<?php

namespace App\Controllers\Admin;

use App\Core\View;
use App\Services\Product\ProductService;
use App\Services\CategoryService;
use App\Services\Additional\AdditionalService;
use App\Services\Stock\StockService;
use App\Repositories\AdditionalCategoryRepository;

/**
 * StockDashboardController - SPA Dashboard para Catálogo/Estoque
 * 
 * Orquestra a navegação AJAX entre as abas:
 * - Produtos
 * - Categorias
 * - Adicionais
 * - Reposição
 * - Movimentações
 */
class StockDashboardController extends BaseController
{
    private ProductService $productService;
    private CategoryService $categoryService;
    private AdditionalService $additionalService;
    private StockService $stockService;
    private AdditionalCategoryRepository $catRepo;

    public function __construct(
        ProductService $productService,
        CategoryService $categoryService,
        AdditionalService $additionalService,
        StockService $stockService,
        AdditionalCategoryRepository $catRepo
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->additionalService = $additionalService;
        $this->stockService = $stockService;
        $this->catRepo = $catRepo;
    }

    /**
     * Página principal do dashboard (com layout completo)
     */
    public function index(): void
    {
        $rid = $this->getRestaurantId();
        
        // Aba inicial a carregar (via hash ou default)
        $initialTab = $this->getParam('tab', 'produtos');
        
        // Dados mínimos para o layout (tabs badges, etc.)
        $totalProducts = count($this->productService->getProducts($rid));
        $totalCategories = count($this->categoryService->list($rid));
        
        View::renderFromScope('admin/stock/dashboard', get_defined_vars());
    }

    /**
     * Endpoint AJAX para carregar conteúdo parcial
     */
    public function partial(string $tab): void
    {
        $rid = $this->getRestaurantId();
        
        // Header para indicar que é conteúdo parcial
        header('X-Partial-Content: true');
        
        switch ($tab) {
            case 'produtos':
                $this->renderProductsPartial($rid);
                break;
            case 'categorias':
                $this->renderCategoriesPartial($rid);
                break;
            case 'adicionais':
                $this->renderAdditionalsPartial($rid);
                break;
            case 'reposicao':
                $this->renderRepositionPartial($rid);
                break;
            case 'movimentacoes':
                $this->renderMovementsPartial($rid);
                break;
            default:
                http_response_code(404);
                echo '<div class="error-message">Aba não encontrada</div>';
        }
    }

    // =========================================================================
    // PARTIALS RENDERERS
    // =========================================================================

    private function renderProductsPartial(int $rid): void
    {
        $rawProducts = $this->productService->getProducts($rid);
        $categories = $this->productService->getCategories($rid);
        
        // Preparação do ViewModel (lógica de apresentação)
        $stockCriticalLimit = 5;
        $totalProducts = count($rawProducts);
        $criticalStockCount = 0;

        $products = array_map(function ($prod) use ($stockCriticalLimit, &$criticalStockCount) {
            $stock = intval($prod['stock']);
            $isNegative = $stock < 0;
            $isCritical = $stock <= $stockCriticalLimit;

            if ($isCritical) {
                $criticalStockCount++;
            }

            $stockClass = 'stock-product-card-stock--ok';
            if ($isNegative) {
                $stockClass = 'stock-product-card-stock--danger';
            } elseif ($isCritical) {
                $stockClass = 'stock-product-card-stock--warning';
            }

            $icon = $prod['icon'] ?? '📦';
            $isLucideIcon = (preg_match('/^[a-z-]+$/', $icon) && strlen($icon) > 4);

            return array_merge($prod, [
                'stock_int' => $stock,
                'is_critical' => $isCritical,
                'is_negative' => $isNegative,
                'stock_class' => $stockClass,
                'formatted_price' => number_format($prod['price'], 2, ',', '.'),
                'is_lucide_icon' => $isLucideIcon,
                'display_icon' => $icon
            ]);
        }, $rawProducts);

        View::renderFromScope('admin/stock/partials/_products', get_defined_vars());
    }

    private function renderCategoriesPartial(int $rid): void
    {
        $categories = $this->categoryService->list($rid);
        
        // Separar categorias de sistema das normais
        $systemCategories = array_filter($categories, fn($c) => in_array($c['category_type'] ?? 'default', ['featured', 'combos']));
        $normalCategories = array_filter($categories, fn($c) => !in_array($c['category_type'] ?? 'default', ['featured', 'combos']));
        $sortedCategories = array_merge($systemCategories, $normalCategories);
        
        $totalCategories = count($categories);

        View::renderFromScope('admin/stock/partials/_categories', get_defined_vars());
    }

    private function renderAdditionalsPartial(int $rid): void
    {
        $groups = $this->additionalService->getAllGroupsWithItems($rid);
        $allItems = $this->additionalService->getAllItems($rid);
        
        $totalGroups = count($groups);
        $totalItems = count($allItems);
        $categories = $this->catRepo->findAllCategories($rid);

        View::renderFromScope('admin/stock/partials/_additionals', get_defined_vars());
    }

    private function renderRepositionPartial(int $rid): void
    {
        $products = $this->stockService->getProducts($rid);
        $categories = $this->stockService->getCategories($rid);
        
        // Contadores
        $STOCK_CRITICAL_LIMIT = 5;
        $totalProducts = count($products);
        $criticalCount = 0;
        $negativeCount = 0;
        
        foreach ($products as $p) {
            $s = intval($p['stock']);
            if ($s < 0) {
                $negativeCount++;
            } elseif ($s <= $STOCK_CRITICAL_LIMIT) {
                $criticalCount++;
            }
        }

        View::renderFromScope('admin/stock/partials/_reposition', get_defined_vars());
    }

    private function renderMovementsPartial(int $rid): void
    {
        // Filtros
        $filters = [
            'product' => $_GET['product'] ?? null,
            'category' => $_GET['category'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null
        ];

        $movements = $this->stockService->getMovements($rid, $filters);
        $products = $this->stockService->getProducts($rid);
        $categories = $this->stockService->getCategories($rid);
        $stats = $this->stockService->getMovementStats($movements);
        
        $productFilter = $_GET['product'] ?? '';
        $categoryFilter = $_GET['category'] ?? '';
        $totalMovements = count($movements);
        $entradas = $stats['entradas'] ?? 0;
        $saidas = $stats['saidas'] ?? 0;

        View::renderFromScope('admin/stock/partials/_movements', get_defined_vars());
    }
}
