<?php
namespace App\Controllers\Admin;

use App\Services\Pdv\PdvService;

/**
 * PdvController - Painel de Vendas (Super Thin)
 * Responsável pelo Frente de Caixa
 */
class PdvController extends BaseController {

    private PdvService $service;

    public function __construct() {
        $this->service = new PdvService();
    }

    public function index() {
        $rid = $this->getRestaurantId();
        
        // Contexto (Mesa ou Comanda)
        $mesaId = $this->getInt('mesa_id'); // getInt retorna 0 se nulo
        $mesaNumero = $_GET['mesa_numero'] ?? null;
        $orderId = $this->getInt('order_id');

        // Busca dados do contexto (Conta aberta, itens já pedidos)
        $context = $this->service->getContextData($rid, $mesaId > 0 ? $mesaId : null, $orderId > 0 ? $orderId : null);
        
        // Extrai variáveis para a View
        $contaAberta = $context['contaAberta'];
        $itensJaPedidos = $context['itensJaPedidos'];
        $isComanda = $context['isComanda'];
        
        // Passa variáveis de contexto mesa para a view (previne Undefined variable)
        $mesa_id = $mesaId; // View usa snake_case
        $mesa_numero = $mesaNumero;

        // Carrega Cardápio (Categorias + Produtos)
        $categories = $this->service->getMenu($rid);

        // Verifica recuperação de carrinho (Modo Edição)
        $cartRecovery = $_SESSION['cart_recovery'] ?? [];
        $isEditing = isset($_SESSION['cart_recovery']);

        require __DIR__ . '/../../../views/admin/panel/dashboard.php';
    }

    /**
     * Cancela a edição e restaura o backup do pedido
     */
    public function cancelEdit() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['edit_backup'])) {
            $this->redirect('../../admin/loja/pdv');
        }

        try {
            $this->service->restoreOrder($_SESSION['edit_backup']);

            // Limpa sessões
            unset($_SESSION['edit_backup']);
            unset($_SESSION['cart_recovery']);

            $this->redirect('../../admin/loja/caixa');

        } catch (\Exception $e) {
            die("Erro crítico ao cancelar edição: " . $e->getMessage());
        }
    }
}
