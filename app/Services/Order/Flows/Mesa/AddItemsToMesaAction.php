<?php

namespace App\Services\Order\Flows\Mesa;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;
use App\Services\Order\TotalCalculator;
use App\Traits\OrderCreationTrait;
use Exception;
use RuntimeException;

/**
 * Action: Adicionar Itens à Mesa
 *
 * Fluxo ISOLADO para adicionar itens a conta aberta.
 *
 * Responsabilidades:
 * - Validar pedido existe e está ABERTO
 * - Inserir novos itens
 * - Atualizar total
 * - Baixar estoque
 *
 * NÃO FAZ:
 * - Processar pagamento
 * - Fechar mesa
 * - Abrir mesa nova
 */
class AddItemsToMesaAction
{
    use OrderCreationTrait;

    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private StockRepository $stockRepo;
    private OrderTotalService $totalService;

    public function __construct(
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        StockRepository $stockRepo,
        OrderTotalService $totalService
    ) {
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->stockRepo = $stockRepo;
        $this->totalService = $totalService;
    }

    /**
     * Adiciona itens a conta aberta
     *
     * @param int $restaurantId ID do restaurante
     * @param array $data Payload validado
     * @return array ['order_id' => int, 'items_added' => int, 'new_total' => float]
     * @throws Exception Se pedido não encontrado ou não está aberto
     */
    public function execute(int $restaurantId, array $data): array
    {
        $conn = Database::connect();
        $orderId = intval($data['order_id']);

        // 1. Buscar pedido existente
        $order = $this->orderRepo->find($orderId, $restaurantId);

        if (!$order) {
            throw new Exception("Pedido #{$orderId} não encontrado");
        }

        // 2. Validar status ABERTO
        if ($order['status'] !== OrderStatus::ABERTO) {
            throw new Exception(
                "Pedido #{$orderId} não está aberto. Status atual: {$order['status']}"
            );
        }

        // 3. Forçar source_type 'comanda'
        foreach ($data['cart'] as &$item) {
            $item['source_type'] = 'comanda';
        }
        unset($item);

        try {
            $conn->beginTransaction();

            // 4. Inserir novos itens e baixar estoque
            $this->insertItemsAndDecrementStock($orderId, $data['cart'], $this->itemRepo, $this->stockRepo);

            // 5. Recalcular Totais (Fonte Única de Verdade)
            $totals = $this->totalService->recalculate($orderId);
            $newTotal = $totals['total'];

            $conn->commit();

            Logger::info("[MESA] Itens adicionados: Pedido #{$orderId}", [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'items_added' => count($data['cart']),
                'new_total' => $totals['total'],
                'total_table' => $totals['total_table']
            ]);

            return [
                'order_id' => $orderId,
                'items_added' => count($data['cart']),
                'new_total' => $totals['total'],
                'total_table' => $totals['total_table'],
                'total_delivery' => $totals['total_delivery']
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            $this->logOrderError('MESA', 'adicionar itens', $e, [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId
            ]);
            throw new RuntimeException('Erro ao adicionar itens: ' . $e->getMessage());
        }
    }
}
