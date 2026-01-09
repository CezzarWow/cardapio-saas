<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\Stock\StockService;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\TableRepository;
use PDO;
use Exception;

class CancelOrderAction
{
    private StockService $stockService;
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private TableRepository $tableRepo;

    public function __construct(
        StockService $stockService,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        TableRepository $tableRepo
    ) {
        $this->stockService = $stockService;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->tableRepo = $tableRepo;
    }

    public function execute(int $orderId, ?int $tableId = null): void
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $items = $this->itemRepo->findAll($orderId);

            foreach ($items as $item) {
                // StockService ainda precisa de connection para Transaction?
                // Sim, ele usa $conn passado. Idealmente refatorar Stock para Repo também no futuro.
                $this->stockService->increment($conn, $item['product_id'], $item['quantity']);
            }

            $this->itemRepo->deleteAll($orderId);
            $this->orderRepo->delete($orderId);

            if ($tableId) {
                $this->tableRepo->free($tableId);
            }

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
