<?php

namespace App\Controllers\Admin;

use App\Core\View;
use App\Services\Product\ProductService;
use App\Services\CategoryService;
use App\Services\Cardapio\CardapioQueryService;

/**
 * AppShellController - Controlador Principal do SPA
 * 
 * Responsável por:
 * - Renderizar o shell (sidebar + container vazio)
 * - Servir partials das seções via AJAX
 */
class AppShellController extends BaseController
{
    private ProductService $productService;
    private CategoryService $categoryService;
    private CardapioQueryService $cardapioQueryService;

    public function __construct(
        ProductService $productService,
        CategoryService $categoryService,
        CardapioQueryService $cardapioQueryService
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->cardapioQueryService = $cardapioQueryService;
    }

    /**
     * Renderiza o shell principal do SPA
     * O conteúdo é carregado via AJAX pelo AdminSPA.js
     */
    public function index(): void
    {
        View::render('admin/spa/shell');
    }

    /**
     * Serve o partial de uma seção específica
     * Chamado via AJAX: /admin/spa/partial/{section}
     */
    public function partial(string $section): void
    {
        // Valida que é requisição AJAX
        if (!$this->isAjaxRequest()) {
            $this->redirect('/admin/loja/spa');
            return;
        }

        $rid = $this->getRestaurantId();

        switch ($section) {
            case 'balcao':
                $this->renderBalcaoPartial($rid);
                break;
            case 'mesas':
                $this->renderMesasPartial($rid);
                break;
            case 'delivery':
                $this->renderDeliveryPartial($rid);
                break;
            case 'cardapio':
                $this->renderCardapioPartial($rid);
                break;
            case 'estoque':
                $this->renderEstoquePartial($rid);
                break;
            case 'caixa':
                $this->renderCaixaPartial($rid);
                break;
            default:
                http_response_code(404);
                echo '<div class="error">Seção não encontrada</div>';
        }
    }

    // =========================================================================
    // PARTIALS - Serão implementados conforme migramos cada seção
    // =========================================================================

    private function renderBalcaoPartial(int $rid): void
    {
        echo '<div style="padding: 40px; text-align: center; color: #6b7280;">
            <h2>Balcão</h2>
            <p>Esta seção será migrada na Etapa 5</p>
        </div>';
    }

    private function renderMesasPartial(int $rid): void
    {
        echo '<div style="padding: 40px; text-align: center; color: #6b7280;">
            <h2>Mesas</h2>
            <p>Esta seção será migrada na Etapa 4</p>
        </div>';
    }

    private function renderDeliveryPartial(int $rid): void
    {
        echo '<div style="padding: 40px; text-align: center; color: #6b7280;">
            <h2>Delivery</h2>
            <p>Esta seção será migrada na Etapa 3</p>
        </div>';
    }

    private function renderCardapioPartial(int $rid): void
    {
        // Busca dados via CardapioQueryService (mesma lógica do CardapioController::index)
        $data = $this->cardapioQueryService->getIndexData($rid);
        extract($data);

        // Renderiza o partial do Cardápio
        require __DIR__ . '/../../../views/admin/spa/partials/_cardapio.php';
    }

    private function renderEstoquePartial(int $rid): void
    {
        // Dados para o partial (usando métodos existentes + count)
        $products = $this->productService->getProducts($rid);
        $categories = $this->categoryService->list($rid);
        
        $totalProducts = count($products);
        $totalCategories = count($categories);

        // Renderiza o partial do Estoque
        require __DIR__ . '/../../../views/admin/spa/partials/_estoque.php';
    }

    private function renderCaixaPartial(int $rid): void
    {
        echo '<div style="padding: 40px; text-align: center; color: #6b7280;">
            <h2>Caixa</h2>
            <p>Esta seção será migrada na Etapa 6</p>
        </div>';
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
