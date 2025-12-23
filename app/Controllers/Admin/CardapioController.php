<?php
namespace App\Controllers\Admin;

class CardapioController {

    public function index() {
        $this->checkSession();
        require __DIR__ . '/../../../views/admin/cardapio/index.php';
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
