<?php
namespace App\Services;

use App\Repositories\Order\OrderPaymentRepository;
use PDO;

class PaymentService
{
    private OrderPaymentRepository $paymentRepo;

    public function __construct(OrderPaymentRepository $paymentRepo) {
        $this->paymentRepo = $paymentRepo;
    }

    /**
     * Registra os pagamentos de um pedido
     * 
     * @param PDO $conn (Unused in new repo implementation but kept for signature compatibility)
     */
    public function registerPayments(PDO $conn, int $orderId, array $payments): float
    {
        if (empty($payments)) {
            return 0.0;
        }

        $total = 0.0;

        foreach ($payments as $pay) {
            $amount = floatval($pay['amount']);
            
            // Delegate to PaymentRepository
            $this->paymentRepo->addPayment($orderId, $pay['method'], $amount);
            
            $total += $amount;
        }

        return $total;
    }
}
