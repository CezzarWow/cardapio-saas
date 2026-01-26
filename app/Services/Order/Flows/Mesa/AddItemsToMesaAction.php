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

        // 3. Calcular valor dos novos itens
        $addedValue = TotalCalculator::fromCart($data['cart'], 0);
        $newTotal = floatval($order['total']) + $addedValue;

        try {
            $conn->beginTransaction();

            // 4. Inserir novos itens e baixar estoque
            $this->insertItemsAndDecrementStock($orderId, $data['cart'], $this->itemRepo, $this->stockRepo);

            // 5. Atualizar total do pedido
            $this->orderRepo->updateTotal($orderId, $newTotal);

            $conn->commit();

            Logger::info("[MESA] Itens adicionados: Pedido #{$orderId}", [
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
            $this->logOrderError('MESA', 'adicionar itens', $e, [
                'restaurant_id' => $restaurantId,
                'order_id' => $orderId
            ]);
            throw new RuntimeException('Erro ao adicionar itens: ' . $e->getMessage());
        }
    }
}
