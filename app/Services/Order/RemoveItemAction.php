<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Repositories\StockRepository;
use App\Repositories\Order\OrderRepository;
use Exception;

class RemoveItemAction
{
    private StockRepository $stockRepo;
    private OrderRepository $orderRepo;

    public function __construct(StockRepository $stockRepo, OrderRepository $orderRepo)
    {
        $this->stockRepo = $stockRepo;
        $this->orderRepo = $orderRepo;
    }

    public function execute(int $itemId, int $orderId): void
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $item = $this->orderRepo->findItem($itemId, $orderId);

            if (!$item) {
                throw new Exception('Item não encontrado');
            }

            $currentOrder = $this->orderRepo->find($orderId);
            $valueToDeduct = 0;

            if ($item['quantity'] > 1) {
                $this->orderRepo->updateItemQuantity($itemId, $item['quantity'] - 1);
                $valueToDeduct = $item['price'];
            } else {
                $this->orderRepo->deleteItem($itemId);
                $valueToDeduct = $item['price'];
            }

            $this->stockRepo->increment($item['product_id'], 1);

            $newTotal = max(0, floatval($currentOrder['total']) - $valueToDeduct);
            $this->orderRepo->updateTotal($orderId, $newTotal);

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
