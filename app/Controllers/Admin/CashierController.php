<?php
namespace App\Controllers\Admin;

use App\Services\Cashier\CashierDashboardService;
use App\Services\Cashier\CashierTransactionService;
use App\Validators\CashierValidator;

/**
 * CashierController - Super Thin (v3)
 * Usa Services para lógica de negócio
 */
class CashierController extends BaseController {

    private const BASE = '/admin/loja/caixa';
    
    private CashierValidator $v;
    private CashierDashboardService $dashboard;
    private CashierTransactionService $transaction;

    public function __construct() {
        $this->v = new CashierValidator();
        $this->dashboard = new CashierDashboardService();
        $this->transaction = new CashierTransactionService();
    }

    // === DASHBOARD ===
    public function index() {
        $rid = $this->getRestaurantId();

        $caixa = $this->dashboard->getOpenCashier($rid);
        if (!$caixa) {
            require __DIR__ . '/../../../views/admin/cashier/open.php';
            return;
        }

        $resumo = $this->dashboard->calculateSalesSummary($rid, $caixa['opened_at']);
        $movimentos = $this->dashboard->getMovements($caixa['id']);
        list($totalSuprimentos, $totalSangrias) = $this->dashboard->sumMovements($movimentos);
        $dinheiroEmCaixa = $this->dashboard->calculateCashInDrawer(
            $caixa['opening_balance'], $resumo['dinheiro'], $totalSuprimentos, $totalSangrias
        );

        require __DIR__ . '/../../../views/admin/cashier/dashboard.php';
    }

    // === ABRIR CAIXA ===
    public function open() {
        $this->handleValidatedPost(
            fn() => $this->v->validateOpenCashier($_POST),
            fn() => $this->v->sanitizeOpenCashier($_POST),
            fn($data, $rid) => $this->dashboard->openCashier($rid, $data['opening_balance']),
            self::BASE, 'aberto'
        );
    }

    // === FECHAR CAIXA ===
    public function close() {
        $rid = $this->getRestaurantId();
        $this->dashboard->closeCashier($rid);
        $this->redirect(self::BASE);
    }

    // === ADICIONAR MOVIMENTO ===
    public function addMovement() {
        $this->handleValidatedPost(
            fn() => $this->v->validateMovement($_POST),
            fn() => $this->v->sanitizeMovement($_POST),
            fn($data, $rid) => $this->addMovementToCashier($rid, $data),
            self::BASE, 'movimento_adicionado'
        );
    }

    // === REVERTER PARA PDV ===
    public function reverseToPdv() {
        $movementId = $this->getInt('id');
        if ($movementId <= 0) {
            $this->redirect(self::BASE . '?error=id_invalido');
        }

        try {
            $result = $this->transaction->reverseToPdv($movementId);
            $_SESSION['edit_backup'] = $result;
            $_SESSION['cart_recovery'] = $result['items'];
            $this->redirect('/admin/loja/pdv?mode=edit');
        } catch (\Exception $e) {
            error_log('reverseToPdv Error: ' . $e->getMessage());
            $this->redirect(self::BASE . '?error=operacao_falhou');
        }
    }

    // === REVERTER PARA MESA ===
    public function reverseToTable() {
        $rid = $this->getRestaurantId();
        $movementId = $this->getInt('id');
        
        if ($movementId <= 0) {
            $this->redirect(self::BASE . '?error=id_invalido');
        }

        try {
            $this->transaction->reverseToTable($movementId, $rid);
            $this->redirect('/admin/loja/mesas');
        } catch (\Exception $e) {
            error_log('reverseToTable Error: ' . $e->getMessage());
            $this->redirect(self::BASE . '?error=operacao_falhou');
        }
    }

    // === REMOVER MOVIMENTO ===
    public function removeMovement() {
        $movementId = $this->getInt('id');
        
        if ($movementId <= 0) {
            $this->redirect(self::BASE . '?error=id_invalido');
        }

        try {
            $this->transaction->removeMovement($movementId);
            $this->redirect(self::BASE);
        } catch (\Exception $e) {
            error_log('removeMovement Error: ' . $e->getMessage());
            $this->redirect(self::BASE . '?error=operacao_falhou');
        }
    }

    // === HELPER ===
    private function addMovementToCashier(int $rid, array $data): void {
        $caixa = $this->dashboard->getOpenCashier($rid);
        if (!$caixa) {
            throw new \Exception('Nenhum caixa aberto');
        }
        $this->dashboard->addMovement($caixa['id'], $data['type'], $data['amount'], $data['description']);
    }
}
