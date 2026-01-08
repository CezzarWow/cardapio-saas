<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Services\StockService;
use PDO;
use Exception;

class CreateOrderAction
{
    private $paymentService;
    private $cashRegisterService;
    private $stockService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->cashRegisterService = new CashRegisterService();
        $this->stockService = new StockService();
    }

    public function execute(int $restaurantId, int $userId, array $data): int
    {
        $conn = Database::connect();
        
        // 1. Validação de Caixa
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            $cart = $data['cart'] ?? [];
            if (empty($cart)) {
                throw new Exception('O carrinho está vazio');
            }

            $orderType = $data['order_type'] ?? 'balcao';
            $tableId = $data['table_id'] ?? null;
            $commandId = $data['command_id'] ?? null;

            if ($orderType === 'mesa') {
                if (!$tableId) throw new Exception('Mesa não identificada');
                // Lógica de mesa ocupada simplificada
            }

            $totalVenda = 0;
            foreach ($cart as $item) {
                $totalVenda += $item['price'] * $item['quantity'];
            }

            $discount = floatval($data['discount'] ?? 0);
            $deliveryFee = floatval($data['delivery_fee'] ?? 0);
            $finalTotal = max(0, $totalVenda + $deliveryFee - $discount);
            
            $isPaid = isset($data['is_paid']) && $data['is_paid'] == 1 ? 1 : 0;
            $paymentMethod = $data['payment_method'] ?? 'dinheiro';
            $payments = $data['payments'] ?? [];
            
            // SAVE_ACCOUNT: Se true, cria como comanda aberta (status 'aberto' aparece em Mesas/Comandas)
            $saveAccount = isset($data['save_account']) && $data['save_account'] == true;
            $orderStatus = $saveAccount ? 'aberto' : 'novo';

            $stmt = $conn->prepare("INSERT INTO orders (restaurant_id, order_type, status, total, created_at, is_paid, payment_method) 
                                   VALUES (:rid, :type, :status, :total, NOW(), :paid, :pay)");
            $stmt->execute([
                'rid' => $restaurantId,
                'type' => $orderType,
                'status' => $orderStatus,
                'total' => $finalTotal,
                'paid' => $isPaid,
                'pay' => $paymentMethod
            ]);
            $orderId = $conn->lastInsertId();

            if (!empty($data['client_id'])) {
                $conn->prepare("UPDATE orders SET client_id = :cid WHERE id = :oid")
                     ->execute(['cid' => $data['client_id'], 'oid' => $orderId]);
            }

            if ($orderType === 'mesa' && $tableId) {
                $conn->prepare("UPDATE tables SET status = 'ocupada', current_order_id = :oid WHERE id = :tid")
                     ->execute(['oid' => $orderId, 'tid' => $tableId]);
            }

            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, quantity, price) VALUES (:oid, :pid, :name, :qtd, :price)");
            
            foreach ($cart as $item) {
                $stmtItem->execute([
                    'oid' => $orderId,
                    'pid' => $item['id'],
                    'name' => $item['name'],
                    'qtd' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Baixa Estoque
                $this->stockService->decrement($conn, $item['id'], $item['quantity']);
            }

            $this->paymentService->registerPayments($conn, $orderId, $payments);

            if ($isPaid == 1 && !empty($payments)) {
                $desc = "Venda " . ucfirst($orderType) . " #" . $orderId;
                $finalAmount = max(0, $totalVenda - $discount);
                
                $this->cashRegisterService->registerMovement(
                    $conn,
                    $caixa['id'],
                    $finalAmount,
                    $desc,
                    $orderId
                );
            }

            $conn->commit();
            return $orderId;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
