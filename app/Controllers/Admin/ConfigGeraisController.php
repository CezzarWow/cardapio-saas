<?php

namespace App\Controllers\Admin;

class ConfigGeraisController extends BaseController {

    public function index() {
        $this->getRestaurantId(); // Checa sessão
        require __DIR__ . '/../../../views/admin/config-gerais/index.php';
    }
}
