<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\AdditionalItemRepository;
use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\PaymentService;

/**
 * Service para criar pedidos via Cardápio Web
 * Orquestra: validação, cliente, pedido, itens
 */
class CreateWebOrderService
{
    private ClientRepository $clientRepository;
    private OrderRepository $orderRepository;
    private OrderItemRepository $itemRepository;
    private ProductRepository $productRepository;
    private AdditionalItemRepository $additionalItemRepository;
    private PaymentService $paymentService;

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
        OrderItemRepository $itemRepository,
        ProductRepository $productRepository,
        AdditionalItemRepository $additionalItemRepository,
        PaymentService $paymentService
    ) {
        $this->clientRepository = $clientRepository;
        $this->orderRepository = $orderRepository;
        $this->itemRepository = $itemRepository;
        $this->productRepository = $productRepository;
        $this->additionalItemRepository = $additionalItemRepository;
        $this->paymentService = $paymentService;
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
            $calculatedItems = $this->calculateItems($restaurantId, $items);
            $subtotal = array_sum(array_column($calculatedItems, 'total'));
            $deliveryFee = floatval($input['delivery_fee'] ?? 0);
            $total = $subtotal + $deliveryFee;

            // 3. Mapear tipo de pedido
            $orderType = $this->resolveOrderType($input['order_type'] ?? null);

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
                'change_for' => $changeAmount,
                'source' => 'web'
            ]);

            // 6. Inserir itens
            $this->itemRepository->insert($orderId, $calculatedItems);

            // 7. Registrar pagamentos relacionados ao pedido
            $payments = $input['payments'] ?? [];
            $this->paymentService->registerPayments($conn, $orderId, $payments);

            $conn->commit();

            return [
                'success' => true,
                'message' => 'Pedido criado com sucesso',
                'order_id' => $orderId
            ];

        } catch (\Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            Logger::error('Falha ao criar pedido web', [
                'restaurant_id' => $restaurantId,
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Erro ao criar pedido: ' . $e->getMessage()];
        }
    }

    /**
     * Calcula preço de cada item (com adicionais)
     */
    private function calculateItems(int $restaurantId, array $items): array
    {
        $calculated = [];

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if ($productId <= 0) {
                throw new \InvalidArgumentException('Produto inválido');
            }

            $product = $this->productRepository->find($productId, $restaurantId);
            if (!$product || (isset($product['is_active']) && (int) $product['is_active'] !== 1)) {
                throw new \InvalidArgumentException('Produto não encontrado ou inativo');
            }

            $unitPrice = $this->resolveProductPrice($product);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $extras = [];

            $productGroups = $this->additionalItemRepository->findByProduct($productId, $restaurantId);
            $additionals = $item['additionals'] ?? [];
            $this->validateRequiredAdditionals($additionals, $productGroups);

            // Soma adicionais
            if (is_array($additionals)) {
                foreach ($additionals as $add) {
                    $addId = (int) ($add['id'] ?? $add['item_id'] ?? 0);
                    if ($addId <= 0) {
                        throw new \InvalidArgumentException('Adicional inválido');
                    }

                    $addItem = $this->additionalItemRepository->findById($addId, $restaurantId);
                    if (!$addItem) {
                        throw new \InvalidArgumentException('Adicional não encontrado');
                    }

                    $addPrice = (float) ($addItem['price'] ?? 0);
                    $unitPrice += $addPrice;
                    $extras[] = [
                        'id' => (int) $addItem['id'],
                        'name' => $addItem['name'] ?? 'Adicional',
                        'price' => $addPrice
                    ];
                }
            }

            $calculated[] = [
                'product_id' => $productId,
                'name' => $product['name'] ?? 'Produto',
                'quantity' => $quantity,
                'price' => $unitPrice,
                'total' => $unitPrice * $quantity,
                'extras' => empty($extras) ? null : $extras
            ];
        }

        return $calculated;
    }

    private function resolveProductPrice(array $product): float
    {
        $price = (float) ($product['price'] ?? 0);
        $promoPrice = (float) ($product['promotional_price'] ?? 0);
        $isPromo = !empty($product['is_on_promotion']) && $promoPrice > 0;

        if (!$isPromo) {
            return $price;
        }

        $expires = $product['promo_expires_at'] ?? null;
        if (!$expires || $expires >= date('Y-m-d')) {
            return $promoPrice;
        }

        return $price;
    }

    /**
     * Converte valor de troco (ex: "R$ 50,00" → 50.00)
     */
    private function parseChangeAmount($value): ?float
    {
        if (!$value) {
            return null;
        }

        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);

        return floatval($value) ?: null;
    }

    private function resolveOrderType(?string $raw): string
    {
        $normalized = trim((string) ($raw ?? ''));
        if ($normalized === '' || !isset(self::ORDER_TYPE_MAP[$normalized])) {
            return 'delivery';
        }
        return self::ORDER_TYPE_MAP[$normalized];
    }

    /**
     * Garante que grupos obrigatórios têm pelo menos um adicional selecionado.
     */
    private function validateRequiredAdditionals(array $additionals, array $groups): void
    {
        $selectedIds = [];
        foreach ($additionals as $additional) {
            $id = $this->extractAdditionalId($additional);
            if ($id > 0) {
                $selectedIds[$id] = true;
            }
        }

        foreach ($groups as $group) {
            if (empty($group['required'])) {
                continue;
            }

            $groupItems = $group['items'] ?? [];
            $hasSelection = false;
            foreach ($groupItems as $item) {
                $itemId = (int) ($item['id'] ?? 0);
                if ($itemId > 0 && isset($selectedIds[$itemId])) {
                    $hasSelection = true;
                    break;
                }
            }

            if (!$hasSelection) {
                $groupName = $group['name'] ?? 'o grupo obrigatório';
                throw new \InvalidArgumentException("Selecione pelo menos um adicional para {$groupName}");
            }
        }
    }

    /**
     * Extrai ID do adicional independentemente do campo enviado (id ou item_id).
     */
    private function extractAdditionalId(array $additional): int
    {
        return (int) ($additional['id'] ?? $additional['item_id'] ?? 0);
    }
}
