<?php

namespace App\Controllers\Admin;

use App\Core\View;
use App\Services\RestaurantService;

/**
 * DashboardController - Super Thin
 * Tela inicial do Admin (Seleção de Loja / Visão Geral)
 */
class DashboardController extends BaseController
{
    private RestaurantService $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    public function index()
    {
        $userId = $this->getUserId(); // Método do BaseController (valida sessão)

        // Busca apenas restaurantes do usuário logado
        $rawRestaurants = $this->restaurantService->getByUser($userId);

        // --- PREPARAÇÃO DO VIEWMODEL ---
        $restaurants = array_map(function ($loja) {
            $isActive = (bool) $loja['is_active'];

            return array_merge($loja, [
                'is_active_bool' => $isActive,
                'status_label' => $isActive ? 'Ativo' : 'Suspenso',
                'status_class' => $isActive ? 'status-ativo' : 'status-suspenso',
                'slug_display' => '/' . $loja['slug']
            ]);
        }, $rawRestaurants);

        // View espera $restaurants
        View::renderFromScope('admin/dashboard', get_defined_vars());
    }
}
