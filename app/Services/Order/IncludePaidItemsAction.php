<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\PaymentService;
use App\Services\StockService;
use PDO;
use Exception;

class IncludePaidItemsAction
{
    private $paymentService;
    private $stockService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->stockService = new StockService();
    }

    public function execute(int $orderId, array $cart, array $payments, int $restaurantId): float
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("SELECT total FROM orders WHERE id = :oid");
            $stmt->execute(['oid' => $orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) throw new Exception("Pedido não encontrado");

            $newTotal = 0;

            foreach ($cart as $item) {
                $qty = intval($item['quantity'] ?? 1);
                $price = floatval($item['price'] ?? 0);
                $itemTotal = $qty * $price;
                $newTotal += $itemTotal;
                
                $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:oid, :pid, :qty, :price)")
                     ->execute([
                         'oid' => $orderId,
                         'pid' => $item['id'],
                         'qty' => $qty,
                         'price' => $price
                     ]);

                $this->stockService->decrement($conn, $item['id'], $qty);
            }

            $this->paymentService->registerPayments($conn, $orderId, $payments);

            $updatedTotal = floatval($order['total']) + $newTotal;
            $conn->prepare("UPDATE orders SET total = :total WHERE id = :oid")
                 ->execute(['total' => $updatedTotal, 'oid' => $orderId]);

            if (!empty($payments)) {
                $paymentTotal = array_sum(array_column($payments, 'amount'));
                $conn->prepare("INSERT INTO cash_movements (restaurant_id, type, amount, description, date, order_id) VALUES (:rid, 'entrada', :amount, :desc, NOW(), :oid)")
                     ->execute([
                         'rid' => $restaurantId,
                         'amount' => $paymentTotal,
                         'desc' => 'Inclusão Pedido #' . $orderId,
                         'oid' => $orderId
                     ]);
            }

            $conn->commit();
            return $updatedTotal;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
