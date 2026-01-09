<?php

namespace App\Services;

use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\TableRepository;
use App\Repositories\StockRepository;
use App\Services\CashRegisterService;
use App\Core\Database;
use Exception;

/**
 * SalesService - Lógica de Negócio de Vendas/Histórico
 * 
 * Gerencia listagem de vendas, cancelamento e reativação de mesas.
 */
class SalesService
{
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private TableRepository $tableRepo;
    private StockRepository $stockRepo;
    private CashRegisterService $cashService;

    public function __construct(
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        TableRepository $tableRepo,
        StockRepository $stockRepo,
        CashRegisterService $cashService
    ) {
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->tableRepo = $tableRepo;
        $this->stockRepo = $stockRepo;
        $this->cashService = $cashService;
    }

    /**
     * Lista todas as vendas do restaurante
     */
    public function listOrders(int $restaurantId): array
    {
        return $this->orderRepo->findAllWithDetails($restaurantId);
    }

    /**
     * Busca itens de um pedido
     */
    public function getOrderItems(int $orderId): array
    {
        return $this->itemRepo->findAll($orderId);
    }

    /**
     * Cancela uma venda: estorna estoque e caixa
     */
    public function cancelOrder(int $orderId): array
    {
        $conn = Database::connect(); // Transaction management

        try {
            $conn->beginTransaction();

            // 1. Verifica o Pedido
            // Repository find returns array, check status manually or add findCompleted to repo.
            // Using find and checking status.
            $order = $this->orderRepo->find($orderId);

            if (!$order || $order['status'] !== 'concluido') {
                throw new Exception("Pedido não encontrado ou já cancelado.");
            }

            // 2. Devolve Estoque
            $items = $this->itemRepo->findAll($orderId);

            foreach ($items as $item) {
                // StockService uses increment/decrement. StockRepo uses increment.
                // Using StockRepo directly.
                $this->stockRepo->increment($item['product_id'], $item['quantity']);
            }

            // 3. Estorna o Caixa
            $this->cashService->deleteByOrderId($conn, $orderId);

            // 4. Marca como Cancelado
            $this->orderRepo->updateStatus($orderId, 'cancelado');

            $conn->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reativa mesa: volta status para aberto, ocupa mesa novamente
     */
    public function reactivateTable(int $orderId, int $restaurantId): array
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Busca movimento do caixa para identificar mesa
            $mov = $this->cashService->findByOrderId($conn, $orderId);

            if (!$mov) {
                throw new Exception("Pagamento não encontrado no caixa.");
            }

            // Extrai número da mesa da descrição "Pagamento Mesa #5"
            preg_match('/#(\d+)/', $mov['description'], $matches);
            $tableNum = $matches[1] ?? null;

            if (!$tableNum) {
                throw new Exception("Não foi possível identificar a mesa.");
            }

            // 2. Verifica se mesa está livre
            $table = $this->tableRepo->findByNumber($restaurantId, $tableNum);

            if (!$table) {
                throw new Exception("Mesa não encontrada.");
            }

            if ($table['status'] === 'ocupada') {
                throw new Exception("A Mesa $tableNum já está ocupada por outro cliente!");
            }

            // 3. Reverte tudo
            $this->orderRepo->updateStatus($orderId, 'aberto');
            $this->tableRepo->occupy($table['id'], $orderId);
            $this->cashService->deleteByOrderId($conn, $orderId);

            $conn->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
