<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

/**
 * SalesService - Lógica de Negócio de Vendas/Histórico
 * 
 * Gerencia listagem de vendas, cancelamento e reativação de mesas.
 */
class SalesService
{
    /**
     * Lista todas as vendas do restaurante
     */
    public function listOrders(int $restaurantId): array
    {
        $conn = Database::connect();
        
        $sql = "SELECT o.*, t.number as table_number,
                COALESCE((SELECT SUM(i.price * i.quantity) FROM order_items i WHERE i.order_id = o.id), 0) as calculated_total
                FROM orders o 
                LEFT JOIN tables t ON t.current_order_id = o.id
                WHERE o.restaurant_id = :rid 
                ORDER BY o.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurantId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca itens de um pedido
     */
    public function getOrderItems(int $orderId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $orderId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cancela uma venda: estorna estoque e caixa
     */
    public function cancelOrder(int $orderId): array
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Verifica o Pedido
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id AND status = 'concluido'");
            $stmt->execute(['id' => $orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                throw new Exception("Pedido não encontrado ou já cancelado.");
            }

            // 2. Devolve Estoque
            $stmtItems = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :oid");
            $stmtItems->execute(['oid' => $orderId]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $conn->prepare("UPDATE products SET stock = stock + :qtd WHERE id = :pid")
                     ->execute(['qtd' => $item['quantity'], 'pid' => $item['product_id']]);
            }

            // 3. Estorna o Caixa
            $conn->prepare("DELETE FROM cash_movements WHERE order_id = :oid")
                 ->execute(['oid' => $orderId]);

            // 4. Marca como Cancelado
            $conn->prepare("UPDATE orders SET status = 'cancelado' WHERE id = :id")
                 ->execute(['id' => $orderId]);

            $conn->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reativa mesa: volta status para aberto, ocupa mesa novamente
     */
    public function reactivateTable(int $orderId, int $restaurantId): array
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Busca movimento do caixa para identificar mesa
            $stmtMov = $conn->prepare("SELECT description FROM cash_movements WHERE order_id = :oid");
            $stmtMov->execute(['oid' => $orderId]);
            $mov = $stmtMov->fetch(PDO::FETCH_ASSOC);

            if (!$mov) {
                throw new Exception("Pagamento não encontrado no caixa.");
            }

            // Extrai número da mesa da descrição "Pagamento Mesa #5"
            preg_match('/#(\d+)/', $mov['description'], $matches);
            $tableNum = $matches[1] ?? null;

            if (!$tableNum) {
                throw new Exception("Não foi possível identificar a mesa.");
            }

            // 2. Verifica se mesa está livre
            $stmtTable = $conn->prepare("SELECT id, status FROM tables WHERE number = :num AND restaurant_id = :rid");
            $stmtTable->execute(['num' => $tableNum, 'rid' => $restaurantId]);
            $table = $stmtTable->fetch(PDO::FETCH_ASSOC);

            if (!$table) {
                throw new Exception("Mesa não encontrada.");
            }

            if ($table['status'] === 'ocupada') {
                throw new Exception("A Mesa $tableNum já está ocupada por outro cliente!");
            }

            // 3. Reverte tudo
            $conn->prepare("UPDATE orders SET status = 'aberto' WHERE id = :oid")
                 ->execute(['oid' => $orderId]);

            $conn->prepare("UPDATE tables SET status = 'ocupada', current_order_id = :oid WHERE id = :tid")
                 ->execute(['oid' => $orderId, 'tid' => $table['id']]);

            $conn->prepare("DELETE FROM cash_movements WHERE order_id = :oid")
                 ->execute(['oid' => $orderId]);

            $conn->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
