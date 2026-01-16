<?php

namespace App\Controllers\Admin;

use App\Core\View;
use App\Services\Product\ProductService;
use App\Services\CategoryService;
use App\Services\Cardapio\CardapioQueryService;
use App\Services\Delivery\DeliveryService;
use App\Services\TableService;
use App\Services\Pdv\PdvService;
use App\Services\RestaurantService;
use App\Services\Cashier\CashierDashboardService;
use App\Repositories\TableRepository;
use App\Repositories\Order\OrderRepository;

/**
 * AppShellController - Controlador Principal do SPA
 */
class AppShellController extends BaseController
{
    private ProductService $productService;
    private CategoryService $categoryService;
    private CardapioQueryService $cardapioQueryService;
    private DeliveryService $deliveryService;
    private TableService $tableService;
    private PdvService $pdvService;
    private RestaurantService $restaurantService;
    private CashierDashboardService $cashierDashboard;
    private TableRepository $tableRepo;
    private OrderRepository $orderRepo;

    public function __construct(
        ProductService $productService,
        CategoryService $categoryService,
        CardapioQueryService $cardapioQueryService,
        DeliveryService $deliveryService,
        TableService $tableService,
        PdvService $pdvService,
        RestaurantService $restaurantService,
        CashierDashboardService $cashierDashboard,
        TableRepository $tableRepo,
        OrderRepository $orderRepo
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->cardapioQueryService = $cardapioQueryService;
        $this->deliveryService = $deliveryService;
        $this->tableService = $tableService;
        $this->pdvService = $pdvService;
        $this->restaurantService = $restaurantService;
        $this->cashierDashboard = $cashierDashboard;
        $this->tableRepo = $tableRepo;
        $this->orderRepo = $orderRepo;
    }

    public function index(): void
    {
        View::render('admin/spa/shell');
    }

    public function partial(string $section): void
    {
        if (!$this->isAjaxRequest()) {
            $this->redirect('/admin/loja/spa');
            return;
        }

        $rid = $this->getRestaurantId();

        switch ($section) {
            case 'balcao': // Agora é o PDV
            case 'pdv':    // Alias
                $this->renderPdvPartial($rid);
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
    // PARTIALS
    // =========================================================================

    private function renderPdvPartial(int $rid): void
    {
        // Lógica migrada de PdvController::index

        // Contexto via Query Params (AJAX)
        // O AdminSPA repassa a query string, então $_GET funciona
        $mesaId = $this->getInt('mesa_id');
        $mesaNumero = $_GET['mesa_numero'] ?? null;
        $orderId = $this->getInt('order_id');

        // Se acessa via order_id, buscar a mesa vinculada (se houver)
        if ($orderId > 0 && $mesaId <= 0) {
            $linkedTable = $this->tableRepo->findByOrderId($orderId);
            if ($linkedTable) {
                $mesaId = (int) $linkedTable['id'];
                $mesaNumero = $linkedTable['number'];
            }
        }

        // Se tem mesa_id mas não tem mesa_numero, buscar o número
        if ($mesaId > 0 && !$mesaNumero) {
            $tableData = $this->tableRepo->findById($mesaId);
            if ($tableData) {
                $mesaNumero = $tableData['number'];
            }
        }

        // Busca dados do contexto
        $context = $this->pdvService->getContextData($rid, $mesaId > 0 ? $mesaId : null, $orderId > 0 ? $orderId : null);

        $contaAberta = $context['contaAberta'];
        $itensJaPedidos = $context['itensJaPedidos'];

        // Variáveis para View (snake_case)
        $mesa_id = $mesaId;
        $mesa_numero = $mesaNumero;

        // 1. Detecta modo edição (Pedido Pago/Retirada)
        $isEditingPaid = isset($_GET['edit_paid']) && $_GET['edit_paid'] == '1';
        $editingOrderId = $orderId;

        $originalPaidTotalFromDB = 0;
        if ($isEditingPaid && $editingOrderId) {
            $orderData = $this->orderRepo->find($editingOrderId);
            if ($orderData) {
                $originalPaidTotalFromDB = floatval($orderData['total'] ?? 0);
            }
        }

        // 2. Carrega Configurações (Taxa de Entrega)
        $settings = $this->restaurantService->getSettings($rid);
        $deliveryFee = floatval($settings['delivery_fee'] ?? 5.0);

        // 3. Flags de Visualização (usadas nas partials internas)
        $showQuickSale = false;
        $showCloseTable = false;
        $showCloseCommand = false;
        $showSaveCommand = false;
        $showIncludePaid = false;

        if ($mesa_id) {
            $showCloseTable = true;
            $showSaveCommand = true;
        } elseif (!empty($contaAberta['id'])) {
            if ($isEditingPaid) {
                $showIncludePaid = true;
            } else {
                $showCloseCommand = true;
                $showSaveCommand = true;
            }
        } else {
            $showQuickSale = true;
            $showSaveCommand = true;
        }

        // 4. Carrinho de Recuperação
        $cartRecovery = ($isEditingPaid && $editingOrderId) ? $itensJaPedidos : [];

        // Carrega Cardápio
        $categories = $this->pdvService->getMenu($rid);

        // Renderiza o partial do PDV
        require __DIR__ . '/../../../views/admin/spa/partials/_pdv.php';
    }

    private function renderMesasPartial(int $rid): void
    {
        $tables = $this->tableService->getAllTables($rid);
        $clientOrders = $this->tableService->getOpenClientOrders($rid);
        require __DIR__ . '/../../../views/admin/spa/partials/_mesas.php';
    }

    private function renderDeliveryPartial(int $rid): void
    {
        $statusFilter = $_GET['status'] ?? null;
        $orders = $this->deliveryService->getOrders($rid, $statusFilter);
        require __DIR__ . '/../../../views/admin/spa/partials/_delivery.php';
    }

    private function renderCardapioPartial(int $rid): void
    {
        $data = $this->cardapioQueryService->getIndexData($rid);
        extract($data);
        require __DIR__ . '/../../../views/admin/spa/partials/_cardapio.php';
    }

    private function renderEstoquePartial(int $rid): void
    {
        $products = $this->productService->getProducts($rid);
        $categories = $this->categoryService->list($rid);
        
        $totalProducts = count($products);
        $totalCategories = count($categories);

        require __DIR__ . '/../../../views/admin/spa/partials/_estoque.php';
    }

    private function renderCaixaPartial(int $rid): void
    {
        // Busca caixa aberto
        $caixa = $this->cashierDashboard->getOpenCashier($rid);
        
        // Se não há caixa aberto, renderiza tela de abertura
        if (!$caixa) {
            $resumo = [];
            $movimentosView = [];
            $dinheiroEmCaixa = 0;
            require __DIR__ . '/../../../views/admin/spa/partials/_caixa.php';
            return;
        }

        // Caixa aberto: busca dados do dashboard
        $resumo = $this->cashierDashboard->calculateSalesSummary($rid, $caixa['opened_at']);
        $movimentos = $this->cashierDashboard->getMovements($caixa['id']);
        
        list($totalSuprimentos, $totalSangrias) = $this->cashierDashboard->sumMovements($movimentos);
        $dinheiroEmCaixa = $this->cashierDashboard->calculateCashInDrawer(
            $caixa['opening_balance'],
            $resumo['dinheiro'],
            $totalSuprimentos,
            $totalSangrias
        );

        // Decorar movimentos para a View (ViewModel)
        $movimentosView = array_map(function ($m) {
            $isSangria = $m['type'] == 'sangria';
            return [
                'id' => $m['id'],
                'type' => $m['type'],
                'description' => $m['description'],
                'amount' => $m['amount'],
                'created_at' => $m['created_at'],
                'order_id' => $m['order_id'],
                'is_sangria' => $isSangria,
                'color_bg' => $isSangria ? '#fee2e2' : '#dcfce7',
                'color_text' => $isSangria ? '#991b1b' : '#166534',
                'icon' => $isSangria ? 'arrow-up-right' : 'arrow-down-left',
                'sign' => $isSangria ? '-' : '+',
                'is_table_reopen' => strpos($m['description'] ?? '', 'Mesa') !== false
            ];
        }, $movimentos);

        require __DIR__ . '/../../../views/admin/spa/partials/_caixa.php';
    }

    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
