<?php
// --- LIGAR VISUALIZAÇÃO DE ERROS ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// -----------------------------------

// *** NOVO: Iniciar Sessão para o Autologin funcionar ***
session_start();

require '../vendor/autoload.php';

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\RestaurantController;
use App\Controllers\Admin\AutologinController; // <--- Importante

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

        $controller = new RestaurantController();
        $controller->delete();
        break;

    // --- NOVAS ROTAS ESCALÁVEIS ---

    // 1. Rota que faz o login na loja
    case '/admin/autologin':
        $controller = new AutologinController();
        $controller->login();
        break;

    // 2. Rota do Painel da Loja (Para onde fomos redirecionados)
    // Por enquanto vamos apenas exibir uma mensagem, depois criamos o Controller real
    case '/admin/loja/painel':
        echo "<h1>Bem-vindo à gestão da loja: " . $_SESSION['loja_ativa_nome'] . "</h1>";
        echo "<p>Aqui vamos gerenciar Categorias e Produtos.</p>";
        echo "<a href='../../admin'>Voltar para Admin Geral</a>";
        break;

    default:
        if ($path == '/' || $path == '') {
            echo "<h1>Página Inicial</h1> <a href='admin'>Ir para Admin</a>";
        } else {
            echo "<h1>Erro 404</h1><p>Página não encontrada: $path</p>";
        }
        break;
}