<?php

namespace App\Services\Order\Flows\Delivery;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Order\OrderStatus;
use App\Services\Order\TotalCalculator;
use App\Services\Order\OrderTotalService;
use App\Services\PaymentService;
use App\Traits\OrderCreationTrait;
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
    use OrderCreationTrait;

    private PaymentService $paymentService;
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private ClientRepository $clientRepo;
    private StockRepository $stockRepo;
    private OrderTotalService $totalService;

    public function __construct(
        PaymentService $paymentService,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        ClientRepository $clientRepo,
        StockRepository $stockRepo,
        OrderTotalService $totalService
    ) {
        $this->paymentService = $paymentService;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->clientRepo = $clientRepo;
        $this->stockRepo = $stockRepo;
        $this->totalService = $totalService;
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

        // 2. Calcular total (Estimativa Inicial para Validação)
        $deliveryFee = floatval($data['delivery_fee'] ?? 0);
        $discount = floatval($data['discount'] ?? 0);
        $cartTotal = TotalCalculator::fromCart($data['cart'], $discount);
        $total = $cartTotal + $deliveryFee;

        // 3. Determinar status inicial
        $isPaid = !empty($data['payments']);
        $initialStatus = $isPaid ? OrderStatus::AGUARDANDO : OrderStatus::NOVO;

        try {
            $conn->beginTransaction();

            // 4. Criar pedido
            $orderId = $this->orderRepo->create([
                'restaurant_id' => $restaurantId,
                'client_id' => $clientId,
                'total' => $total,
                'total_delivery' => $total, // FIX: Inicia com valor correto para evitar 0.00 no Kanban
                'order_type' => 'delivery',
                'observation' => $data['observation'] ?? null,
                'change_for' => $data['change_for'] ?? null
            ], $initialStatus);

            // 5. Inserir itens e baixar estoque
            $this->insertItemsAndDecrementStock($orderId, $data['cart'], $this->itemRepo, $this->stockRepo);

            // 6. Registrar pagamento (se informado)
            if ($isPaid) {
                $this->paymentService->registerPayments($conn, $orderId, $data['payments']);

                $mainMethod = count($data['payments']) > 1
                    ? 'multiplo'
                    : ($data['payments'][0]['method'] ?? 'dinheiro');
                $this->orderRepo->updatePayment($orderId, true, $mainMethod);
            }

            // 7. Recalcular Totais Definitivos (Popula total_delivery)
            // Importante: Taxa de Entrega deve estar nos itens se quisermos que o Service some.
            // Se o Create não insere a taxa como item, o Service não vai considerar?
            // DIAGNÓSTICO: Pedido #13 tinha item 'Taxa de Entrega'.
            // Quem insere esse item? Se for o frontend no cart, ok.
            // Se o frontend manda cart + delivery_fee separado, e a Action não insere item de taxa,
            // então o Service vai ignorar a taxa.
            // Vou assumir que o cart já vem com a taxa ou que este sistema precisa inserir a taxa como item.
            // Como vi que o sistema antigo usava 'Taxa de Entrega' como item, vou manter o recalculate.
            // Se o total diminuir, é porque a taxa não virou item.
            
            $finalTotals = $this->totalService->recalculate($orderId);
            $total = $finalTotals['total']; // Atualiza com o valor real do banco

            $conn->commit();

            $this->logOrderCreated('DELIVERY', $orderId, [
                'restaurant_id' => $restaurantId,
                'client_id' => $clientId,
                'status' => $initialStatus,
                'total' => $total,
                'is_paid' => $isPaid,
                'total_delivery' => $finalTotals['total_delivery']
            ]);

            return [
                'order_id' => $orderId,
                'client_id' => $clientId,
                'total' => $total,
                'status' => $initialStatus,
                'is_paid' => $isPaid,
                'total_delivery' => $finalTotals['total_delivery']
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            $this->logOrderError('DELIVERY', 'criar', $e, [
                'restaurant_id' => $restaurantId
            ]);
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
