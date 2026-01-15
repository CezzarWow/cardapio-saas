<?php

namespace App\Services\Order\Flows\Comanda;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;
use App\Services\Order\TotalCalculator;
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

            // 5. Inserir novos itens
            $this->itemRepo->insert($orderId, $data['cart']);

            // 6. Atualizar total
            $this->orderRepo->updateTotal($orderId, $newTotal);

            // 7. Baixar estoque
            foreach ($data['cart'] as $item) {
                $this->stockRepo->decrement($item['id'], $item['quantity']);
            }

            $conn->commit();

            error_log("[COMANDA] Itens adicionados: Pedido #{$orderId}, +R$ " . number_format($addedValue, 2, ',', '.'));

            return [
                'order_id' => $orderId,
                'items_added' => count($data['cart']),
                'added_value' => $addedValue,
                'new_total' => $newTotal
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            error_log('[COMANDA] ERRO ao adicionar itens: ' . $e->getMessage());
            throw new RuntimeException('Erro ao adicionar itens: ' . $e->getMessage());
        }
    }
}
