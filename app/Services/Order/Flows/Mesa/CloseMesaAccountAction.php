<?php

namespace App\Services\Order\Flows\Mesa;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\Order\OrderRepository;
use App\Repositories\TableRepository;
use App\Services\CashRegisterService;
use App\Services\Order\OrderStatus;
use App\Services\PaymentService;
use Exception;
use RuntimeException;

/**
 * Action: Fechar Conta de Mesa
 *
 * Fluxo ISOLADO para fechar conta com pagamento.
 *
 * Responsabilidades:
 * - Validar mesa existe e tem pedido ABERTO
 * - Validar pagamento cobre total
 * - Registrar pagamentos
 * - Atualizar status para CONCLUIDO
 * - Liberar mesa
 * - Registrar movimento de caixa
 *
 * NÃO FAZ:
 * - Adicionar itens
 * - Operações de Balcão/Comanda/Delivery
 */
class CloseMesaAccountAction
{
    private PaymentService $paymentService;
    private CashRegisterService $cashRegisterService;
    private OrderRepository $orderRepo;
    private TableRepository $tableRepo;
    private MesaValidator $validator;

    public function __construct(
        PaymentService $paymentService,
        CashRegisterService $cashRegisterService,
        OrderRepository $orderRepo,
        TableRepository $tableRepo,
        MesaValidator $validator
    ) {
        $this->paymentService = $paymentService;
        $this->cashRegisterService = $cashRegisterService;
        $this->orderRepo = $orderRepo;
        $this->tableRepo = $tableRepo;
        $this->validator = $validator;
    }

    /**
     * Fecha conta de mesa
     *
     * @param int $restaurantId ID do restaurante
     * @param array $data Payload validado
     * @return array ['order_id' => int, 'total' => float, 'status' => string]
     * @throws Exception Se validação falhar
     * @throws RuntimeException Se erro na transação
     */
    public function execute(int $restaurantId, array $data): array
    {
        $conn = Database::connect();
        $tableId = intval($data['table_id']);

        // 1. Validar caixa aberto
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        // 2. Buscar mesa
        $mesa = $this->tableRepo->findWithCurrentOrder($tableId, $restaurantId);

        if (empty($mesa)) {
            throw new Exception("Mesa #{$tableId} não encontrada");
        }

        if (empty($mesa['current_order_id'])) {
            throw new Exception("Mesa #{$mesa['number']} não tem pedido aberto");
        }

        $orderId = intval($mesa['current_order_id']);

        // 3. Buscar pedido
        $order = $this->orderRepo->find($orderId, $restaurantId);

        if (!$order) {
            throw new Exception("Pedido #{$orderId} não encontrado");
        }

        // 4. Validar status ABERTO
        if ($order['status'] !== OrderStatus::ABERTO) {
            throw new Exception(
                "Mesa #{$mesa['number']} não tem conta aberta. Status: {$order['status']}"
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

            // 9. Liberar mesa
            $this->tableRepo->free($tableId);

            // 10. Registrar movimento de caixa
            $this->cashRegisterService->registerMovement(
                $conn,
                $caixa['id'],
                $total,
                "Fechamento Mesa #{$mesa['number']} - Pedido #{$orderId}",
                $orderId
            );

            $conn->commit();

            Logger::info("[MESA] Conta fechada: Mesa #{$mesa['number']}, Pedido #{$orderId}", [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'table_id' => $tableId,
                'table_number' => $mesa['number'],
                'total' => $total
            ]);

            return [
                'order_id' => $orderId,
                'table_id' => $tableId,
                'table_number' => $mesa['number'],
                'total' => $total,
                'status' => OrderStatus::CONCLUIDO
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            Logger::error('[MESA] ERRO ao fechar', [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'table_id' => $tableId,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Erro ao fechar mesa: ' . $e->getMessage());
        }
    }
}
