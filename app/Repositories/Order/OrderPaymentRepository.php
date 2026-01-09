<?php

namespace App\Repositories\Order;

use App\Core\Database;
use PDO;

/**
 * Repository para Pagamentos de Pedido
 * 
 * Responsável exclusivamente pela tabela `order_payments`
 */
class OrderPaymentRepository
{
    /**
     * Deleta todos os pagamentos de um pedido
     */
    public function deleteAll(int $orderId): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM order_payments WHERE order_id = :oid")
             ->execute(['oid' => $orderId]);
    }

    /**
     * Adiciona um pagamento ao pedido
     */
    public function addPayment(int $orderId, string $method, float $amount): void
    {
        $conn = Database::connect();
        $conn->prepare("INSERT INTO order_payments (order_id, method, amount) VALUES (:oid, :method, :amount)")
             ->execute(['oid' => $orderId, 'method' => $method, 'amount' => $amount]);
    }

    /**
     * Retorna resumo de vendas por método de pagamento
     * 
     * Usado para fechamento de caixa
     */
    public function getSalesSummary(int $restaurantId, string $openedAt): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT op.method, SUM(op.amount) as total 
            FROM order_payments op
            INNER JOIN orders o ON o.id = op.order_id
            WHERE o.restaurant_id = :rid 
            AND o.created_at >= :opened_at 
            AND o.status = 'concluido'
            GROUP BY op.method
        ");
        $stmt->execute(['rid' => $restaurantId, 'opened_at' => $openedAt]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
