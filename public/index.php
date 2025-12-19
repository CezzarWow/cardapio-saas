<?php
// --- LIGAR VISUALIZAÇÃO DE ERROS ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// -----------------------------------

require '../vendor/autoload.php';

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\RestaurantController;

$url = $_SERVER['REQUEST_URI'];
// 1. Limpa os parâmetros GET (tudo depois do ?) para não confundir o switch
$url_clean = parse_url($url, PHP_URL_PATH);

// 2. Remove a pasta base para sobrar só a rota limpa
$path = str_replace('/cardapio-saas/public', '', $url_clean);

switch ($path) {
    case '/admin':
        $controller = new DashboardController();
        $controller->index();
        break;

    case '/admin/restaurantes/novo':
        $controller = new RestaurantController();
        $controller->create();
        break;

    case '/admin/restaurantes/salvar':
        $controller = new RestaurantController();
        $controller->store();
        break;

    case '/admin/restaurantes/editar':
        $controller = new RestaurantController();
        $controller->edit();
        break;

    case '/admin/restaurantes/status':
        $controller = new RestaurantController();
        $controller->toggleStatus();
        break;
    
    // Rota que PROCESSA a edição (O botão Salvar manda pra cá)
    case '/admin/restaurantes/atualizar':
        $controller = new RestaurantController();
        $controller->update();
        break;

    // Rota de EXCLUSÃO
    case '/admin/restaurantes/deletar':
        $controller = new RestaurantController();
        $controller->delete();
        break;

    default:
        if ($path == '/' || $path == '') {
            echo "<h1>Página Inicial</h1> <a href='admin'>Ir para Admin</a>";
        } else {
            echo "<h1>Erro 404</h1><p>Página não encontrada: $path</p>";
        }
        break;
}