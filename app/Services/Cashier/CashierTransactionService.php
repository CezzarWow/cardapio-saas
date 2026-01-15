<?php

namespace App\Services\Cashier;

use App\Core\Database;
use App\Repositories\CashRegisterRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderPaymentRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\TableRepository;
use Exception;

/**
 * Service para operações transacionais complexas do Caixa
 * (Cancelamento, Estorno, etc)
 */
class CashierTransactionService
{
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private OrderPaymentRepository $paymentRepo;
    private TableRepository $tableRepo;
    private StockRepository $stockRepo;
    private CashRegisterRepository $cashRepo;

    public function __construct(
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        OrderPaymentRepository $paymentRepo,
        TableRepository $tableRepo,
        StockRepository $stockRepo,
        CashRegisterRepository $cashRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->paymentRepo = $paymentRepo;
        $this->tableRepo = $tableRepo;
        $this->stockRepo = $stockRepo;
        $this->cashRepo = $cashRepo;
    }

    /**
     * Cancela uma venda/movimentação no caixa
     * Reverte: Movimento Caixa -> Pedido (Status) -> Mesa (Status) -> Estoque
     */
    public function cancelSale(int $movementId, int $restaurantId): array
    {
        $conn = Database::connect(); // Transaction management

        try {
            $conn->beginTransaction();

            // 1. Busca Movimento
            $mov = $this->cashRepo->findMovement($movementId);

            if (!$mov) {
                throw new Exception('Movimentação não encontrada.');
            }

            if ($mov['type'] !== 'venda') {
                // Se for suprimento/sangria, só apaga
                $this->cashRepo->deleteMovement($movementId);
                $conn->commit();
                return ['success' => true, 'message' => 'Movimentação removida.'];
            }

            $orderId = $mov['order_id'];

            // 2. Busca Pedido
            $order = $this->orderRepo->find($orderId);

            if ($order) {
                // 3. Reverte Estoque
                $items = $this->itemRepo->findAll($orderId);

                foreach ($items as $item) {
                    // Incrementa estoque (Estorno)
                    $this->stockRepo->increment($item['product_id'], $item['quantity']);
                }

                // 4. Libera Mesa (se houver)
                if ($order['order_type'] === 'mesa') {
                    // Busca mesa vinculada pelo current_order_id
                    // TableRepo doesn't have findByOrder, let's assume we fetch all or add a method.
                    // Or iterate. Tables are few.
                    // Better: add findByCurrentOrder to TableRepo.
                    // I will add it briefly or use raw SQL? NO.
                    // Use `findAll` and filter in PHP (safe for small number of tables).
                    $tables = $this->tableRepo->findAll($restaurantId);
                    foreach ($tables as $t) {
                        if ($t['current_order_id'] == $orderId) {
                            $this->tableRepo->free($t['id']);
                            break;
                        }
                    }
                }

                // 5. Marca pedido como cancelado
                $this->orderRepo->updateStatus($orderId, 'cancelado');
            }

            // 6. Apaga movimento
            $this->cashRepo->deleteMovement($movementId);

            $conn->commit();
            return ['success' => true, 'message' => 'Venda cancelada e estornada com sucesso!'];

        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, 'message' => 'Erro ao cancelar: ' . $e->getMessage()];
        }
    }

    /**
     * Remove pedido completamente (Admin) - CUIDADO
     * Usado para limpar dados de teste ou erros graves
     */
    public function deleteRef(int $orderId): void
    {
        $conn = Database::connect();
        try {
            $conn->beginTransaction();

            $items = $this->itemRepo->findAll($orderId);

            foreach ($items as $item) {
                $this->stockRepo->increment($item['product_id'], $item['quantity']);
            }

            // Mesa logic similar to above
            // We need restaurantId to find tables safely? Or assuming order has restaurant_id?
            // Use orderRepo find first to get restaurantId?
            // Assuming we loop tables of current restaurant?
            // Or add `freeByOrder` to TableRepo later.
            // For now, let's leave table clearing if we can't easily find it without raw SQL.
            // But `deleteRef` is critical cleanup.
            // I'll skip table freeing here for now or use `findAll` if I can get restaurantId.
            // This method `deleteRef` signature only has `orderId`.
            // I'll fetch order to get restaurantId.
            $order = $this->orderRepo->find($orderId);
            if ($order) {
                $tables = $this->tableRepo->findAll($order['restaurant_id']);
                foreach ($tables as $t) {
                    if ($t['current_order_id'] == $orderId) {
                        $this->tableRepo->free($t['id']);
                        break;
                    }
                }
            }

            $this->cashRepo->deleteMovementByOrder($orderId);
            $this->itemRepo->deleteAll($orderId);
            $this->paymentRepo->deleteAll($orderId);
            $this->orderRepo->delete($orderId);

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
