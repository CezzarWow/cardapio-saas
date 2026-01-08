<?php

namespace App\Controllers\Admin;

use App\Services\Stock\StockService;

/**
 * StockMovementController - Super Thin
 * Responsável somente pela exibição do relatório de movimentações
 */
class StockMovementController extends BaseController
{
    private StockService $service;

    public function __construct() {
        $this->service = new StockService();
    }

    /**
     * Listar Movimentações (Relatório)
     */
    public function index(): void {
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

        require __DIR__ . '/../../../views/admin/movements/index.php';
    }
}
