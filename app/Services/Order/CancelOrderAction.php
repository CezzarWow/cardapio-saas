<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\StockService;
use PDO;
use Exception;

class CancelOrderAction
{
    private $stockService;

    public function __construct()
    {
        $this->stockService = new StockService();
    }

    public function execute(int $orderId, ?int $tableId = null): void
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $stmtItems = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :oid");
            $stmtItems->execute(['oid' => $orderId]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $this->stockService->increment($conn, $item['product_id'], $item['quantity']);
            }

            $conn->prepare("DELETE FROM order_items WHERE order_id = :oid")->execute(['oid' => $orderId]);
            $conn->prepare("DELETE FROM orders WHERE id = :oid")->execute(['oid' => $orderId]);

            if ($tableId) {
                $conn->prepare("UPDATE tables SET status = 'livre', current_order_id = NULL WHERE id = :tid")
                     ->execute(['tid' => $tableId]);
            }

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
