<?php

namespace App\Controllers\Admin;

use App\Services\Stock\StockService;
use App\Core\View;

/**
 * StockMovementController - Super Thin
 * Responsável somente pela exibição do relatório de movimentações
 */
class StockMovementController extends BaseController
{
    private StockService $service;

    public function __construct(StockService $service)
    {
        $this->service = $service;
    }

    /**
     * Listar Movimentações (Relatório)
     */
    public function index(): void
    {
        $restaurantId = $this->getRestaurantId();

        // Coleta filtros
        $filters = [
            'product'    => $_GET['product'] ?? '',
            'category'   => $_GET['category'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date'   => $_GET['end_date'] ?? ''
        ];

        // Busca dados via Service
        $movements = $this->service->getMovements($restaurantId, $filters);
        $products = $this->service->getProducts($restaurantId);
        $categories = $this->service->getCategories($restaurantId);

        View::renderFromScope('admin/movements/index', get_defined_vars());
    }
}
