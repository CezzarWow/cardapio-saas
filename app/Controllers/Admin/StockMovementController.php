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
     * Listar Movimentações - Redireciona para SPA Dashboard
     */
    public function index(): void
    {
        $this->redirect('/admin/loja/catalogo#movimentacoes');
    }
}
