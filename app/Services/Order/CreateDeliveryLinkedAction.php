<?php

namespace App\Services\Order;

use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\TableRepository;
use Exception;

/**
 * Cria pedido de entrega vinculado a uma conta existente (mesa ou comanda de cliente).
 * 
 * O pedido de delivery vai para o Kanban normalmente, mas a cobrança
 * aparece como item na conta da mesa/comanda para pagamento posterior.
 * 
 * ⚠️ REGRA CRÍTICA (implementation_plan.md Seção 0):
 * Apenas adiciona item financeiro (order_items) e atualiza total.
 * O status da comanda só muda via CloseCommandAction.
 * 
 * @deprecated Este fluxo está sendo substituído pelo fluxo isolado de Delivery.
 * Não utilizar em novos códigos.
 */
class CreateDeliveryLinkedAction
{
    private $createOrderAction;
    private $tableRepo;
    private $itemRepo;
    private $orderRepo;

    public function __construct(
        CreateOrderAction $createOrderAction,
        TableRepository $tableRepo,
        OrderItemRepository $itemRepo,
        OrderRepository $orderRepo
    ) {
        $this->createOrderAction = $createOrderAction;
        $this->tableRepo = $tableRepo;
        $this->itemRepo = $itemRepo;
        $this->orderRepo = $orderRepo;
    }

    /**
     * Executa a criação de delivery vinculado.
     * 
     * @param int $restaurantId
     * @param int $userId
     * @param array $data
     * @return int ID do pedido de delivery criado
     */
    public function execute(int $restaurantId, int $userId, array $data): int
    {
        // 1. Criar o pedido de entrega/retirada normalmente (como pedido NOVO, não incremento)
        $deliveryData = $data;
        $deliveryData['table_id'] = null;
        // Mantém o order_type original (delivery ou pickup)
        // $deliveryData['order_type'] já vem definido corretamente do frontend
        $deliveryData['order_id'] = null;         // Forçar criação de novo pedido
        $deliveryData['save_account'] = false;    // Não salvar como comanda
        $deliveryData['link_to_comanda'] = false; // Evitar loop
        $deliveryData['link_to_table'] = false;   // Evitar loop

        $deliveryOrderId = $this->createOrderAction->execute($restaurantId, $userId, $deliveryData);

        // 2. Determinar onde vincular: Mesa ou Comanda de Cliente
        $linkedOrderId = $this->getLinkedOrderId($restaurantId, $data);

        if (!$linkedOrderId) {
            // Sem vinculação - retorna apenas o delivery criado
            return $deliveryOrderId;
        }

        // 3. Calcular valor total a cobrar
        $totalToCharge = $this->calculateTotal($data);

        // 4. Adicionar item na conta vinculada representando a entrega
        // ⚠️ Apenas adiciona ITEM, não altera STATUS da comanda
        $this->addDeliveryItem($linkedOrderId, $deliveryOrderId, $data, $totalToCharge);

        // 5. Atualizar TOTAL do pedido vinculado (valor financeiro)
        // ⚠️ Apenas atualiza TOTAL, não altera STATUS da comanda
        $currentOrder = $this->orderRepo->find($linkedOrderId);
        $currentTotal = floatval($currentOrder['total'] ?? 0);
        $this->orderRepo->updateTotal($linkedOrderId, $currentTotal + $totalToCharge);

        return $deliveryOrderId;
    }

    /**
     * Determina o ID do pedido onde vincular a entrega.
     */
    private function getLinkedOrderId(int $restaurantId, array $data): ?int
    {
        // Prioridade 1: Mesa
        if (!empty($data['table_id'])) {
            return $this->getOrCreateTableOrder($restaurantId, $data['table_id']);
        }

        // Prioridade 2: Comanda de Cliente
        if (!empty($data['link_to_comanda']) && !empty($data['client_id'])) {
            return $this->getOrCreateClientComanda($restaurantId, $data['client_id']);
        }

        return null;
    }

    /**
     * Busca ou cria pedido da mesa.
     * 
     * Se criar novo pedido, cria diretamente com status 'aberto'.
     */
    private function getOrCreateTableOrder(int $restaurantId, int $tableId): int
    {
        $table = $this->tableRepo->findWithCurrentOrder($tableId, $restaurantId);

        if ($table && !empty($table['current_order_id'])) {
            return $table['current_order_id'];
        }

        // Criar novo pedido de mesa diretamente com status 'aberto'
        $orderId = $this->orderRepo->create([
            'restaurant_id' => $restaurantId,
            'client_id' => null,
            'total' => 0,
            'order_type' => 'mesa',
            'payment_method' => 'dinheiro',
            'observation' => null,
            'change_for' => null
        ], 'aberto'); // Status inicial = 'aberto'

        $this->tableRepo->occupy($tableId, $orderId);

        return $orderId;
    }

    /**
     * Busca ou cria comanda do cliente.
     * 
     * Se criar nova comanda, cria diretamente com status 'aberto'.
     */
    private function getOrCreateClientComanda(int $restaurantId, int $clientId): int
    {
        // Buscar comanda aberta do cliente
        $comanda = $this->orderRepo->findOpenByClient($clientId, $restaurantId);

        if ($comanda) {
            // Retorna comanda existente SEM alterar status
            return (int) $comanda['id'];
        }

        // Criar nova comanda diretamente com status 'aberto'
        $orderId = $this->orderRepo->create([
            'restaurant_id' => $restaurantId,
            'client_id' => $clientId,
            'total' => 0,
            'order_type' => 'comanda',
            'payment_method' => 'dinheiro',
            'observation' => null,
            'change_for' => null
        ], 'aberto'); // Status inicial = 'aberto'

        return $orderId;
    }

    /**
     * Calcula valor total da entrega.
     */
    private function calculateTotal(array $data): float
    {
        $cartTotal = 0;
        foreach ($data['cart'] as $item) {
            $cartTotal += ($item['price'] * $item['quantity']);
        }

        $deliveryFee = $data['delivery_fee'] ?? 0;
        $discount = $data['discount'] ?? 0;

        return $cartTotal + $deliveryFee - $discount;
    }

    /**
     * Adiciona item representando a entrega na conta vinculada.
     * 
     * ⚠️ Apenas adiciona ITEM na tabela order_items.
     * NÃO altera status da comanda.
     */
    private function addDeliveryItem(int $orderId, int $deliveryOrderId, array $data, float $totalToCharge): void
    {
        // Determina se é Entrega ou Retirada baseado no order_type
        $orderType = $data['order_type'] ?? 'delivery';
        $tipoLabel = ($orderType === 'pickup') ? 'Retirada' : 'Entrega';
        $description = "{$tipoLabel} #{$deliveryOrderId}";

        // Resumo dos itens
        $resumoItens = [];
        foreach ($data['cart'] as $item) {
            $resumoItens[] = "{$item['quantity']}x {$item['name']}";
        }
        $obs = implode(', ', $resumoItens);

        $deliveryFee = $data['delivery_fee'] ?? 0;
        $obsComplete = "Vinculado: {$obs}" . ($deliveryFee > 0 ? " (+Taxa: {$deliveryFee})" : "");

        $this->itemRepo->add($orderId, [
            'product_id' => 999999, // ID fictício para entrega/retirada vinculada
            'name' => $description,
            'quantity' => 1,
            'price' => $totalToCharge,
            'observation' => $obsComplete
        ]);
    }
}
