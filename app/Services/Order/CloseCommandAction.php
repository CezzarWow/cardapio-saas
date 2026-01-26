<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\Order\OrderRepository;
use App\Services\CashRegisterService;
use App\Services\PaymentService;
use Exception;
use RuntimeException;

/**
 * Fecha uma comanda (conta de cliente).
 *
 * Responsabilidades:
 * - Registrar pagamentos
 * - Atualizar is_paid e payment_method
 * - Atualizar status para 'concluido'
 * - Registrar movimento de caixa
 *
 * @see implementation_plan.md Seção 0 (Separação Pedido vs Conta)
 */
class CloseCommandAction
{
    private PaymentService $paymentService;
    private CashRegisterService $cashRegisterService;
    private OrderRepository $orderRepo;

    public function __construct(
        PaymentService $paymentService,
        CashRegisterService $cashRegisterService,
        OrderRepository $orderRepo
    ) {
        $this->paymentService = $paymentService;
        $this->cashRegisterService = $cashRegisterService;
        $this->orderRepo = $orderRepo;
    }

    /**
     * Executa o fechamento da comanda.
     *
     * @param int $restaurantId ID do restaurante
     * @param int $orderId ID da comanda (pedido com status 'aberto')
     * @param array $payments Lista de pagamentos [{method, amount}, ...]
     * @throws Exception Se validação falhar
     * @throws RuntimeException Se updateStatus não afetar linhas
     * @return int ID do pedido fechado (para impressão)
     */
    public function execute(int $restaurantId, int $orderId, array $payments): int
    {
        $conn = Database::connect();

        // Validar caixa aberto antes de iniciar transação
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            // 1. Buscar pedido atual
            $currentOrder = $this->orderRepo->find($orderId, $restaurantId);

            if (!$currentOrder) {
                throw new Exception('Pedido não encontrado');
            }

            // 2. Validar estado atual (deve ser 'aberto' para fechar comanda)
            if ($currentOrder['status'] !== 'aberto') {
                throw new Exception(
                    "Comanda #{$orderId} não está aberta. Status atual: {$currentOrder['status']}"
                );
            }

            // 3. Validar pagamentos se não estiver pago
            if ($currentOrder['is_paid'] == 0 && empty($payments)) {
                throw new Exception('Nenhum pagamento informado');
            }

            // 4. Registrar pagamentos (se houver)
            if (!empty($payments)) {
                $mainMethod = $payments[0]['method'] ?? 'dinheiro';
                $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

                // Registrar pagamentos na tabela order_payments
                $totalPago = $this->paymentService->registerPayments($conn, $orderId, $payments);

                // Registrar movimento de caixa
                $desc = 'Comanda #' . $orderId;
                $this->cashRegisterService->registerMovement(
                    $conn,
                    $caixa['id'],
                    $totalPago,
                    $desc,
                    $orderId
                );

                // Atualizar flag is_paid e método
                $this->orderRepo->updatePayment($orderId, true, $paymentMethodDesc);
            }

            // 5. ATUALIZAR STATUS PARA CONCLUIDO (crítico)
            $affected = $this->orderRepo->updateStatus($orderId, 'concluido');

            if ($affected === 0) {
                throw new RuntimeException(
                    "updateStatus affected 0 rows for orderId: {$orderId}"
                );
            }

            $conn->commit();

            // Log de sucesso
            Logger::info("[CLOSE_COMMAND] Comanda fechada com sucesso", [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'status' => 'concluido'
            ]);

            return $orderId;

        } catch (\Throwable $e) {
            $conn->rollBack();
            Logger::error("[CLOSE_COMMAND] ERRO ao fechar comanda", [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
