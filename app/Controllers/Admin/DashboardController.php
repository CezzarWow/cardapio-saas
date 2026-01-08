<?php

namespace App\Controllers\Admin;

use App\Services\RestaurantService;

/**
 * DashboardController - Super Thin
 * Tela inicial do Admin (Seleção de Loja / Visão Geral)
 */
class DashboardController extends BaseController
{
    private RestaurantService $restaurantService;

    public function __construct() {
        $this->restaurantService = new RestaurantService();
    }
    
    public function index() {
        $userId = $this->getUserId(); // Método do BaseController (valida sessão)

        // Busca apenas restaurantes do usuário logado
        $restaurants = $this->restaurantService->getByUser($userId);

        // View espera $restaurants
        require __DIR__ . '/../../../views/admin/dashboard.php';
    }
}