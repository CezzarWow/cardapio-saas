<?php

namespace App\Services\Order\Flows\Comanda;

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
 * Action: Adicionar Itens à Comanda
 *
 * Fluxo ISOLADO para adicionar itens a comanda aberta.
 *
 * Responsabilidades:
 * - Validar pedido existe e está ABERTO
 * - Inserir novos itens
 * - Atualizar total
 * - Baixar estoque
 */
class AddItemsToComandaAction
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
     * Adiciona itens a comanda aberta
     */
    public function execute(int $restaurantId, array $data): array
    {
        $conn = Database::connect();
        $orderId = intval($data['order_id']);

        // 1. Buscar pedido existente
        $order = $this->orderRepo->find($orderId, $restaurantId);

        if (!$order) {
            throw new Exception("Comanda #{$orderId} não encontrada");
        }

        // 2. Validar status ABERTO
        if ($order['status'] !== OrderStatus::ABERTO) {
            throw new Exception(
                "Comanda #{$orderId} não está aberta. Status: {$order['status']}"
            );
        }

        // 3. Validar order_type = comanda
        if ($order['order_type'] !== 'comanda') {
            throw new Exception(
                "Pedido #{$orderId} não é uma comanda"
            );
        }

        // 4. Forçar source_type 'comanda' se não vier
        foreach ($data['cart'] as &$item) {
             if (empty($item['source_type'])) {
                 $item['source_type'] = 'comanda';
             }
        }
        unset($item);

        try {
            $conn->beginTransaction();

            // 5. Inserir novos itens e baixar estoque
            $this->insertItemsAndDecrementStock($orderId, $data['cart'], $this->itemRepo, $this->stockRepo);

            // 6. Recalcular Totais
            $totals = $this->totalService->recalculate($orderId);

            $conn->commit();

            Logger::info("[COMANDA] Itens adicionados: Pedido #{$orderId}", [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'items_added' => count($data['cart']),
                'new_total' => $totals['total']
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
            $this->logOrderError('COMANDA', 'adicionar itens', $e, [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId
            ]);
            throw new RuntimeException('Erro ao adicionar itens: ' . $e->getMessage());
        }
    }
}
