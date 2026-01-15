<?php

namespace App\Services\Order\Flows\Comanda;

use App\Core\Database;
use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;
use App\Services\Order\TotalCalculator;
use Exception;
use RuntimeException;

/**
 * Action: Abrir Comanda
 *
 * Fluxo ISOLADO para criar comanda vinculada a cliente.
 *
 * Responsabilidades:
 * - Validar cliente existe
 * - Criar pedido com status ABERTO
 * - Vincular cliente ao pedido
 * - Inserir itens iniciais
 * - Baixar estoque
 *
 * NÃO FAZ:
 * - Ocupar mesa (não é fluxo Mesa)
 * - Processar pagamento
 * - Fechar comanda
 */
class OpenComandaAction
{
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private ClientRepository $clientRepo;
    private StockRepository $stockRepo;

    public function __construct(
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        ClientRepository $clientRepo,
        StockRepository $stockRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->clientRepo = $clientRepo;
        $this->stockRepo = $stockRepo;
    }

    /**
     * Abre comanda para cliente
     *
     * @param int $restaurantId ID do restaurante
     * @param array $data Payload validado
     * @return array ['order_id' => int, 'total' => float, 'client_id' => int]
     * @throws Exception Se cliente não encontrado
     */
    public function execute(int $restaurantId, array $data): array
    {
        $conn = Database::connect();
        $clientId = intval($data['client_id']);

        // 1. Verificar se cliente existe
        $client = $this->clientRepo->find($clientId, $restaurantId);

        if (empty($client)) {
            throw new Exception("Cliente #{$clientId} não encontrado");
        }

        // 2. Calcular total inicial
        $total = TotalCalculator::fromCart($data['cart'], 0);

        try {
            $conn->beginTransaction();

            // 3. Criar pedido com status ABERTO vinculado ao cliente
            $orderId = $this->orderRepo->create([
                'restaurant_id' => $restaurantId,
                'client_id' => $clientId,
                'total' => $total,
                'order_type' => 'comanda',
                'observation' => $data['observation'] ?? null,
                'change_for' => null
            ], OrderStatus::ABERTO);

            // 4. Inserir itens
            $this->itemRepo->insert($orderId, $data['cart']);

            // 5. Baixar estoque
            foreach ($data['cart'] as $item) {
                $this->stockRepo->decrement($item['id'], $item['quantity']);
            }

            $conn->commit();

            $clientName = $client['name'] ?? 'Cliente';
            error_log("[COMANDA] Comanda aberta: Cliente '{$clientName}' (#{$clientId}), Pedido #{$orderId}, Total: R$ " . number_format($total, 2, ',', '.'));

            return [
                'order_id' => $orderId,
                'total' => $total,
                'client_id' => $clientId,
                'client_name' => $clientName
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            error_log('[COMANDA] ERRO ao abrir: ' . $e->getMessage());
            throw new RuntimeException('Erro ao abrir comanda: ' . $e->getMessage());
        }
    }
}
