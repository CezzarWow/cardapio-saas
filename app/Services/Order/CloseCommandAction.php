<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use PDO;
use Exception;

class CloseCommandAction
{
    private $paymentService;
    private $cashRegisterService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->cashRegisterService = new CashRegisterService();
    }

    public function execute(int $restaurantId, int $orderId, array $payments): void
    {
        $conn = Database::connect();
        
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :oid AND restaurant_id = :rid");
            $stmt->execute(['oid' => $orderId, 'rid' => $restaurantId]);
            $currentOrder = $stmt->fetch(PDO::FETCH_ASSOC);

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

                $conn->prepare("UPDATE orders SET is_paid = 1, payment_method = :method WHERE id = :oid")
                     ->execute(['oid' => $orderId, 'method' => $paymentMethodDesc]);
            }

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
