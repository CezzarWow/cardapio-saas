<?php

namespace App\Services\Order\Flows\Comanda;

use App\Core\Database;
use App\Repositories\Order\OrderRepository;
use App\Services\CashRegisterService;
use App\Services\Order\OrderStatus;
use App\Services\PaymentService;
use Exception;
use RuntimeException;

/**
 * Action: Fechar Comanda
 *
 * Fluxo ISOLADO para fechar comanda com pagamento.
 *
 * Responsabilidades:
 * - Validar comanda existe e está ABERTA
 * - Validar pagamento cobre total
 * - Registrar pagamentos
 * - Atualizar status para CONCLUIDO
 * - Registrar movimento de caixa
 */
class CloseComandaAction
{
    private PaymentService $paymentService;
    private CashRegisterService $cashRegisterService;
    private OrderRepository $orderRepo;
    private ComandaValidator $validator;

    public function __construct(
        PaymentService $paymentService,
        CashRegisterService $cashRegisterService,
        OrderRepository $orderRepo,
        ComandaValidator $validator
    ) {
        $this->paymentService = $paymentService;
        $this->cashRegisterService = $cashRegisterService;
        $this->orderRepo = $orderRepo;
        $this->validator = $validator;
    }

    /**
     * Fecha comanda com pagamento
     */
    public function execute(int $restaurantId, array $data): array
    {
        $conn = Database::connect();
        $orderId = intval($data['order_id']);

        // 1. Validar caixa aberto
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        // 2. Buscar comanda
        $order = $this->orderRepo->find($orderId, $restaurantId);

        if (!$order) {
            throw new Exception("Comanda #{$orderId} não encontrada");
        }

        // 3. Validar order_type = comanda
        if ($order['order_type'] !== 'comanda') {
            throw new Exception("Pedido #{$orderId} não é uma comanda");
        }

        // 4. Validar status ABERTO
        if ($order['status'] !== OrderStatus::ABERTO) {
            throw new Exception(
                "Comanda #{$orderId} não está aberta. Status: {$order['status']}"
            );
        }

        // 5. Validar pagamento cobre total
        $total = floatval($order['total']);
        $payments = $data['payments'];

        $paymentErrors = $this->validator->validatePaymentCoversTotal($total, $payments);
        if (!empty($paymentErrors)) {
            throw new Exception($paymentErrors['payments']);
        }

        try {
            $conn->beginTransaction();

            // 6. Registrar pagamentos
            $this->paymentService->registerPayments($conn, $orderId, $payments);

            // 7. Atualizar status para CONCLUIDO
            $affected = $this->orderRepo->updateStatus($orderId, OrderStatus::CONCLUIDO);

            if ($affected === 0) {
                throw new RuntimeException(
                    "updateStatus affected 0 rows for orderId: {$orderId}"
                );
            }

            // 8. Marcar como pago
            $mainMethod = count($payments) > 1
                ? 'multiplo'
                : ($payments[0]['method'] ?? 'dinheiro');
            $this->orderRepo->updatePayment($orderId, true, $mainMethod);

            // 9. Registrar movimento de caixa
            $clientId = $order['client_id'] ?? 'N/A';
            $this->cashRegisterService->registerMovement(
                $conn,
                $caixa['id'],
                $total,
                "Fechamento Comanda #{$orderId} - Cliente #{$clientId}",
                $orderId
            );

            $conn->commit();

            Logger::info("[COMANDA] Comanda #{$orderId} fechada", [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'client_id' => $order['client_id'] ?? null,
                'total' => $total
            ]);

            return [
                'order_id' => $orderId,
                'client_id' => $order['client_id'],
                'total' => $total,
                'status' => OrderStatus::CONCLUIDO
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            Logger::error('[COMANDA] ERRO ao fechar', [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Erro ao fechar comanda: ' . $e->getMessage());
        }
    }
}
