<?php

namespace App\Services\Order;

use App\Core\Database;
use PDO;
use Exception;

class DeliverOrderAction
{
    public function execute(int $orderId, int $restaurantId): void
    {
        $conn = Database::connect();
        try {
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :oid AND restaurant_id = :rid");
            $stmt->execute(['oid' => $orderId, 'rid' => $restaurantId]);
            if (!$stmt->fetch()) throw new Exception('Pedido não encontrado');

            $conn->prepare("UPDATE orders SET status = 'concluido' WHERE id = :oid")
                 ->execute(['oid' => $orderId]);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
