<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use Exception;

class RemoveItemAction
{
    private StockRepository $stockRepo;
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;

    public function __construct(
        StockRepository $stockRepo,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo
    ) {
        $this->stockRepo = $stockRepo;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
    }

    public function execute(int $itemId, int $orderId): void
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $item = $this->itemRepo->find($itemId, $orderId);

            if (!$item) {
                throw new Exception('Item não encontrado');
            }

            $currentOrder = $this->orderRepo->find($orderId);
            $valueToDeduct = 0;

            if ($item['quantity'] > 1) {
                $this->itemRepo->updateQuantity($itemId, $item['quantity'] - 1);
                $valueToDeduct = $item['price'];
            } else {
                $this->itemRepo->delete($itemId);
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
