<?php

namespace App\Services\Order\Flows\Mesa;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;
use App\Services\Order\TotalCalculator;
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

            // 4. Inserir novos itens
            $this->itemRepo->insert($orderId, $data['cart']);

            // 5. Atualizar total do pedido
            $this->orderRepo->updateTotal($orderId, $newTotal);

            // 6. Baixar estoque
            foreach ($data['cart'] as $item) {
                $this->stockRepo->decrement($item['id'], $item['quantity']);
            }

            $conn->commit();

            error_log("[MESA] Itens adicionados: Pedido #{$orderId}, +R$ " . number_format($addedValue, 2, ',', '.') . ', Novo Total: R$ ' . number_format($newTotal, 2, ',', '.'));

            return [
                'order_id' => $orderId,
                'items_added' => count($data['cart']),
                'added_value' => $addedValue,
                'new_total' => $newTotal
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            error_log('[MESA] ERRO ao adicionar itens: ' . $e->getMessage());
            throw new RuntimeException('Erro ao adicionar itens: ' . $e->getMessage());
        }
    }
}
