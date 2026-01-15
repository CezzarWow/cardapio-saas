<?php

namespace App\Services\Order\Flows\Delivery;

use App\Core\Database;
use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;
use App\Services\Order\TotalCalculator;
use App\Services\PaymentService;
use RuntimeException;

/**
 * Action: Criar Delivery Standalone
 *
 * Fluxo ISOLADO para criar pedido de delivery.
 *
 * Responsabilidades:
 * - Criar ou buscar cliente
 * - Criar pedido com status NOVO (não é conta aberta)
 * - Inserir itens
 * - Registrar pagamento (se informado)
 * - Baixar estoque
 *
 * NÃO FAZ:
 * - Vincular a mesa
 * - Criar conta aberta
 * - Usar status ABERTO
 */
class CreateDeliveryStandaloneAction
{
    private PaymentService $paymentService;
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private ClientRepository $clientRepo;
    private StockRepository $stockRepo;

    public function __construct(
        PaymentService $paymentService,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        ClientRepository $clientRepo,
        StockRepository $stockRepo
    ) {
        $this->paymentService = $paymentService;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->clientRepo = $clientRepo;
        $this->stockRepo = $stockRepo;
    }

    /**
     * Cria pedido de delivery
     *
     * @param int $restaurantId ID do restaurante
     * @param array $data Payload validado
     * @return array ['order_id' => int, 'total' => float, 'status' => string]
     */
    public function execute(int $restaurantId, array $data): array
    {
        $conn = Database::connect();

        // 1. Buscar ou criar cliente
        $clientId = $this->getOrCreateClient($restaurantId, $data);

        // 2. Calcular total
        $deliveryFee = floatval($data['delivery_fee'] ?? 0);
        $discount = floatval($data['discount'] ?? 0);
        $cartTotal = TotalCalculator::fromCart($data['cart'], $discount);
        $total = $cartTotal + $deliveryFee;

        // 3. Determinar status inicial
        // Delivery começa como NOVO (ou AGUARDANDO se pago)
        $isPaid = !empty($data['payments']);
        $initialStatus = $isPaid ? OrderStatus::AGUARDANDO : OrderStatus::NOVO;

        try {
            $conn->beginTransaction();

            // 4. Criar pedido
            $orderId = $this->orderRepo->create([
                'restaurant_id' => $restaurantId,
                'client_id' => $clientId,
                'total' => $total,
                'order_type' => 'delivery',
                'observation' => $data['observation'] ?? null,
                'change_for' => $data['change_for'] ?? null
            ], $initialStatus);

            // 5. Inserir itens
            $this->itemRepo->insert($orderId, $data['cart']);

            // 6. Registrar pagamento (se informado)
            if ($isPaid) {
                $this->paymentService->registerPayments($conn, $orderId, $data['payments']);

                $mainMethod = count($data['payments']) > 1
                    ? 'multiplo'
                    : ($data['payments'][0]['method'] ?? 'dinheiro');
                $this->orderRepo->updatePayment($orderId, true, $mainMethod);
            }

            // 7. Baixar estoque
            foreach ($data['cart'] as $item) {
                $this->stockRepo->decrement($item['id'], $item['quantity']);
            }

            $conn->commit();

            error_log("[DELIVERY] Pedido #{$orderId} criado. Status: {$initialStatus}, Total: R$ " . number_format($total, 2, ',', '.'));

            return [
                'order_id' => $orderId,
                'client_id' => $clientId,
                'total' => $total,
                'status' => $initialStatus,
                'is_paid' => $isPaid
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            error_log('[DELIVERY] ERRO ao criar: ' . $e->getMessage());
            throw new RuntimeException('Erro ao criar delivery: ' . $e->getMessage());
        }
    }

    /**
     * Busca cliente existente ou cria novo
     */
    private function getOrCreateClient(int $restaurantId, array $data): int
    {
        // Se já tem client_id, usar
        if (!empty($data['client_id'])) {
            return intval($data['client_id']);
        }

        // Buscar por telefone
        $phone = $data['phone'] ?? null;
        if ($phone) {
            $existing = $this->clientRepo->findByPhone($restaurantId, $phone);
            if ($existing) {
                return intval($existing['id']);
            }
        }

        // Criar novo cliente
        return $this->clientRepo->create($restaurantId, [
            'restaurant_id' => $restaurantId,
            'name' => $data['client_name'] ?? 'Cliente Delivery',
            'phone' => $phone,
            'address' => $data['address'] ?? null,
            'address_number' => $data['address_number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'neighborhood' => $data['neighborhood'] ?? null,
            'reference' => $data['reference'] ?? null
        ]);
    }
}
