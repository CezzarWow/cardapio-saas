<?php

namespace App\Controllers\Admin;

/**
 * PanelController - Super Thin
 * Redirecionador para o PDV (antigo dashboard)
 */
class PanelController extends BaseController
{
    public function index()
    {
        // Valida sessão (BaseController)
        $this->getRestaurantId();

        // Redireciona para o controller real do PDV
        $this->redirect('/admin/loja/pdv');
    }
}
