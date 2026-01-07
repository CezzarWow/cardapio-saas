<?php
/**
 * ============================================
 * CARDÁPIO CONTROLLER - DDD Lite
 * Gerencia configurações do cardápio web
 * ============================================
 */

namespace App\Controllers\Admin;

use App\Services\Cardapio\CardapioQueryService;
use App\Services\Cardapio\UpdateCardapioConfigService;
use App\Services\Admin\ComboService;

class CardapioController {

    // ==========================================
    // LISTAGEM PRINCIPAL (VIEW)
    // ==========================================
    public function index() {
        $this->checkSession();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        $queryService = new CardapioQueryService();
        $data = $queryService->getIndexData($restaurantId);
        
        // Extrai variáveis para a view
        extract($data);

        require __DIR__ . '/../../../views/admin/cardapio/index.php';
    }

    // ==========================================
    // ATUALIZAR CONFIGURAÇÕES
    // ==========================================
    public function update() {
        $this->checkSession();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        $service = new UpdateCardapioConfigService();
        $service->execute($restaurantId, $_POST);

        // Log da ação
        if (class_exists('\App\Core\Logger')) {
            \App\Core\Logger::info('Configurações do cardápio atualizadas', [
                'restaurant_id' => $restaurantId
            ]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=salvo#destaques');
        exit;
    }

    // ==========================================
    // COMBO: FORMULÁRIO
    // ==========================================
    public function comboForm() {
        $this->checkSession();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $combo = null;
        $comboProducts = [];

        $queryService = new CardapioQueryService();
        $data = $queryService->getComboFormData($restaurantId);
        $products = $data['products'];

        require __DIR__ . '/../../../views/admin/cardapio/combo_form.php';
    }

    // ==========================================
    // COMBO: CRIAR (delega para ComboService)
    // ==========================================
    public function storeCombo() {
        $this->checkSession();
        $service = new ComboService();
        $service->store();
    }

    // ==========================================
    // COMBO: EDITAR
    // ==========================================
    public function editCombo() {
        $this->checkSession();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        // Se for AJAX, o Service faz o exit internamente
        $service = new ComboService();
        $result = $service->getForEdit();
        
        // Se chegou aqui, é requisição tradicional (view)
        $combo = $result['combo'];
        $comboProducts = $result['comboProducts'];
        $comboItemsSettings = $result['comboItemsSettings'];
        
        // Buscar produtos para a view
        $queryService = new CardapioQueryService();
        $data = $queryService->getComboFormData($restaurantId);
        $products = $data['products'];

        require __DIR__ . '/../../../views/admin/cardapio/combo_form.php';
    }

    // ==========================================
    // COMBO: ATUALIZAR (delega para ComboService)
    // ==========================================
    public function updateCombo() {
        $this->checkSession();
        $service = new ComboService();
        $service->update();
    }

    // ==========================================
    // COMBO: DELETAR (delega para ComboService)
    // ==========================================
    public function deleteCombo() {
        $this->checkSession();
        $service = new ComboService();
        $service->delete();
    }

    // ==========================================
    // COMBO: TOGGLE STATUS (delega para ComboService)
    // ==========================================
    public function toggleComboStatus() {
        $this->checkSession();
        $service = new ComboService();
        $service->toggleStatus();
    }

    // ==========================================
    // SESSÃO
    // ==========================================
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
