<?php

namespace App\Controllers\Admin;

use App\Core\View;

class ConfigGeraisController extends BaseController
{
    public function index()
    {
        $this->getRestaurantId(); // Checa sessão
        View::renderFromScope('admin/config-gerais/index', get_defined_vars());
    }
}
