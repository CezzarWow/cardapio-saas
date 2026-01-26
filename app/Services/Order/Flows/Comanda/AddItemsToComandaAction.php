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

    public function __construct(
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        StockRepository $stockRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->stockRepo = $stockRepo;
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

        // 4. Calcular valor dos novos itens
        $addedValue = TotalCalculator::fromCart($data['cart'], 0);
        $newTotal = floatval($order['total']) + $addedValue;

        try {
            $conn->beginTransaction();

            // 5. Inserir novos itens e baixar estoque
            $this->insertItemsAndDecrementStock($orderId, $data['cart'], $this->itemRepo, $this->stockRepo);

            // 6. Atualizar total
            $this->orderRepo->updateTotal($orderId, $newTotal);

            $conn->commit();

            Logger::info("[COMANDA] Itens adicionados: Pedido #{$orderId}", [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId,
                'items_added' => count($data['cart']),
                'added_value' => $addedValue,
                'new_total' => $newTotal
            ]);

            return [
                'order_id' => $orderId,
                'items_added' => count($data['cart']),
                'added_value' => $addedValue,
                'new_total' => $newTotal
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
