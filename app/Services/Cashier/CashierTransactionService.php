<?php
namespace App\Services\Cashier;

use App\Core\Database;
use PDO;
use Exception;

/**
 * CashierTransactionService - Lógica de Transações e Reversões
 * 
 * Responsabilidades:
 * - Reverter vendas para PDV (edição)
 * - Reverter vendas para Mesa
 * - Remover/Cancelar movimentos
 */
class CashierTransactionService {

    /**
     * Reverte venda para PDV (modo edição)
     * 
     * @return array Itens para recuperar no carrinho
     * @throws Exception
     */
    public function reverseToPdv(int $movementId): array {
        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();
            
            // Busca movimento
            $mov = $this->getMovement($conn, $movementId);
            if (!$mov || !$mov['order_id']) {
                throw new Exception('Movimento inválido ou sem pedido associado');
            }
            
            // Busca pedido e itens
            $order = $this->getOrder($conn, $mov['order_id']);
            $items = $this->getOrderItemsWithDetails($conn, $mov['order_id']);
            
            // Devolve estoque
            $this->restoreStock($conn, $items);
            
            // Apaga registros
            $this->deleteMovement($conn, $movementId);
            $this->deleteOrderItems($conn, $mov['order_id']);
            $this->deleteOrder($conn, $mov['order_id']);
            
            $conn->commit();
            
            return [
                'movement' => $mov,
                'order' => $order,
                'items' => $items
            ];
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Reverte venda para Mesa
     * 
     * @throws Exception
     */
    public function reverseToTable(int $movementId, int $restaurantId): int {
        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();
            
            // Busca movimento
            $mov = $this->getMovement($conn, $movementId);
            if (!$mov) {
                throw new Exception('Movimento não encontrado');
            }
            
            // Extrai número da mesa da descrição
            preg_match('/#(\d+)/', $mov['description'], $matches);
            $mesaNumero = $matches[1] ?? null;
            
            if (!$mesaNumero) {
                throw new Exception('Não foi possível identificar o número da mesa');
            }
            
            // Busca mesa
            $mesa = $this->getTableByNumber($conn, $mesaNumero, $restaurantId);
            if (!$mesa) {
                throw new Exception('Mesa não encontrada');
            }
            
            // Reverte status do pedido
            $conn->prepare("UPDATE orders SET status = 'aberto' WHERE id = :oid")
                 ->execute(['oid' => $mov['order_id']]);
            
            // Ocupa a mesa novamente
            $conn->prepare("UPDATE tables SET status = 'ocupada', current_order_id = :oid WHERE id = :tid")
                 ->execute(['oid' => $mov['order_id'], 'tid' => $mesa['id']]);
            
            // Apaga movimento
            $this->deleteMovement($conn, $movementId);
            
            $conn->commit();
            
            return $mesa['id'];
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Remove movimento (cancela venda se necessário)
     * 
     * @throws Exception
     */
    public function removeMovement(int $movementId): void {
        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();
            
            // Busca movimento
            $mov = $this->getMovement($conn, $movementId);
            if (!$mov) {
                throw new Exception('Movimento não encontrado');
            }
            
            // Se for venda, cancela pedido e devolve estoque
            if ($mov['type'] == 'venda' && $mov['order_id']) {
                $items = $this->getOrderItemsSimple($conn, $mov['order_id']);
                $this->restoreStock($conn, $items);
                
                $conn->prepare("UPDATE orders SET status = 'cancelado' WHERE id = :oid")
                     ->execute(['oid' => $mov['order_id']]);
            }
            
            // Apaga movimento
            $this->deleteMovement($conn, $movementId);
            
            $conn->commit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    // ============================================
    // MÉTODOS PRIVADOS
    // ============================================

    private function getMovement($conn, int $id): ?array {
        $stmt = $conn->prepare("SELECT * FROM cash_movements WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getOrder($conn, int $orderId): ?array {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getOrderItemsWithDetails($conn, int $orderId): array {
        $stmt = $conn->prepare("SELECT product_id as id, name, price, quantity FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getOrderItemsSimple($conn, int $orderId): array {
        $stmt = $conn->prepare("SELECT product_id as id, quantity FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTableByNumber($conn, int $number, int $rid): ?array {
        $stmt = $conn->prepare("SELECT id FROM tables WHERE number = :num AND restaurant_id = :rid");
        $stmt->execute(['num' => $number, 'rid' => $rid]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function restoreStock($conn, array $items): void {
        foreach ($items as $item) {
            $conn->prepare("UPDATE products SET stock = stock + :qtd WHERE id = :pid")
                 ->execute(['qtd' => $item['quantity'], 'pid' => $item['id']]);
        }
    }

    private function deleteMovement($conn, int $id): void {
        $conn->prepare("DELETE FROM cash_movements WHERE id = :id")->execute(['id' => $id]);
    }

    private function deleteOrderItems($conn, int $orderId): void {
        $conn->prepare("DELETE FROM order_items WHERE order_id = :oid")->execute(['oid' => $orderId]);
    }

    private function deleteOrder($conn, int $orderId): void {
        $conn->prepare("DELETE FROM orders WHERE id = :oid")->execute(['oid' => $orderId]);
    }
}
