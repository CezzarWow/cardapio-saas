<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use PDO;
use Exception;

class CloseTableAction
{
    private $paymentService;
    private $cashRegisterService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->cashRegisterService = new CashRegisterService();
    }

    public function execute(int $restaurantId, int $tableId, array $payments): void
    {
        $conn = Database::connect();
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("SELECT t.current_order_id, t.number, o.total 
                                    FROM tables t 
                                    JOIN orders o ON t.current_order_id = o.id 
                                    WHERE t.id = :tid AND t.restaurant_id = :rid");
            $stmt->execute(['tid' => $tableId, 'rid' => $restaurantId]);
            $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$mesa || !$mesa['current_order_id']) {
                throw new Exception("Mesa não encontrada ou sem pedido aberto.");
            }

            $orderId = $mesa['current_order_id'];
            
            $mainMethod = $payments[0]['method'] ?? 'dinheiro';
            $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

            $conn->prepare("UPDATE orders SET status = 'concluido', is_paid = 1, payment_method = :method WHERE id = :oid")
                 ->execute(['oid' => $orderId, 'method' => $paymentMethodDesc]);

            $this->paymentService->registerPayments($conn, $orderId, $payments);

            $desc = "Mesa #" . $mesa['number'];
            $this->cashRegisterService->registerMovement(
                $conn,
                $caixa['id'],
                $mesa['total'],
                $desc,
                $orderId
            );

            $conn->prepare("UPDATE tables SET status = 'livre', current_order_id = NULL WHERE id = :tid")
                 ->execute(['tid' => $tableId]);

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
