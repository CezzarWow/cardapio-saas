<?php
// public/index.php

// 1. Configurações de Erro e Sessão
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Security: Session Hardening
ini_set('session.cookie_httponly', 1); // Prevent JS access
ini_set('session.use_only_cookies', 1); // Prevent ID in URL
ini_set('session.cookie_samesite', 'Lax'); // Prevent CSRF
ini_set('session.gc_maxlifetime', 86400); // 1 day
ini_set('session.use_strict_mode', 1); // Prevent Session Fixation

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1); // Only send over HTTPS
}


session_start();
date_default_timezone_set('America/Sao_Paulo');

// Define a URL base dinamicamente
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = str_replace('\\', '/', $scriptName);
define('BASE_URL', rtrim($baseUrl, '/'));
define('APP_VERSION', '1.1.5'); // Cache-buster: incremente ao atualizar JS/CSS

require '../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\RestaurantController;
use App\Controllers\Admin\AutologinController;
use App\Controllers\Admin\PanelController; // <--- Vamos criar esse cara já já
use App\Core\Router;

// 2. Container e Dependências
$container = require __DIR__ . '/../app/Config/dependencies.php';
Router::setContainer($container);

// ADD GLOBAL MIDDLEWARE (Security)
// 1. Rate Limiting (Block abuse first)
Router::addGlobalMiddleware(\App\Middleware\ThrottleMiddleware::class);
// 2. Sanitization (Clean Input)
Router::addGlobalMiddleware(\App\Middleware\RequestSanitizerMiddleware::class);
// 2. CSRF (Validate Token)
Router::addGlobalMiddleware(\App\Middleware\CsrfMiddleware::class);

$url = $_SERVER['REQUEST_URI'];
$url_clean = parse_url($url, PHP_URL_PATH);
$path = str_replace('/cardapio-saas/public', '', $url_clean);

// ============================================================
// GRUPO 1: Rotas de API (migradas para Router)
// ============================================================
Router::add('/api/order/create', \App\Controllers\Api\OrderApiController::class, 'create');

// --- FLOWS ISOLADOS (Nova Arquitetura) ---
Router::add('/api/v1/balcao/venda', \App\Controllers\Api\BalcaoController::class, 'store');
Router::add('/api/v1/mesa/abrir', \App\Controllers\Api\MesaController::class, 'open');
Router::add('/api/v1/mesa/itens', \App\Controllers\Api\MesaController::class, 'addItems');
Router::add('/api/v1/mesa/fechar', \App\Controllers\Api\MesaController::class, 'close');
Router::add('/api/v1/comanda/abrir', \App\Controllers\Api\ComandaController::class, 'open');
Router::add('/api/v1/comanda/itens', \App\Controllers\Api\ComandaController::class, 'addItems');
Router::add('/api/v1/comanda/fechar', \App\Controllers\Api\ComandaController::class, 'close');
Router::add('/api/v1/delivery/criar', \App\Controllers\Api\DeliveryController::class, 'create');
Router::add('/api/v1/delivery/status', \App\Controllers\Api\DeliveryController::class, 'updateStatus');

// ============================================================
// GRUPO 2: Rotas Admin Geral (migradas para Router)
// ============================================================
Router::add('/admin', DashboardController::class, 'index');
Router::add('/admin/restaurantes/novo', RestaurantController::class, 'create');
Router::add('/admin/restaurantes/salvar', RestaurantController::class, 'store');
Router::add('/admin/restaurantes/editar', RestaurantController::class, 'edit');
Router::add('/admin/restaurantes/atualizar', RestaurantController::class, 'update');
Router::add('/admin/restaurantes/deletar', RestaurantController::class, 'delete');
Router::add('/admin/restaurantes/status', RestaurantController::class, 'toggleStatus');
Router::add('/admin/autologin', AutologinController::class, 'login');

// ============================================================
// GRUPO 3: Painel/PDV → REDIRECT TO SPA
// ============================================================
// Legacy routes redirect to SPA shell
Router::add('/admin/loja/painel', \App\Controllers\Admin\RedirectController::class, 'toSpaBalcao');
Router::add('/admin/loja/pdv', \App\Controllers\Admin\RedirectController::class, 'toSpaBalcao');
Router::add('/admin/loja/mesas', \App\Controllers\Admin\RedirectController::class, 'toSpaMesas');
Router::add('/admin/loja/pdv/cancelar-edicao', \App\Controllers\Admin\PdvController::class, 'cancelEdit');

// ============================================================
// GRUPO 4: Delivery → REDIRECT TO SPA (except API endpoints)
// ============================================================
Router::add('/admin/loja/delivery', \App\Controllers\Admin\RedirectController::class, 'toSpaDelivery');
Router::add('/admin/loja/delivery/status', \App\Controllers\Admin\DeliveryController::class, 'updateStatus');
Router::add('/admin/loja/delivery/list', \App\Controllers\Admin\DeliveryController::class, 'list');
Router::add('/admin/loja/delivery/details', \App\Controllers\Admin\DeliveryController::class, 'getOrderDetails');
Router::add('/admin/loja/delivery/history', \App\Controllers\Admin\DeliveryController::class, 'history');
Router::add('/admin/loja/delivery/send-to-table', \App\Controllers\Admin\DeliveryController::class, 'sendToTable');

// ============================================================
// GRUPO 5: Cardápio Admin → REDIRECT TO SPA (except form endpoints)
// ============================================================
Router::add('/admin/loja/cardapio', \App\Controllers\Admin\RedirectController::class, 'toSpaCardapio');
Router::add('/admin/loja/cardapio/salvar', \App\Controllers\Admin\CardapioController::class, 'update');
Router::add('/admin/loja/cardapio/combo/novo', \App\Controllers\Admin\CardapioController::class, 'comboForm');
Router::add('/admin/loja/cardapio/combo/salvar', \App\Controllers\Admin\CardapioController::class, 'storeCombo');
Router::add('/admin/loja/cardapio/combo/editar', \App\Controllers\Admin\CardapioController::class, 'editCombo');
Router::add('/admin/loja/cardapio/combo/atualizar', \App\Controllers\Admin\CardapioController::class, 'updateCombo');
Router::add('/admin/loja/cardapio/combo/deletar', \App\Controllers\Admin\CardapioController::class, 'deleteCombo');
Router::add('/admin/loja/cardapio/combo/status', \App\Controllers\Admin\CardapioController::class, 'toggleComboStatus');
Router::add('/admin/loja/cardapio/produto/promocao', \App\Controllers\Admin\CardapioController::class, 'setProductPromotion');
Router::add('/admin/loja/cardapio/produto/promocao/toggle', \App\Controllers\Admin\CardapioController::class, 'toggleProductPromotion');
Router::add('/admin/loja/cardapio/produto/promocao/remover', \App\Controllers\Admin\CardapioController::class, 'removeProductPromotion');

// ============================================================
// GRUPO 6: Estoque/Produtos (migradas para Router)
// ============================================================
Router::add('/admin/loja/produtos', \App\Controllers\Admin\ProductController::class, 'index');
Router::add('/admin/loja/produtos/novo', \App\Controllers\Admin\ProductController::class, 'create');
Router::add('/admin/loja/produtos/salvar', \App\Controllers\Admin\ProductController::class, 'store');
Router::add('/admin/loja/produtos/deletar', \App\Controllers\Admin\ProductController::class, 'delete');
Router::add('/admin/loja/produtos/editar', \App\Controllers\Admin\ProductController::class, 'edit');
Router::add('/admin/loja/produtos/atualizar', \App\Controllers\Admin\ProductController::class, 'update');
Router::add('/admin/loja/reposicao', \App\Controllers\Admin\StockRepositionController::class, 'index');
Router::add('/admin/loja/reposicao/ajustar', \App\Controllers\Admin\StockRepositionController::class, 'adjust');
Router::add('/admin/loja/movimentacoes', \App\Controllers\Admin\StockMovementController::class, 'index');

// --- SPA Stock Dashboard (Catálogo Unificado) → REDIRECT TO SPA
Router::add('/admin/loja/catalogo', \App\Controllers\Admin\RedirectController::class, 'toSpaEstoque');
Router::pattern('/^\/admin\/loja\/catalogo\/partial\/([a-z]+)$/', \App\Controllers\Admin\StockDashboardController::class, 'partial');

// --- SPA Admin Shell (Navegação Unificada) ---
Router::add('/admin/loja/spa', \App\Controllers\Admin\AppShellController::class, 'index');
Router::pattern('/^\/admin\/spa\/partial\/([a-z]+)$/', \App\Controllers\Admin\AppShellController::class, 'partial');


// ============================================================
// GRUPO 7: Categorias (migradas para Router)
// ============================================================
Router::add('/admin/loja/categorias', \App\Controllers\Admin\CategoryController::class, 'index');
Router::add('/admin/loja/categorias/salvar', \App\Controllers\Admin\CategoryController::class, 'store');
Router::add('/admin/loja/categorias/editar', \App\Controllers\Admin\CategoryController::class, 'edit');
Router::add('/admin/loja/categorias/atualizar', \App\Controllers\Admin\CategoryController::class, 'update');
Router::add('/admin/loja/categorias/deletar', \App\Controllers\Admin\CategoryController::class, 'delete');
Router::add('/admin/categories', \App\Controllers\Admin\CategoryController::class, 'index');
Router::add('/admin/categories/salvar', \App\Controllers\Admin\CategoryController::class, 'store');
Router::add('/admin/categories/deletar', \App\Controllers\Admin\CategoryController::class, 'delete');

// ============================================================
// GRUPO 8: Adicionais (migradas para Router)
// ============================================================
Router::add('/admin/loja/adicionais', \App\Controllers\Admin\AdditionalController::class, 'index');
Router::add('/admin/loja/adicionais/grupo/salvar', \App\Controllers\Admin\AdditionalController::class, 'storeGroup');
Router::add('/admin/loja/adicionais/grupo/deletar', \App\Controllers\Admin\AdditionalController::class, 'deleteGroup');
Router::add('/admin/loja/adicionais/item/salvar-modal', \App\Controllers\Admin\AdditionalController::class, 'storeItemWithGroups');
Router::add('/admin/loja/adicionais/item/atualizar-modal', \App\Controllers\Admin\AdditionalController::class, 'updateItemWithGroups');
Router::add('/admin/loja/adicionais/get-item-data', \App\Controllers\Admin\AdditionalController::class, 'getItemData');
Router::add('/admin/loja/adicionais/get-product-extras', \App\Controllers\Admin\AdditionalController::class, 'getProductExtras');
Router::add('/admin/loja/adicionais/item/deletar', \App\Controllers\Admin\AdditionalController::class, 'deleteItem');
Router::add('/admin/loja/adicionais/vincular', \App\Controllers\Admin\AdditionalController::class, 'linkItem');
Router::add('/admin/loja/adicionais/desvincular', \App\Controllers\Admin\AdditionalController::class, 'unlinkItem');
Router::add('/admin/loja/adicionais/vincular-multiplos', \App\Controllers\Admin\AdditionalController::class, 'linkMultipleItems');
Router::add('/admin/loja/adicionais/vincular-categoria', \App\Controllers\Admin\AdditionalController::class, 'linkCategory');
Router::add('/admin/loja/adicionais/get-linked-categories', \App\Controllers\Admin\AdditionalController::class, 'getLinkedCategories');

// ============================================================
// GRUPO 9: Caixa/Financeiro → REDIRECT TO SPA (except action endpoints)
// ============================================================
Router::add('/admin/loja/caixa', \App\Controllers\Admin\RedirectController::class, 'toSpaCaixa');
Router::add('/admin/loja/caixa/abrir', \App\Controllers\Admin\CashierController::class, 'open');
Router::add('/admin/loja/caixa/fechar', \App\Controllers\Admin\CashierController::class, 'close');
Router::add('/admin/loja/caixa/movimentar', \App\Controllers\Admin\CashierController::class, 'addMovement');
Router::add('/admin/loja/caixa/estornar-pdv', \App\Controllers\Admin\CashierController::class, 'reverseToPdv');
Router::add('/admin/loja/caixa/remover', \App\Controllers\Admin\CashierController::class, 'removeMovement');
Router::add('/admin/loja/caixa/estornar-mesa', \App\Controllers\Admin\CashierController::class, 'reverseToTable');
Router::add('/admin/loja/config', \App\Controllers\Admin\ConfigController::class, 'index');
Router::add('/admin/loja/config/salvar', \App\Controllers\Admin\ConfigController::class, 'update');
Router::add('/admin/loja/configuracoes-gerais', \App\Controllers\Admin\ConfigGeraisController::class, 'index');

// ============================================================
// GRUPO 10: Pedidos/Vendas (migradas para Router)
// ============================================================
Router::add('/admin/loja/venda/finalizar', \App\Controllers\Admin\OrderController::class, 'store');
Router::add('/admin/loja/mesa/fechar', \App\Controllers\Admin\OrderController::class, 'closeTable');
Router::add('/admin/loja/venda/fechar-comanda', \App\Controllers\Admin\OrderController::class, 'closeCommand');
Router::add('/admin/loja/venda/remover-item', \App\Controllers\Admin\OrderController::class, 'removeItem');
Router::add('/admin/loja/mesa/cancelar', \App\Controllers\Admin\OrderController::class, 'cancelTableOrder');
Router::add('/admin/loja/pedidos/entregar', \App\Controllers\Admin\OrderController::class, 'deliverOrder');
Router::add('/admin/loja/pedidos/cancelar', \App\Controllers\Admin\OrderController::class, 'cancelOrder');
Router::add('/admin/loja/pedido-pago/incluir', \App\Controllers\Admin\OrderController::class, 'includePaidOrderItems');
Router::add('/admin/loja/mesas/deletar', \App\Controllers\Admin\TableController::class, 'deleteByNumber');
Router::add('/admin/loja/mesas/buscar', \App\Controllers\Admin\TableController::class, 'search');
Router::add('/admin/loja/mesas/salvar', \App\Controllers\Admin\TableController::class, 'store');
Router::add('/admin/loja/vendas/itens', \App\Controllers\Admin\SalesController::class, 'getItems');
Router::add('/admin/loja/clientes/buscar', \App\Controllers\Admin\ClientController::class, 'search');
Router::add('/admin/loja/clientes/salvar', \App\Controllers\Admin\ClientController::class, 'store');
Router::add('/admin/loja/clientes/detalhes', \App\Controllers\Admin\ClientController::class, 'details');
Router::add('/admin/loja/vendas', \App\Controllers\Admin\SalesController::class, 'index');
Router::add('/admin/loja/vendas/cancelar', \App\Controllers\Admin\SalesController::class, 'cancel');
Router::add('/admin/loja/vendas/reabrir', \App\Controllers\Admin\SalesController::class, 'reactivateTable');

// ============================================================
// GRUPO 11: Rotas Dinâmicas (Regex) - Cardápio Público
// ============================================================
Router::pattern('/^\\/cardapio\\/([a-zA-Z0-9_-]+)$/', \App\Controllers\CardapioPublicoController::class, 'showBySlug');
Router::pattern('/^\\/c\\/(\\d+)$/', \App\Controllers\CardapioPublicoController::class, 'show');

// ============================================================
// DEFAULT: Handler para rotas não encontradas
// ============================================================
Router::setDefault(function($path) {
    // Se for a raiz ou vazio, vai pro admin
    if ($path == '/' || $path == '') {
        header('Location: admin');
        exit;
    }
    
    // Tenta carregar o cardápio pelo slug
    $slug = ltrim($path, '/');
    if ($slug) {
        global $container; // Fallback access to container since we are in a closure in global scope
        $controller = $container->get(\App\Controllers\CardapioPublicoController::class);
        $controller->showBySlug($slug);
    }
});

// Tenta despachar pelo Router
Router::dispatch($path);