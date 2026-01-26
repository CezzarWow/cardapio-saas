<?php

namespace App\Services\Order\Flows\Balcao;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\CashRegisterService;
use App\Services\Order\OrderStatus;
use App\Services\Order\TotalCalculator;
use App\Services\PaymentService;
use App\Traits\OrderCreationTrait;
use RuntimeException;

/**
 * Action: Criar Venda Balcão
 *
 * Fluxo ISOLADO para venda direta com pagamento imediato.
 *
 * Responsabilidades:
 * - Criar pedido com status CONCLUIDO
 * - Inserir itens
 * - Registrar pagamentos (tabela separada)
 * - Baixar estoque
 * - Registrar movimento de caixa
 *
 * NÃO FAZ:
 * - Abrir conta
 * - Vincular a mesa ou cliente
 * - Inferir fluxo por order_type
 */
class CreateBalcaoSaleAction
{
    use OrderCreationTrait;

    private PaymentService $paymentService;
    private CashRegisterService $cashRegisterService;
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private StockRepository $stockRepo;

    public function __construct(
        PaymentService $paymentService,
        CashRegisterService $cashRegisterService,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        StockRepository $stockRepo
    ) {
        $this->paymentService = $paymentService;
        $this->cashRegisterService = $cashRegisterService;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->stockRepo = $stockRepo;
    }

    /**
     * Executa venda balcão
     *
     * @param int $restaurantId ID do restaurante
     * @param array $data Payload validado
     * @return array ['order_id' => int, 'total' => float]
     * @throws RuntimeException Se caixa fechado ou erro na transação
     */
    public function execute(int $restaurantId, array $data): array
    {
        $conn = Database::connect();

        // 1. Validar caixa aberto
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        // 2. Calcular total usando TotalCalculator (mesmo usado no Validator)
        $discount = floatval($data['discount'] ?? 0);
        $total = TotalCalculator::fromCart($data['cart'], $discount);

        try {
            $conn->beginTransaction();

            $mainMethod = count($data['payments']) > 1
                ? 'multiplo'
                : ($data['payments'][0]['method'] ?? 'dinheiro');

            // 3. Criar pedido com status CONCLUIDO
            // NOTA: Agora passamos payment_method para evitar Warning no Repository
            $orderId = $this->orderRepo->create([
                'restaurant_id' => $restaurantId,
                'client_id' => null,          // Balcão não tem cliente
                'total' => $total,
                'order_type' => 'balcao',     // Apenas para persistência, não para lógica
                'payment_method' => $mainMethod,
                'observation' => $data['observation'] ?? null,
                'change_for' => $data['change_for'] ?? null
            ], OrderStatus::CONCLUIDO);       // Status inicial = concluido

            // 4. Marcar como pago
            // NOTA: Passamos o primeiro método como referência, mas fonte da verdade é order_payments
            $this->orderRepo->updatePayment($orderId, true, $mainMethod);

            // 5. Inserir itens e baixar estoque
            $this->insertItemsAndDecrementStock($orderId, $data['cart'], $this->itemRepo, $this->stockRepo);

            // 6. Registrar pagamentos na tabela order_payments
            $this->paymentService->registerPayments($conn, $orderId, $data['payments']);

            // 8. Registrar movimento de caixa
            $this->cashRegisterService->registerMovement(
                $conn,
                $caixa['id'],
                $total,
                "Venda Balcão #{$orderId}",
                $orderId
            );

            $conn->commit();

            $this->logOrderCreated('BALCAO', $orderId, [
                'restaurant_id' => $restaurantId,
                'total' => $total,
                'payment_method' => $mainMethod
            ]);

            return [
                'order_id' => $orderId,
                'total' => $total
            ];

        } catch (\Throwable $e) {
            $conn->rollBack();
            $this->logOrderError('BALCAO', 'criar venda', $e, [
                'restaurant_id' => $restaurantId
            ]);
            throw new RuntimeException('Erro ao processar venda: ' . $e->getMessage());
        }
    }
}
