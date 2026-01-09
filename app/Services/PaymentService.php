<?php
namespace App\Services;

use App\Repositories\Order\OrderRepository;
use PDO;

class PaymentService
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo) {
        $this->orderRepo = $orderRepo;
    }

    /**
     * Registra os pagamentos de um pedido
     * 
     * @param PDO $conn (Unused in new repo implementation but kept for signature compatibility if needed)
     */
    public function registerPayments(PDO $conn, int $orderId, array $payments): float
    {
        if (empty($payments)) {
            return 0.0;
        }

        $total = 0.0;

        foreach ($payments as $pay) {
            $amount = floatval($pay['amount']);
            
            // Delegate to Repository
            $this->orderRepo->addPayment($orderId, $pay['method'], $amount);
            
            $total += $amount;
        }

        return $total;
    }
}
