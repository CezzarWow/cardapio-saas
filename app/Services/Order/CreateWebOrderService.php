<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;

/**
 * Service para criar pedidos via Cardápio Web
 * Orquestra: validação, cliente, pedido, itens
 */
class CreateWebOrderService
{
    private ClientRepository $clientRepository;
    private OrderRepository $orderRepository;
    private OrderItemRepository $itemRepository;

    /**
     * Mapeamento de tipos de pedido (frontend → banco)
     */
    private const ORDER_TYPE_MAP = [
        'entrega' => 'delivery',
        'retirada' => 'pickup',
        'local' => 'local',
        'delivery' => 'delivery',
        'pickup' => 'pickup'
    ];

    public function __construct(
        ClientRepository $clientRepository,
        OrderRepository $orderRepository,
        OrderItemRepository $itemRepository
    ) {
        $this->clientRepository = $clientRepository;
        $this->orderRepository = $orderRepository;
        $this->itemRepository = $itemRepository;
    }

    /**
     * Cria um pedido completo (cliente + pedido + itens)
     * 
     * @return array ['success' => bool, 'message' => string, 'order_id' => ?int]
     */
    public function execute(array $input): array
    {
        // Validações
        $restaurantId = $input['restaurant_id'] ?? null;
        $customerName = trim($input['customer_name'] ?? '');
        $items = $input['items'] ?? [];

        if (!$restaurantId || !$customerName || empty($items)) {
            return ['success' => false, 'message' => 'Dados obrigatórios faltando'];
        }

        try {
            $conn = Database::connect();
            $conn->beginTransaction();

            // 1. Criar ou buscar cliente
            $clientId = $this->clientRepository->findOrCreate($restaurantId, [
                'name' => $customerName,
                'phone' => trim($input['customer_phone'] ?? ''),
                'address' => $input['customer_address'] ?? null,
                'number' => $input['customer_number'] ?? null,
                'neighborhood' => $input['customer_neighborhood'] ?? null
            ]);

            // 2. Calcular total
            $calculatedItems = $this->calculateItems($items);
            $subtotal = array_sum(array_column($calculatedItems, 'total'));
            $deliveryFee = floatval($input['delivery_fee'] ?? 0);
            $total = $subtotal + $deliveryFee;

            // 3. Mapear tipo de pedido
            $orderTypeRaw = trim($input['order_type'] ?? 'delivery');
            if (empty($orderTypeRaw)) $orderTypeRaw = 'delivery';
            $orderType = self::ORDER_TYPE_MAP[$orderTypeRaw] ?? 'delivery';

            // 4. Processar troco
            $changeAmount = $this->parseChangeAmount($input['change_amount'] ?? null);

            // 5. Criar pedido
            $orderId = $this->orderRepository->create([
                'restaurant_id' => $restaurantId,
                'client_id' => $clientId,
                'total' => $total,
                'order_type' => $orderType,
                'payment_method' => $input['payment_method'] ?? 'dinheiro',
                'observation' => $input['observation'] ?? null,
                'change_for' => $changeAmount
            ]);

            // 6. Inserir itens
            $this->itemRepository->insert($orderId, $calculatedItems);

            $conn->commit();

            return [
                'success' => true,
                'message' => 'Pedido criado com sucesso',
                'order_id' => $orderId
            ];

        } catch (\Exception $e) {
            if (isset($conn)) $conn->rollBack();
            return ['success' => false, 'message' => 'Erro ao criar pedido: ' . $e->getMessage()];
        }
    }

    /**
     * Calcula preço de cada item (com adicionais)
     */
    private function calculateItems(array $items): array
    {
        $calculated = [];

        foreach ($items as $item) {
            $unitPrice = $item['unit_price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;

            // Soma adicionais
            if (!empty($item['additionals'])) {
                foreach ($item['additionals'] as $add) {
                    $unitPrice += ($add['price'] ?? 0);
                }
            }

            $calculated[] = [
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'] ?? 'Produto',
                'quantity' => $quantity,
                'price' => $unitPrice,
                'total' => $unitPrice * $quantity
            ];
        }

        return $calculated;
    }

    /**
     * Converte valor de troco (ex: "R$ 50,00" → 50.00)
     */
    private function parseChangeAmount($value): ?float
    {
        if (!$value) return null;

        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);
        
        return floatval($value) ?: null;
    }
}
