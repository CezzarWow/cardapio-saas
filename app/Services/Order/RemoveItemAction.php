<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\StockService;
use PDO;
use Exception;

class RemoveItemAction
{
    private $stockService;

    public function __construct()
    {
        $this->stockService = new StockService();
    }

    public function execute(int $itemId, int $orderId): void
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $stmtItem = $conn->prepare("SELECT product_id, quantity, price FROM order_items WHERE id = :id AND order_id = :oid");
            $stmtItem->execute(['id' => $itemId, 'oid' => $orderId]);
            $item = $stmtItem->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw new Exception('Item não encontrado');
            }

            $valueToDeduct = 0;

            if ($item['quantity'] > 1) {
                $conn->prepare("UPDATE order_items SET quantity = quantity - 1 WHERE id = :id")->execute(['id' => $itemId]);
                $valueToDeduct = $item['price'];
            } else {
                $conn->prepare("DELETE FROM order_items WHERE id = :id")->execute(['id' => $itemId]);
                $valueToDeduct = $item['price'];
            }

            $this->stockService->increment($conn, $item['product_id'], 1);

            $conn->prepare("UPDATE orders SET total = GREATEST(0, total - :val) WHERE id = :oid")
                 ->execute(['val' => $valueToDeduct, 'oid' => $orderId]);

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
