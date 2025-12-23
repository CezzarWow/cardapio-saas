<?php
namespace App\Controllers\Admin;

class DeliveryController {

    public function index() {
        $this->checkSession();
        require __DIR__ . '/../../../views/admin/delivery/index.php';
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
