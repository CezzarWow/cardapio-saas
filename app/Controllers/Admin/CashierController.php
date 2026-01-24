<?php

namespace App\Controllers\Admin;

use App\Services\Cashier\CashierDashboardService;
use App\Services\Cashier\CashierTransactionService;
use App\Validators\CashierValidator;
use App\Core\View;

/**
 * CashierController - Super Thin (v3)
 * Usa Services para lógica de negócio
 */
class CashierController extends BaseController
{
    private const BASE = '/admin/loja/caixa';

    private CashierValidator $v;
    private CashierDashboardService $dashboard;
    private CashierTransactionService $transaction;

    public function __construct(
        CashierDashboardService $dashboard,
        CashierTransactionService $transaction,
        CashierValidator $validator
    ) {
        $this->dashboard = $dashboard;
        $this->transaction = $transaction;
        $this->v = $validator;
    }

    // === DASHBOARD ===
    public function index()
    {
        $rid = $this->getRestaurantId();

        $caixa = $this->dashboard->getOpenCashier($rid);
        if (!$caixa) {
            View::renderFromScope('admin/cashier/open', get_defined_vars());
            return;
        }

        $resumo = $this->dashboard->calculateSalesSummary($rid, $caixa['opened_at']);
        $movimentos = $this->dashboard->getMovements($caixa['id']);
        // Calcular totais
        list($totalSuprimentos, $totalSangrias) = $this->dashboard->sumMovements($movimentos);
        $dinheiroEmCaixa = $this->dashboard->calculateCashInDrawer(
            $caixa['opening_balance'],
            $resumo['dinheiro'],
            $totalSuprimentos,
            $totalSangrias
        );

        // Decorar movimentos para a View (ViewModel)
        $movimentosView = array_map(function ($m) {
            $isSangria = $m['type'] == 'sangria';
            return [
                'id' => $m['id'],
                'type' => $m['type'],
                'description' => $m['description'],
                'amount' => $m['amount'],
                'created_at' => $m['created_at'],
                'order_id' => $m['order_id'],
                // UI Helpers
                'is_sangria' => $isSangria,
                'color_bg' => $isSangria ? '#fee2e2' : '#dcfce7',
                'color_text' => $isSangria ? '#991b1b' : '#166534',
                'icon' => $isSangria ? 'arrow-up-right' : 'arrow-down-left',
                'sign' => $isSangria ? '-' : '+',
                'is_table_reopen' => strpos($m['description'] ?? '', 'Mesa') !== false
            ];
        }, $movimentos);

        View::renderFromScope('admin/cashier/dashboard', get_defined_vars());
    }

    // === ABRIR CAIXA ===
    public function open()
    {
        $this->handleValidatedPost(
            fn () => $this->v->validateOpenCashier($_POST),
            fn () => $this->v->sanitizeOpenCashier($_POST),
            fn ($data, $rid) => $this->dashboard->openRegister($rid, $data['opening_balance']),
            self::BASE,
            'aberto'
        );
    }

    // === VERIFICAR PENDÊNCIAS (AJAX) ===
    public function checkPending()
    {
        header('Content-Type: application/json');
        $rid = $this->getRestaurantId();
        $pendencias = $this->dashboard->checkPendingItems($rid);
        
        if (!empty($pendencias)) {
            echo json_encode([
                'success' => false,
                'pendencias' => $pendencias
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'pendencias' => []
            ]);
        }
        exit;
    }

    // === FECHAR CAIXA ===
    public function close()
    {
        $rid = $this->getRestaurantId();
        $result = $this->dashboard->closeRegister($rid);
        
        if (!$result['success']) {
            // Construir mensagem de erro detalhada
            $mensagens = array_column($result['pendencias'], 'mensagem');
            $erroMsg = implode(' | ', $mensagens);
            $_SESSION['flash_error'] = $erroMsg;
            $this->redirect(self::BASE . '?error=pendencias');
            return;
        }
        
        $this->redirect(self::BASE);
    }

    // === ADICIONAR MOVIMENTO ===
    public function addMovement()
    {
        $this->handleValidatedPost(
            fn () => $this->v->validateMovement($_POST),
            fn () => $this->v->sanitizeMovement($_POST),
            fn ($data, $rid) => $this->addMovementToCashier($rid, $data),
            self::BASE,
            'movimento_adicionado'
        );
    }

    // === REVERTER PARA PDV ===
    public function reverseToPdv()
    {
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
    public function reverseToTable()
    {
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
    public function removeMovement()
    {
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
    private function addMovementToCashier(int $rid, array $data): void
    {
        $caixa = $this->dashboard->getOpenCashier($rid);
        if (!$caixa) {
            throw new \Exception('Nenhum caixa aberto');
        }
        $this->dashboard->addMovement($caixa['id'], $data['type'], $data['amount'], $data['description']);
    }
}
