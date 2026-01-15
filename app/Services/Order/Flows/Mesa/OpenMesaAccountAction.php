<?php

namespace App\Services\Order\Flows\Mesa;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\TableRepository;
use App\Services\Order\OrderStatus;
use App\Services\Order\TotalCalculator;
use Exception;
use RuntimeException;

/**
 * Action: Abrir Conta de Mesa
 *
 * Fluxo ISOLADO para criar conta aberta em mesa.
 *
 * Responsabilidades:
 * - Validar mesa disponível
 * - Criar pedido com status ABERTO
 * - Inserir itens iniciais
 * - Ocupar mesa
 * - Baixar estoque
 *
 * NÃO FAZ:
 * - Processar pagamento (isso é no fechamento)
 * - Fechar mesa
 * - Qualquer operação de Balcão/Comanda/Delivery
 */
class OpenMesaAccountAction
{
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private TableRepository $tableRepo;
    private StockRepository $stockRepo;

    public function __construct(
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        TableRepository $tableRepo,
        StockRepository $stockRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->tableRepo = $tableRepo;
        $this->stockRepo = $stockRepo;
    }

    /**
     * Abre conta de mesa
     *
     * @param int $restaurantId ID do restaurante
     * @param array $data Payload validado
     * @return array ['order_id' => int, 'total' => float, 'table_id' => int]
     * @throws RuntimeException Se mesa ocupada ou erro na transação
     */
    public function execute(int $restaurantId, array $data): array
    {
        $conn = Database::connect();
        $tableId = intval($data['table_id']);

        // 1. Verificar se mesa existe e está disponível
        $mesa = $this->tableRepo->findWithCurrentOrder($tableId, $restaurantId);

        if (empty($mesa)) {
            throw new Exception("Mesa #{$tableId} não encontrada");
        }

        if (!empty($mesa['current_order_id'])) {
            throw new Exception("Mesa #{$mesa['number']} já está ocupada");
        }

        // 2. Calcular total inicial
        $total = TotalCalculator::fromCart($data['cart'], 0);

        try {
            $conn->beginTransaction();

            // 3. Criar pedido com status ABERTO
            $orderId = $this->orderRepo->create([
                'restaurant_id' => $restaurantId,
                'client_id' => null,
                'total' => $total,
                'order_type' => 'mesa',
                'observation' => $data['observation'] ?? null,
                'change_for' => null
            ], OrderStatus::ABERTO); // Status inicial = aberto

            // 4. Ocupar mesa
            $this->tableRepo->occupy($tableId, $orderId);

            // 5. Inserir itens
            $this->itemRepo->insert($orderId, $data['cart']);

            // 6. Baixar estoque
            foreach ($data['cart'] as $item) {
                $this->stockRepo->decrement($item['id'], $item['quantity']);
            }

            $conn->commit();

            error_log("[MESA] Conta aberta: Mesa #{$mesa['number']}, Pedido #{$orderId}, Total: R$ " . number_format($total, 2, ',', '.'));

            return [
                'order_id' => $orderId,
                'total' => $total,
                'table_id' => $tableId,
                'table_number' => $mesa['number']
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            error_log('[MESA] ERRO ao abrir: ' . $e->getMessage());
            throw new RuntimeException('Erro ao abrir mesa: ' . $e->getMessage());
        }
    }
}
