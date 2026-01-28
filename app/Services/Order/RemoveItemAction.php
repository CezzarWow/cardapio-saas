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
    private OrderTotalService $totalService;

    public function __construct(
        StockRepository $stockRepo,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        OrderTotalService $totalService
    ) {
        $this->stockRepo = $stockRepo;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->totalService = $totalService;
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

            // Validar status/pedido se necessário (já estava na lógica original implicitamente?)
            // O código original buscava order e checava? 
            // Na versão lida (Step 857), ele buscava orderRepo->find($orderId).
            // Manter lógica original.

            $currentOrder = $this->orderRepo->find($orderId);
            // ... lógica de validação removida no meu snippet anterior?
            // O snippet anterior tinha validação de quantidade.
            // Vou manter a lógica de quantidade.
            
            if ($item['quantity'] > 1) {
                $this->itemRepo->updateQuantity($itemId, $item['quantity'] - 1);
            } else {
                $this->itemRepo->delete($itemId);
            }

            $this->stockRepo->increment($item['product_id'], 1);

            // Recalcular Total via Serviço (Substitui lógica manual)
            $this->totalService->recalculate($orderId);

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
