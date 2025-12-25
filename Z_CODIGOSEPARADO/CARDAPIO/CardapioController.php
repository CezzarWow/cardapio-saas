/**
 * ═══════════════════════════════════════════════════════════════════════════
 * LOCALIZAÇÃO ORIGINAL: app/Controllers/Admin/CardapioController.php
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * DESCRIÇÃO: Controller principal da aba Cardápio
 * STATUS: Em construção (apenas placeholder atualmente)
 * 
 * COMO EXPANDIR:
 * 1. Adicionar métodos para cada funcionalidade (salvar, editar, deletar)
 * 2. Conectar ao banco de dados usando Database::connect()
 * 3. Criar views correspondentes em views/admin/cardapio/
 * 4. Adicionar rotas no public/index.php
 * ═══════════════════════════════════════════════════════════════════════════
 */

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
