<?php

namespace App\Services;

use PDO;

class PaymentService
{
    /**
     * Registra os pagamentos de um pedido
     * ⚠️ NÃO gerencia transaction (deve ser chamado dentro de uma)
     * 
     * @param PDO $conn Conexão ativa com transaction iniciada
     * @param int $orderId ID do pedido
     * @param array $payments Array de pagamentos com 'method' e 'amount'
     * @return float Valor total dos pagamentos registrados
     */
    public function registerPayments(PDO $conn, int $orderId, array $payments): float
    {
        if (empty($payments)) {
            return 0.0;
        }

        $stmt = $conn->prepare("INSERT INTO order_payments (order_id, method, amount) VALUES (:oid, :method, :amount)");
        $total = 0.0;

        foreach ($payments as $pay) {
            $amount = floatval($pay['amount']);
            
            $stmt->execute([
                'oid' => $orderId,
                'method' => $pay['method'],
                'amount' => $amount
            ]);
            
            $total += $amount;
        }

        return $total;
    }
}
