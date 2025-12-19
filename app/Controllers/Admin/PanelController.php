<?php
namespace App\Controllers\Admin;

class PanelController {
    
    public function index() {
        // 1. Segurança: Verifica se tem loja na sessão
        if (!isset($_SESSION['loja_ativa_id'])) {
            die("Acesso negado. Selecione uma loja no Admin Geral.");
        }

        $nomeLoja = $_SESSION['loja_ativa_nome'];
        $idLoja = $_SESSION['loja_ativa_id'];

        // 2. Carrega a View do Painel da Loja
        // Vamos criar essa view no Passo 3
        require __DIR__ . '/../../../views/admin/panel/dashboard.php';
    }
}
