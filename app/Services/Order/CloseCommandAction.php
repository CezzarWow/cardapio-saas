<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\Order\OrderRepository;
use PDO;
use Exception;

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

    public function execute(int $restaurantId, int $orderId, array $payments): void
    {
        $conn = Database::connect();
        
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            $currentOrder = $this->orderRepo->find($orderId, $restaurantId);

            if (!$currentOrder) {
                throw new Exception('Pedido não encontrado');
            }

            if ($currentOrder['is_paid'] == 0 && empty($payments)) {
                throw new Exception('Nenhum pagamento informado');
            }

            if (!empty($payments)) {
                $mainMethod = $payments[0]['method'] ?? 'dinheiro';
                $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

                $totalPago = $this->paymentService->registerPayments($conn, $orderId, $payments);

                $desc = "Comanda #" . $orderId;
                $this->cashRegisterService->registerMovement(
                    $conn,
                    $caixa['id'],
                    $totalPago,
                    $desc,
                    $orderId
                );

                $this->orderRepo->updatePayment($orderId, true, $paymentMethodDesc);
            }

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
