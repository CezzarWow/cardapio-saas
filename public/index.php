<?php
// public/index.php

// 1. Configurações de Erro e Sessão
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
date_default_timezone_set('America/Sao_Paulo');

// Define a URL base dinamicamente
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = str_replace('\\', '/', $scriptName);
define('BASE_URL', rtrim($baseUrl, '/'));

require '../vendor/autoload.php';

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\RestaurantController;
use App\Controllers\Admin\AutologinController;
use App\Controllers\Admin\PanelController; // <--- Vamos criar esse cara já já

$url = $_SERVER['REQUEST_URI'];
$url_clean = parse_url($url, PHP_URL_PATH);
$path = str_replace('/cardapio-saas/public', '', $url_clean);

switch ($path) {
    // --- ROTAS DE API (Cardápio Público) ---
    case '/api/order/create':
        require __DIR__ . '/../app/Controllers/Api/OrderApiController.php';
        (new \App\Controllers\Api\OrderApiController())->create();
        break;

    // --- ROTAS DO ADMIN GERAL (Dono do SaaS) ---
    case '/admin':
        (new DashboardController())->index();
        break;

    case '/admin/restaurantes/novo':
        (new RestaurantController())->create();
        break;

    case '/admin/restaurantes/salvar':
        (new RestaurantController())->store();
        break;

    case '/admin/restaurantes/editar':
        (new RestaurantController())->edit();
        break;

    case '/admin/restaurantes/atualizar':
        (new RestaurantController())->update();
        break;

    case '/admin/restaurantes/deletar': // <--- CORREÇÃO APLICADA AQUI
        (new RestaurantController())->delete();
        break;

    case '/admin/restaurantes/status':
        (new RestaurantController())->toggleStatus();
        break;
    
    case '/admin/autologin':
        (new AutologinController())->login();
        break;

    // --- ROTAS DO PAINEL DO RESTAURANTE (Onde o cliente mexe) ---
    // --- ROTAS DO PAINEL DO RESTAURANTE ---
    case '/admin/loja/painel': 
    case '/admin/loja/pdv': // Adicionei essa rota pois seu botão na sidebar aponta para 'pdv'
        require __DIR__ . '/../app/Controllers/Admin/ProductController.php';
        (new \App\Controllers\Admin\ProductController())->index();
        break;

    // --- ROTA DE MESAS ---
    case '/admin/loja/mesas':
        require __DIR__ . '/../app/Controllers/Admin/TableController.php';
        (new \App\Controllers\Admin\TableController())->index();
        break;

    case '/admin/loja/delivery':
        require __DIR__ . '/../app/Controllers/Admin/DeliveryController.php';
        (new \App\Controllers\Admin\DeliveryController())->index();
        break;

    case '/admin/loja/delivery/status':
        require __DIR__ . '/../app/Controllers/Admin/DeliveryController.php';
        (new \App\Controllers\Admin\DeliveryController())->updateStatus();
        break;

    case '/admin/loja/delivery/list':
        require __DIR__ . '/../app/Controllers/Admin/DeliveryController.php';
        (new \App\Controllers\Admin\DeliveryController())->list();
        break;

    case '/admin/loja/delivery/details':
        require __DIR__ . '/../app/Controllers/Admin/DeliveryController.php';
        (new \App\Controllers\Admin\DeliveryController())->getOrderDetails();
        break;

    case '/admin/loja/delivery/history':
        require __DIR__ . '/../app/Controllers/Admin/DeliveryController.php';
        (new \App\Controllers\Admin\DeliveryController())->history();
        break;

    case '/admin/loja/delivery/send-to-table':
        require __DIR__ . '/../app/Controllers/Admin/DeliveryController.php';
        (new \App\Controllers\Admin\DeliveryController())->sendToTable();
        break;

    // --- CARDÁPIO WEB ---
    case '/admin/loja/cardapio':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->index();
        break;

    case '/admin/loja/cardapio/salvar':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->update();
        break;

    // --- COMBOS (ETAPA 3) ---
    case '/admin/loja/cardapio/combo/novo':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->comboForm();
        break;

    case '/admin/loja/cardapio/combo/salvar':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->storeCombo();
        break;

    case '/admin/loja/cardapio/combo/editar':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->editCombo();
        break;

    case '/admin/loja/cardapio/combo/atualizar':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->updateCombo();
        break;

    case '/admin/loja/cardapio/combo/deletar':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->deleteCombo();
        break;

    case '/admin/loja/cardapio/combo/status':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->toggleComboStatus();
        break;

    // --- GESTÃO DE ESTOQUE (PRODUTOS) ---
    case '/admin/loja/produtos':
        require __DIR__ . '/../app/Controllers/Admin/StockController.php';
        (new \App\Controllers\Admin\StockController())->index();
        break;
    
    // [CATEGORIAS] - Layout Moderno
    case '/admin/loja/categorias':
        require __DIR__ . '/../app/Controllers/Admin/CategoryController.php';
        (new \App\Controllers\Admin\CategoryController())->index();
        break;

    case '/admin/loja/categorias/salvar':
        require __DIR__ . '/../app/Controllers/Admin/CategoryController.php';
        (new \App\Controllers\Admin\CategoryController())->store();
        break;

    case '/admin/loja/categorias/editar':
        require __DIR__ . '/../app/Controllers/Admin/CategoryController.php';
        (new \App\Controllers\Admin\CategoryController())->edit();
        break;

    case '/admin/loja/categorias/atualizar':
        require __DIR__ . '/../app/Controllers/Admin/CategoryController.php';
        (new \App\Controllers\Admin\CategoryController())->update();
        break;

    case '/admin/loja/categorias/deletar':
        require __DIR__ . '/../app/Controllers/Admin/CategoryController.php';
        (new \App\Controllers\Admin\CategoryController())->delete();
        break;

    case '/admin/loja/produtos/novo':
        require __DIR__ . '/../app/Controllers/Admin/StockController.php';
        (new \App\Controllers\Admin\StockController())->create();
        break;

    case '/admin/loja/produtos/salvar':
        require __DIR__ . '/../app/Controllers/Admin/StockController.php';
        (new \App\Controllers\Admin\StockController())->store();
        break;

    case '/admin/loja/produtos/deletar':
        require __DIR__ . '/../app/Controllers/Admin/StockController.php';
        (new \App\Controllers\Admin\StockController())->delete();
        break;

    // [FASE 1] Editar e Atualizar Produto
    case '/admin/loja/produtos/editar':
        require __DIR__ . '/../app/Controllers/Admin/StockController.php';
        (new \App\Controllers\Admin\StockController())->edit();
        break;

    case '/admin/loja/produtos/atualizar':
        require __DIR__ . '/../app/Controllers/Admin/StockController.php';
        (new \App\Controllers\Admin\StockController())->update();
        break;

    // [FASE 3] Reposição de Estoque
    case '/admin/loja/reposicao':
        require __DIR__ . '/../app/Controllers/Admin/StockRepositionController.php';
        (new \App\Controllers\Admin\StockRepositionController())->index();
        break;

    case '/admin/loja/reposicao/ajustar':
        require __DIR__ . '/../app/Controllers/Admin/StockRepositionController.php';
        (new \App\Controllers\Admin\StockRepositionController())->adjust();
        break;

    // [FASE 4] Movimentações de Estoque (Histórico)
    case '/admin/loja/movimentacoes':
        require __DIR__ . '/../app/Controllers/Admin/StockMovementController.php';
        (new \App\Controllers\Admin\StockMovementController())->index();
        break;

    // [FASE 5.1] Adicionais - Arquitetura Global
    case '/admin/loja/adicionais':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->index();
        break;

    case '/admin/loja/adicionais/itens':
        // Redireciona para a página principal que agora contém as abas
        header('Location: ' . BASE_URL . '/admin/loja/adicionais');
        exit;
        break;

    case '/admin/loja/adicionais/grupo/salvar':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->storeGroup();
        break;

    case '/admin/loja/adicionais/grupo/deletar':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->deleteGroup();
        break;





    case '/admin/loja/adicionais/item/salvar-modal':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->storeItemWithGroups();
        break;

    case '/admin/loja/adicionais/item/atualizar-modal':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->updateItemWithGroups();
        break;

    case '/admin/loja/adicionais/get-item-data':
        require_once __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->getItemData();
        break;

    case '/admin/loja/adicionais/get-product-extras':
        require_once __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->getProductExtras();
        break;





    case '/admin/loja/adicionais/item/deletar':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->deleteItem();
        break;

    case '/admin/loja/adicionais/vincular':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->linkItem();
        break;

    case '/admin/loja/adicionais/desvincular':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->unlinkItem();
        break;

    case '/admin/loja/adicionais/vincular-multiplos':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->linkMultipleItems();
        break;

    case '/admin/loja/adicionais/vincular-categoria':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->linkCategory();
        break;

    // AJAX: Recuperar categorias vinculadas (para o modal)
    case '/admin/loja/adicionais/get-linked-categories':
        require __DIR__ . '/../app/Controllers/Admin/AdditionalController.php';
        (new \App\Controllers\Admin\AdditionalController())->getLinkedCategories();
        break;

    // --- FINANCEIRO E CAIXA ---
    case '/admin/loja/caixa':
        require __DIR__ . '/../app/Controllers/Admin/CashierController.php';
        (new \App\Controllers\Admin\CashierController())->index();
        break;

    // --- CARDÁPIO (Em construção) ---
    case '/admin/loja/cardapio':
        require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
        (new \App\Controllers\Admin\CardapioController())->index();
        break;

    case '/admin/loja/caixa/abrir':
        require __DIR__ . '/../app/Controllers/Admin/CashierController.php';
        (new \App\Controllers\Admin\CashierController())->open();
        break;

    case '/admin/loja/caixa/fechar':
        require __DIR__ . '/../app/Controllers/Admin/CashierController.php';
        (new \App\Controllers\Admin\CashierController())->close();
        break;

    case '/admin/loja/caixa/movimentar':
        require __DIR__ . '/../app/Controllers/Admin/CashierController.php';
        (new \App\Controllers\Admin\CashierController())->addMovement();
        break;

    case '/admin/loja/pdv/cancelar-edicao':
        require __DIR__ . '/../app/Controllers/Admin/ProductController.php';
        (new \App\Controllers\Admin\ProductController())->cancelEdit();
        break;

    case '/admin/loja/caixa/estornar-pdv':
        require __DIR__ . '/../app/Controllers/Admin/CashierController.php';
        (new \App\Controllers\Admin\CashierController())->reverseToPdv();
        break;

    case '/admin/loja/caixa/remover':
        require __DIR__ . '/../app/Controllers/Admin/CashierController.php';
        (new \App\Controllers\Admin\CashierController())->removeMovement();
        break;

    case '/admin/loja/caixa/estornar-mesa':
        require __DIR__ . '/../app/Controllers/Admin/CashierController.php';
        (new \App\Controllers\Admin\CashierController())->reverseToTable();
        break;

    // --- CONFIGURAÇÕES DA LOJA ---
    case '/admin/loja/config':
        require __DIR__ . '/../app/Controllers/Admin/ConfigController.php';
        (new \App\Controllers\Admin\ConfigController())->index();
        break;

    case '/admin/loja/config/salvar':
        require __DIR__ . '/../app/Controllers/Admin/ConfigController.php';
        (new \App\Controllers\Admin\ConfigController())->update();
        break;

    case '/admin/loja/configuracoes-gerais':
        require __DIR__ . '/../app/Controllers/Admin/ConfigGeraisController.php';
        (new \App\Controllers\Admin\ConfigGeraisController())->index();
        break;

    // --- ROTAS DE AÇÃO (AJAX) ---
    case '/admin/loja/venda/finalizar':
        require __DIR__ . '/../app/Controllers/Admin/OrderController.php';
        (new \App\Controllers\Admin\OrderController())->store();
        break;

    case '/admin/loja/mesa/fechar':
        require __DIR__ . '/../app/Controllers/Admin/OrderController.php';
        (new \App\Controllers\Admin\OrderController())->closeTable();
        break;

    case '/admin/loja/venda/fechar-comanda':
        require __DIR__ . '/../app/Controllers/Admin/OrderController.php';
        (new \App\Controllers\Admin\OrderController())->closeCommand();
        break;

    case '/admin/loja/venda/remover-item':
        require __DIR__ . '/../app/Controllers/Admin/OrderController.php';
        (new \App\Controllers\Admin\OrderController())->removeItem();
        break;

    case '/admin/loja/mesa/cancelar':
        require __DIR__ . '/../app/Controllers/Admin/OrderController.php';
        (new \App\Controllers\Admin\OrderController())->cancelTableOrder();
        break;

    case '/admin/loja/pedidos/entregar':
        require __DIR__ . '/../app/Controllers/Admin/OrderController.php';
        (new \App\Controllers\Admin\OrderController())->deliverOrder();
        break;

    case '/admin/loja/pedidos/cancelar':
        require __DIR__ . '/../app/Controllers/Admin/OrderController.php';
        (new \App\Controllers\Admin\OrderController())->cancelOrder();
        break;

    case '/admin/loja/pedido-pago/incluir':
        require __DIR__ . '/../app/Controllers/Admin/OrderController.php';
        (new \App\Controllers\Admin\OrderController())->includePaidOrderItems();
        break;

    case '/admin/loja/mesas/deletar':
        require __DIR__ . '/../app/Controllers/Admin/TableController.php';
        (new \App\Controllers\Admin\TableController())->deleteByNumber();
        break;

    case '/admin/loja/mesas/buscar':
        require __DIR__ . '/../app/Controllers/Admin/TableController.php';
        (new \App\Controllers\Admin\TableController())->search();
        break;

    case '/admin/loja/mesas/salvar':
        require __DIR__ . '/../app/Controllers/Admin/TableController.php';
        (new \App\Controllers\Admin\TableController())->store();
        break;

    // 3. Obter Itens (AJAX)
    case '/admin/loja/vendas/itens':
        require __DIR__ . '/../app/Controllers/Admin/SalesController.php';
        (new \App\Controllers\Admin\SalesController())->getItems();
        break;

    // --- GESTÃO DE CLIENTES (AJAX) ---
    case '/admin/loja/clientes/buscar':
        require __DIR__ . '/../app/Controllers/Admin/ClientController.php';
        (new \App\Controllers\Admin\ClientController())->search();
        break;

    case '/admin/loja/clientes/salvar':
        require __DIR__ . '/../app/Controllers/Admin/ClientController.php';
        (new \App\Controllers\Admin\ClientController())->store();
        break;

    case '/admin/loja/clientes/detalhes':
        require __DIR__ . '/../app/Controllers/Admin/ClientController.php';
        (new \App\Controllers\Admin\ClientController())->details();
        break;

    // --- GESTÃO DE CATEGORIAS ---
    
    // 1. Listar
    case '/admin/categories':
        require __DIR__ . '/../app/Controllers/Admin/CategoryController.php';
        (new \App\Controllers\Admin\CategoryController())->index();
        break;

    // 2. Salvar (POST do formulário)
    case '/admin/categories/salvar':
        require __DIR__ . '/../app/Controllers/Admin/CategoryController.php';
        (new \App\Controllers\Admin\CategoryController())->store();
        break;

    // 3. Deletar
    case '/admin/categories/deletar':
        require __DIR__ . '/../app/Controllers/Admin/CategoryController.php';
        (new \App\Controllers\Admin\CategoryController())->delete();
        break;

    // --- ROTA DE RELATÓRIO DE VENDAS ---
    case '/admin/loja/vendas':
        require __DIR__ . '/../app/Controllers/Admin/SalesController.php';
        (new \App\Controllers\Admin\SalesController())->index();
        break;

    // --- ROTA PARA PEGAR ITENS DA VENDA (AJAX) ---
    case '/admin/loja/vendas/itens':
        require __DIR__ . '/../app/Controllers/Admin/SalesController.php';
        (new \App\Controllers\Admin\SalesController())->getItems();
        break;

    // --- AÇÕES DE VENDAS ---
    case '/admin/loja/vendas/cancelar':
        require __DIR__ . '/../app/Controllers/Admin/SalesController.php';
        (new \App\Controllers\Admin\SalesController())->cancel();
        break;

    case '/admin/loja/vendas/reabrir':
        require __DIR__ . '/../app/Controllers/Admin/SalesController.php';
        (new \App\Controllers\Admin\SalesController())->reactivateTable();
        break;

    // --- ROTA PÚBLICA: CARDÁPIO POR SLUG (/cardapio/{slug}) ---
    case (preg_match('/^\\/cardapio\\/([a-zA-Z0-9_-]+)$/', $path, $mSlug) ? true : false):
        require __DIR__ . '/../app/Controllers/CardapioPublicoController.php';
        (new \App\Controllers\CardapioPublicoController())->showBySlug($mSlug[1]);
        break;

    // --- ROTA PÚBLICA: CARDÁPIO POR ID (para acesso pelo celular) ---
    // Acesse: /c/1 para ver cardápio do restaurante ID 1
    case (preg_match('/^\/c\/(\d+)$/', $path, $m) ? true : false):
        require __DIR__ . '/../app/Controllers/CardapioPublicoController.php';
        (new \App\Controllers\CardapioPublicoController())->show(intval($m[1]));
        break;

    // --- ROTA PÚBLICA (Cardápio) ---
    default:
        // Se for a raiz ou vazio, vai pro admin
        if ($path == '/' || $path == '') {
            header('Location: admin');
            exit;
        }
        
        // Se não, tenta carregar o cardápio
        require __DIR__ . '/../app/Controllers/MenuController.php';
        $menu = new \App\Controllers\MenuController();
        // Remove a barra inicial do slug (ex: "/pizzaria" vira "pizzaria")
        $slug = ltrim($path, '/');
        if($slug) $menu->index($slug);
        break;
}