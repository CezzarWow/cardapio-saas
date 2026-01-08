<?php
namespace App\Services\Pdv;

use App\Core\Database;
use PDO;
use Exception;

/**
 * PdvService - Lógica de Negócio do PDV (Frente de Caixa)
 */
class PdvService {

    /**
     * Busca dados do contexto atual (Mesa ou Comanda Aberta)
     */
    public function getContextData(int $restaurantId, ?int $mesaId, ?int $orderId): array {
        $conn = Database::connect();
        $contaAberta = null;
        $itensJaPedidos = [];
        $isComanda = false;
        
        // 1. Se for Mesa
        if ($mesaId) {
            $stmtMesa = $conn->prepare("SELECT * FROM tables WHERE id = :tid AND restaurant_id = :rid");
            $stmtMesa->execute(['tid' => $mesaId, 'rid' => $restaurantId]);
            $mesaDados = $stmtMesa->fetch(PDO::FETCH_ASSOC);

            if ($mesaDados && $mesaDados['status'] == 'ocupada' && $mesaDados['current_order_id']) {
                $contaAberta = $this->getOrderData($conn, $mesaDados['current_order_id']);
                $itensJaPedidos = $this->getOrderItems($conn, $mesaDados['current_order_id']);
                
                // Recálculo do total real
                $contaAberta['total'] = $this->calculateTotal($itensJaPedidos);
            }
        }
        // 2. Se for Comanda (Order ID direto)
        elseif ($orderId) {
            $stmtOrder = $conn->prepare("SELECT o.*, c.name as client_name, c.id as client_id 
                                       FROM orders o 
                                       JOIN clients c ON o.client_id = c.id 
                                       WHERE o.id = :oid AND o.restaurant_id = :rid AND o.status = 'aberto'");
            $stmtOrder->execute(['oid' => $orderId, 'rid' => $restaurantId]);
            $contaAberta = $stmtOrder->fetch(PDO::FETCH_ASSOC);

            if ($contaAberta) {
                $itensJaPedidos = $this->getOrderItems($conn, $orderId);
                $contaAberta['total'] = $this->calculateTotal($itensJaPedidos);
                $isComanda = true;
            }
        }
        
        return [
            'contaAberta' => $contaAberta,
            'itensJaPedidos' => $itensJaPedidos,
            'isComanda' => $isComanda
        ];
    }

    /**
     * Busca Menu (Categorias e Produtos) para o PDV
     */
    public function getMenu(int $restaurantId): array {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY ordem ASC");
        $stmt->execute(['rid' => $restaurantId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categories as &$cat) {
            // Busca produtos ativos com flag de adicionais otimizada
            $stmtProd = $conn->prepare("
                SELECT p.*, 
                       (SELECT 1 FROM product_additional_relations par WHERE par.product_id = p.id LIMIT 1) as has_extras
                FROM products p 
                WHERE p.category_id = :cid AND p.is_active = 1
            ");
            $stmtProd->execute(['cid' => $cat['id']]);
            $products = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
            
            // Cast has_extras para bool (MySQL retorna 1/0/null)
            foreach ($products as &$p) {
                $p['has_extras'] = (bool) $p['has_extras'];
            }
            
            $cat['products'] = $products;
        }
        
        return $categories;
    }

    /**
     * Restaura um pedido cancelado (Desfaz a edição)
     */
    public function restoreOrder(array $backup): void {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Restaura Order
            $this->restoreOrderHeader($conn, $backup['order']);

            // 2. Restaura Itens e Estoque
            $this->restoreDetails($conn, $backup['order']['id'], $backup['items']);

            // 3. Restaura Movimento financeiro
            $this->restoreMovement($conn, $backup['order']['id'], $backup['movement']);

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw new Exception("Erro ao restaurar backup: " . $e->getMessage());
        }
    }

    // --- Helpers Privados ---

    private function getOrderData($conn, int $orderId): ?array {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getOrderItems($conn, int $orderId): array {
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function calculateTotal(array $items): float {
        $total = 0;
        foreach ($items as $item) {
            $total += ($item['price'] * $item['quantity']);
        }
        return $total;
    }

    private function restoreOrderHeader($conn, array $order): void {
        $stmt = $conn->prepare("INSERT INTO orders (id, restaurant_id, total, status, payment_method, created_at) VALUES (:id, :rid, :total, :status, :pay, :date)");
        $stmt->execute([
            'id' => $order['id'],
            'rid' => $order['restaurant_id'],
            'total' => $order['total'],
            'status' => $order['status'],
            'pay' => $order['payment_method'],
            'date' => $order['created_at']
        ]);
    }

    private function restoreDetails($conn, int $orderId, array $items): void {
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, quantity, price) VALUES (:oid, :pid, :name, :qtd, :price)");
        $stmtStock = $conn->prepare("UPDATE products SET stock = stock - :qtd WHERE id = :pid");

        foreach ($items as $item) {
            $stmtItem->execute([
                'oid' => $orderId,
                'pid' => $item['id'],
                'name' => $item['name'],
                'qtd' => $item['quantity'],
                'price' => $item['price']
            ]);
            $stmtStock->execute(['qtd' => $item['quantity'], 'pid' => $item['id']]);
        }
    }

    private function restoreMovement($conn, int $orderId, array $mov): void {
        $stmt = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, :type, :amount, :desc, :oid, :date)");
        $stmt->execute([
            'cid' => $mov['cash_register_id'],
            'type' => $mov['type'],
            'amount' => $mov['amount'],
            'desc' => $mov['description'],
            'oid' => $orderId,
            'date' => $mov['created_at']
        ]);
    }
}
