<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Repositories\Order\OrderRepository;
use App\Repositories\TableRepository;
use App\Services\CashRegisterService;
use App\Services\PaymentService;
use Exception;
use RuntimeException;

/**
 * Fecha uma mesa (conta de mesa).
 *
 * Responsabilidades:
 * - Registrar pagamentos
 * - Atualizar status para 'concluido'
 * - Registrar movimento de caixa
 * - Liberar mesa
 *
 * @see implementation_plan.md Seção 0 (Separação Pedido vs Conta)
 */
class CloseTableAction
{
    private PaymentService $paymentService;
    private CashRegisterService $cashRegisterService;
    private OrderRepository $orderRepo;
    private TableRepository $tableRepo;

    public function __construct(
        PaymentService $paymentService,
        CashRegisterService $cashRegisterService,
        OrderRepository $orderRepo,
        TableRepository $tableRepo
    ) {
        $this->paymentService = $paymentService;
        $this->cashRegisterService = $cashRegisterService;
        $this->orderRepo = $orderRepo;
        $this->tableRepo = $tableRepo;
    }

    /**
     * Executa o fechamento da mesa.
     *
     * @param int $restaurantId ID do restaurante
     * @param int $tableId ID da mesa
     * @param array $payments Lista de pagamentos
     * @throws Exception Se mesa não encontrada ou sem pedido
     * @throws RuntimeException Se updateStatus não afetar linhas
     */
    public function execute(int $restaurantId, int $tableId, array $payments): void
    {
        $conn = Database::connect();
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            $mesa = $this->tableRepo->findWithCurrentOrder($tableId, $restaurantId);

            if (!$mesa || !$mesa['current_order_id']) {
                throw new Exception('Mesa não encontrada ou sem pedido aberto.');
            }

            $orderId = $mesa['current_order_id'];

            // Validar status atual
            $order = $this->orderRepo->find($orderId, $restaurantId);
            if ($order && $order['status'] !== 'aberto') {
                throw new Exception(
                    "Mesa #{$mesa['number']} não tem pedido aberto. Status atual: {$order['status']}"
                );
            }

            $mainMethod = $payments[0]['method'] ?? 'dinheiro';
            $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

            // Atualizar status para concluido COM rowCount check
            $affected = $this->orderRepo->updateStatus($orderId, 'concluido');

            if ($affected === 0) {
                throw new RuntimeException(
                    "updateStatus affected 0 rows for orderId: {$orderId}"
                );
            }

            $this->orderRepo->updatePayment($orderId, true, $paymentMethodDesc);

            $this->paymentService->registerPayments($conn, $orderId, $payments);

            $desc = 'Mesa #' . $mesa['number'];
            $this->cashRegisterService->registerMovement(
                $conn,
                $caixa['id'],
                $mesa['order_total'],
                $desc,
                $orderId
            );

            $this->tableRepo->free($tableId);

            $conn->commit();

            error_log("[CLOSE_TABLE] Mesa #{$mesa['number']} fechada. Pedido #{$orderId} status: concluido");

        } catch (\Throwable $e) {
            $conn->rollBack();
            error_log("[CLOSE_TABLE] ERRO mesa #{$tableId}: " . $e->getMessage());
            throw $e;
        }
    }
}
