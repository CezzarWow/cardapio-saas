<?php
// public/index.php

// 1. Configurações de Erro e Sessão
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require '../vendor/autoload.php';

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\RestaurantController;
use App\Controllers\Admin\AutologinController;
use App\Controllers\Admin\PanelController; // <--- Vamos criar esse cara já já

$url = $_SERVER['REQUEST_URI'];
$url_clean = parse_url($url, PHP_URL_PATH);
$path = str_replace('/cardapio-saas/public', '', $url_clean);

switch ($path) {
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

    case '/admin/loja/mesas':
        echo "<h1>Tela de Mesas (Em construção) 🚧</h1>";
        break;

    case '/admin/loja/delivery':
        echo "<h1>Tela de Delivery (Em construção) 🛵</h1>";
        break;

    case '/admin/loja/produtos': // Estoque
        echo "<h1>Tela de Estoque (Em construção) 📦</h1>";
        break;

    case '/admin/loja/vendas':
        echo "<h1>Tela de Vendas (Em construção) 🛍️</h1>";
        break;

    case '/admin/loja/caixa':
        echo "<h1>Tela de Caixa (Em construção) 💰</h1>";
        break;

    case '/admin/loja/config':
        echo "<h1>Tela de Configurações (Em construção) ⚙️</h1>";
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