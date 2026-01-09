<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\Order\OrderRepository;
use App\Repositories\TableRepository;
use PDO;
use Exception;

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

    public function execute(int $restaurantId, int $tableId, array $payments): void
    {
        $conn = Database::connect();
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            $mesa = $this->tableRepo->findWithCurrentOrder($tableId, $restaurantId);

            if (!$mesa || !$mesa['current_order_id']) {
                throw new Exception("Mesa não encontrada ou sem pedido aberto.");
            }

            $orderId = $mesa['current_order_id'];
            
            $mainMethod = $payments[0]['method'] ?? 'dinheiro';
            $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

            // Updates usando Repository (sem SQL direto)
            $this->orderRepo->updateStatus($orderId, 'concluido');
            $this->orderRepo->updatePayment($orderId, true, $paymentMethodDesc);

            $this->paymentService->registerPayments($conn, $orderId, $payments);

            $desc = "Mesa #" . $mesa['number'];
            // TODO: registerMovement deveria ser via repo em breve, por enquanto usa Service que tem SQL encapsulado
            $this->cashRegisterService->registerMovement(
                $conn,
                $caixa['id'],
                $mesa['order_total'], // Veio do join no TableRepo
                $desc,
                $orderId
            );

            $this->tableRepo->free($tableId);

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
