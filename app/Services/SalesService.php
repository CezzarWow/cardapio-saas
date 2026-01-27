<?php

namespace App\Services;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\TableRepository;
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
     * Lista todas as vendas do restaurante (sem paginação)
     */
    public function listOrders(int $restaurantId): array
    {
        return $this->orderRepo->findAllWithDetails($restaurantId);
    }

    /**
     * Lista vendas paginadas (ETAPA 5).
     *
     * @return array{items: array, total: int, page: int, per_page: int, total_pages: int}
     */
    public function listOrdersPaginated(int $restaurantId, int $page = 1, int $perPage = 20): array
    {
        return $this->orderRepo->findAllWithDetailsPaginated($restaurantId, $page, $perPage);
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
                throw new Exception('Pedido não encontrado ou já cancelado.');
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
     * Reativa mesa: recria pedido com status aberto, ocupa mesa novamente.
     *
     * NOTA: Transição concluido→aberto é inválida no modelo de estados.
     * Esta função recria o pedido com status 'aberto' ao invés de transitar.
     */
    public function reactivateTable(int $orderId, int $restaurantId): array
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Busca movimento do caixa para identificar mesa
            $mov = $this->cashService->findByOrderId($conn, $orderId);

            if (!$mov) {
                throw new Exception('Pagamento não encontrado no caixa.');
            }

            // 2. Buscar pedido original para restaurar dados
            $originalOrder = $this->orderRepo->find($orderId);

            if (!$originalOrder || $originalOrder['status'] !== 'concluido') {
                throw new Exception('Pedido não está concluído ou não existe.');
            }

            // Extrai número da mesa da descrição "Pagamento Mesa #5"
            preg_match('/#(\d+)/', $mov['description'], $matches);
            $tableNum = $matches[1] ?? null;

            if (!$tableNum) {
                throw new Exception('Não foi possível identificar a mesa.');
            }

            // 3. Verifica se mesa está livre
            $table = $this->tableRepo->findByNumber($restaurantId, $tableNum);

            if (!$table) {
                throw new Exception('Mesa não encontrada.');
            }

            if ($table['status'] === 'ocupada') {
                throw new Exception("A Mesa $tableNum já está ocupada por outro cliente!");
            }

            // 4. Buscar itens do pedido original para restaurar
            $items = $this->itemRepo->findAll($orderId);

            // 5. Estornar caixa do pedido antigo
            $this->cashService->deleteByOrderId($conn, $orderId);

            // 6. Criar novo pedido com status 'aberto' (não transitamos concluido→aberto)
            $newOrderId = $this->orderRepo->create([
                'restaurant_id' => $restaurantId,
                'client_id' => $originalOrder['client_id'],
                'total' => $originalOrder['total'],
                'order_type' => 'mesa',
                'payment_method' => 'dinheiro',
                'observation' => $originalOrder['observation'],
                'change_for' => null
            ], 'aberto'); // Status inicial = 'aberto'

            // 7. Copiar itens para o novo pedido
            foreach ($items as $item) {
                $this->itemRepo->add($newOrderId, [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'] ?? 'Produto',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'observation' => $item['observation'] ?? null
                ]);
            }

            // 8. Ocupar mesa com novo pedido
            $this->tableRepo->occupy($table['id'], $newOrderId);

            // 9. Marcar pedido antigo como cancelado (se desejar manter histórico)
            // Ou deletar (mais limpo)
            $this->orderRepo->delete($orderId);

            $conn->commit();
            return ['success' => true, 'new_order_id' => $newOrderId];

        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
