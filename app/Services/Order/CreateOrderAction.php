<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Core\Logger;
use App\Events\EventDispatcher;
use App\Events\OrderCreatedEvent;
use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\TableRepository;
use App\Services\CashRegisterService;
use App\Services\PaymentService;
use Exception;

class CreateOrderAction
{
    private PaymentService $paymentService;
    private CashRegisterService $cashRegisterService;
    private StockRepository $stockRepo;
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private TableRepository $tableRepo;
    private ClientRepository $clientRepo;

    public function __construct(
        PaymentService $paymentService,
        CashRegisterService $cashRegisterService,
        StockRepository $stockRepo,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        TableRepository $tableRepo,
        ClientRepository $clientRepo
    ) {
        $this->paymentService = $paymentService;
        $this->cashRegisterService = $cashRegisterService;
        $this->stockRepo = $stockRepo;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->tableRepo = $tableRepo;
        $this->clientRepo = $clientRepo;
    }

    public function execute(int $restaurantId, int $userId, array $data): int
    {
        $conn = Database::connect();

        // 1. Validação de Caixa
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            $cart = $data['cart'] ?? [];
            $existingOrderId = $data['order_id'] ?? null;
            $finalizeNow = isset($data['finalize_now']) && $data['finalize_now'] == true;

            // Se for novo pedido, exige carrinho. Se for existente (finalização), pode vir vazio (apenas pagando).
            if (empty($cart) && !$existingOrderId) {
                throw new Exception('O carrinho está vazio');
            }

            $rawOrderType = $data['order_type'] ?? 'balcao';
            $orderType = $this->normalizeOrderType($rawOrderType);
            $tableId = $data['table_id'] ?? null;
            $commandId = $data['command_id'] ?? null;

            // Debug log (apenas em desenvolvimento)
            Logger::debug('CreateOrderAction: Processando pedido', [
                'restaurant_id' => $restaurantId,
                'order_id_input' => $existingOrderId,
                'save_account' => isset($data['save_account']) && $data['save_account'] == true,
                'finalize_now' => $finalizeNow,
                'order_type' => $orderType,
                'cart_count' => count($cart),
                'client_id' => $data['client_id'] ?? null,
                'delivery_data_present' => !empty($data['delivery_data'])
            ]);





            if ($orderType === 'mesa') {
                if (!$tableId) {
                    throw new Exception('Mesa não identificada');
                }
            }

            // Calcula totais e processa carrinho (separa ajustes negativos)
            $totals = $this->calculateTotals($cart, $data);
            $cart = $totals['cart'];
            $totalVenda = $totals['subtotal'];
            $adjustmentDiscount = $totals['adjustment_discount'];
            $discount = $totals['discount'];
            $deliveryFee = $totals['delivery_fee'];
            $finalTotal = $totals['final_total'];

            $isPaid = isset($data['is_paid']) && $data['is_paid'] == 1 ? 1 : 0;
            $payments = $data['payments'] ?? [];
            
            // Tenta obter o método do array de pagamentos, senão usa o do payload, senão 'dinheiro'
            $extractedMethod = !empty($payments[0]['method']) ? $payments[0]['method'] : null;
            $paymentMethod = $extractedMethod ?? ($data['payment_method'] ?? 'dinheiro');
            
            // Se houver múltiplos pagamentos, marca como 'multiplo'
            if (count($payments) > 1) {
                $paymentMethod = 'multiplo';
            }


            // SAVE_ACCOUNT: Cria como comanda aberta
            $saveAccount = isset($data['save_account']) && $data['save_account'] == true;
            $finalizeNow = isset($data['finalize_now']) && $data['finalize_now'] == true;

            // Determinar status inicial do pedido
            $orderStatus = $this->determineOrderStatus($orderType, $saveAccount, $finalizeNow, $isPaid);



            // VERIFICAR SE É INCREMENTO OU FINALIZAÇÃO DE PEDIDO EXISTENTE
            if ($existingOrderId && ($saveAccount || $finalizeNow)) {
                $result = $this->handleExistingOrder(
                    $conn,
                    $existingOrderId,
                    $orderType,
                    $orderStatus,
                    $cart,
                    $totalVenda,
                    $adjustmentDiscount,
                    $discount,
                    $finalizeNow,
                    $isPaid,
                    $payments,
                    $paymentMethod,
                    $caixa['id']
                );
                
                if ($result !== null) {
                    return $result;
                }
            }

            // Criar novo pedido
            $orderId = $this->createNewOrder(
                $conn,
                $restaurantId,
                $orderType,
                $orderStatus,
                $tableId,
                $cart,
                $totalVenda,
                $discount,
                $deliveryFee,
                $finalTotal,
                $isPaid,
                $paymentMethod,
                $payments,
                $data,
                $caixa['id']
            );

            $conn->commit();

            EventDispatcher::dispatch(new OrderCreatedEvent(
                $orderId,
                $restaurantId,
                $orderType,
                $orderStatus
            ));

            return $orderId;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Normaliza o tipo de pedido (PT -> EN)
     * Garante que 'entrega' vire 'delivery' para aparecer no Kanban
     *
     * @param string $rawOrderType Tipo de pedido bruto
     * @return string Tipo normalizado
     */
    private function normalizeOrderType(string $rawOrderType): string
    {
        $typeMap = [
            'entrega' => 'delivery',
            'retirada' => 'pickup',
            'retirada_pdv' => 'pickup',
            'balcao' => 'balcao',
            'mesa' => 'mesa',
            'comanda' => 'comanda',
            'local' => 'balcao'
        ];

        return $typeMap[$rawOrderType] ?? $rawOrderType;
    }

    /**
     * Calcula totais do pedido e processa carrinho
     * Separa itens reais de ajustes negativos (que viram desconto)
     *
     * @param array $cart Carrinho de itens
     * @param array $data Dados do pedido
     * @return array{cart: array, subtotal: float, adjustment_discount: float, discount: float, delivery_fee: float, final_total: float}
     */
    private function calculateTotals(array $cart, array $data): array
    {
        $totalVenda = 0;
        $finalCart = [];
        $adjustmentDiscount = 0;

        foreach ($cart as $item) {
            // Se for item de ajuste negativo, converte em desconto
            if (floatval($item['price']) < 0) {
                $adjustmentDiscount += abs(floatval($item['price']) * ($item['quantity'] ?? 1));
            } else {
                $finalCart[] = $item;
                $totalVenda += $item['price'] * ($item['quantity'] ?? 1);
            }
        }

        $discount = floatval($data['discount'] ?? 0) + $adjustmentDiscount;
        $deliveryFee = floatval($data['delivery_fee'] ?? 0);
        $finalTotal = max(0, $totalVenda + $deliveryFee - $discount);

        return [
            'cart' => $finalCart,
            'subtotal' => $totalVenda,
            'adjustment_discount' => $adjustmentDiscount,
            'discount' => $discount,
            'delivery_fee' => $deliveryFee,
            'final_total' => $finalTotal
        ];
    }

    /**
     * Determina o status inicial do pedido baseado em condições
     *
     * @param string $orderType Tipo do pedido
     * @param bool $saveAccount Se é para salvar como conta aberta
     * @param bool $finalizeNow Se está finalizando agora
     * @param int $isPaid Se está pago
     * @return string Status inicial do pedido
     */
    private function determineOrderStatus(string $orderType, bool $saveAccount, bool $finalizeNow, int $isPaid): string
    {
        // Delivery e Retirada SEMPRE começam como 'novo' para aparecer no Kanban
        $kanbanTypes = ['delivery', 'pickup'];

        if ($saveAccount) {
            // Se for salvar conta, delivery/pickup vão pro Kanban, outros ficam abertos
            return in_array($orderType, $kanbanTypes) ? OrderStatus::NOVO : OrderStatus::ABERTO;
        }

        if ($finalizeNow) {
            if (in_array($orderType, $kanbanTypes)) {
                // Delivery e Retirada sempre vão pro Kanban
                return OrderStatus::NOVO;
            }

            if ($isPaid) {
                // Finalizou e pagou (não é delivery/pickup) -> concluído
                return OrderStatus::CONCLUIDO;
            }

            // Finalizou mas não pagou -> aberto (comanda)
            return OrderStatus::ABERTO;
        }

        // Padrão: novo
        return OrderStatus::NOVO;
    }

    /**
     * Processa pedido existente (incremento ou finalização)
     *
     * @param \PDO $conn Conexão do banco
     * @param int|null $existingOrderId ID do pedido existente
     * @param string $orderType Tipo do pedido
     * @param string $orderStatus Status do pedido
     * @param array $cart Carrinho de itens
     * @param float $totalVenda Subtotal da venda
     * @param float $adjustmentDiscount Desconto de ajustes
     * @param float $discount Desconto total
     * @param bool $finalizeNow Se está finalizando
     * @param int $isPaid Se está pago
     * @param array $payments Array de pagamentos
     * @param string $paymentMethod Método de pagamento
     * @param int $caixaId ID do caixa
     * @return int|null ID do pedido se processado, null se não encontrado
     * @throws Exception
     */
    private function handleExistingOrder(
        \PDO $conn,
        ?int $existingOrderId,
        string $orderType,
        string $orderStatus,
        array $cart,
        float $totalVenda,
        float $adjustmentDiscount,
        float $discount,
        bool $finalizeNow,
        int $isPaid,
        array $payments,
        string $paymentMethod,
        int $caixaId
    ): ?int {
        if (!$existingOrderId) {
            return null;
        }

        // Buscar pedido existente
        $existingOrder = $this->orderRepo->find($existingOrderId);

        if (!$existingOrder || !in_array($existingOrder['status'], [OrderStatus::ABERTO, OrderStatus::NOVO])) {
            return null;
        }

        // Se for finalização, atualiza status
        if ($finalizeNow) {
            $this->orderRepo->updateStatus($existingOrderId, $orderStatus);
        }

        // Adicionar itens ao pedido existente
        $this->itemRepo->insert($existingOrderId, $cart);

        // Atualizar total do pedido (soma novos itens - descontos de ajuste)
        $newTotal = floatval($existingOrder['total']) + $totalVenda - $adjustmentDiscount;
        $this->orderRepo->updateTotal($existingOrderId, max(0, $newTotal));

        // Se mudou o tipo de pedido (ex: Retirada -> Delivery), atualiza o tipo
        if (!empty($orderType) && $existingOrder['order_type'] !== $orderType) {
            $this->orderRepo->updateOrderType($existingOrderId, $orderType);
        }

        // Baixa Estoque
        foreach ($cart as $item) {
            $this->stockRepo->decrement($item['id'], $item['quantity']);
        }

        $conn->commit();

        // Se pagou, registrar pagamentos e movimento de caixa
        if ($finalizeNow && !empty($payments)) {
            $this->paymentService->registerPayments($conn, $existingOrderId, $payments);

            if ($isPaid) {
                $this->orderRepo->updatePayment($existingOrderId, true, $paymentMethod);

                $desc = 'Venda ' . ucfirst($orderType) . ' #' . $existingOrderId;
                // Calcula o valor final considerando apenas os novos itens e desconto
                $finalAmount = max(0, $totalVenda - $adjustmentDiscount);

                $this->cashRegisterService->registerMovement(
                    $conn,
                    $caixaId,
                    $finalAmount,
                    $desc,
                    $existingOrderId
                );
            }
        }

        return $existingOrderId;
    }

    /**
     * Cria um novo pedido completo
     *
     * @param \PDO $conn Conexão do banco
     * @param int $restaurantId ID do restaurante
     * @param string $orderType Tipo do pedido
     * @param string $orderStatus Status inicial
     * @param int|null $tableId ID da mesa (se houver)
     * @param array $cart Carrinho de itens
     * @param float $totalVenda Subtotal da venda
     * @param float $discount Desconto total
     * @param float $deliveryFee Taxa de entrega
     * @param float $finalTotal Total final
     * @param int $isPaid Se está pago
     * @param string $paymentMethod Método de pagamento
     * @param array $payments Array de pagamentos
     * @param array $data Dados completos do pedido
     * @param int $caixaId ID do caixa
     * @return int ID do pedido criado
     * @throws Exception
     */
    private function createNewOrder(
        \PDO $conn,
        int $restaurantId,
        string $orderType,
        string $orderStatus,
        ?int $tableId,
        array $cart,
        float $totalVenda,
        float $discount,
        float $deliveryFee,
        float $finalTotal,
        int $isPaid,
        string $paymentMethod,
        array $payments,
        array $data,
        int $caixaId
    ): int {
        // Processar delivery_data para criar/atualizar cliente (se houver)
        $clientId = $data['client_id'] ?? null;
        if (!empty($data['delivery_data']) && $orderType === 'delivery') {
            $dd = $data['delivery_data'];
            $clientId = $this->clientRepo->findOrCreate($restaurantId, [
                'name' => $dd['name'] ?? 'Cliente Delivery',
                'phone' => $dd['phone'] ?? '',
                'address' => $dd['address'] ?? null,
                'number' => $dd['number'] ?? null,
                'neighborhood' => $dd['neighborhood'] ?? null
            ]);
        }

        // Montar observação final (pode vir de delivery_data ou do payload direto)
        $orderObservation = $data['observation'] ?? null;
        if (!empty($data['delivery_data']['observation'])) {
            $orderObservation = $data['delivery_data']['observation'];
        }

        // Criar pedido
        $orderId = $this->orderRepo->create([
            'restaurant_id' => $restaurantId,
            'client_id' => $clientId,
            'table_id' => $tableId ?: null,
            'total' => $finalTotal,
            'order_type' => $orderType,
            'payment_method' => $paymentMethod,
            'observation' => $orderObservation,
            'change_for' => $data['change_for'] ?? null
        ], $orderStatus);

        // Atualiza payment se pago
        if ($isPaid) {
            $this->orderRepo->updatePayment($orderId, true, $paymentMethod);
        }

        // FORÇA atualização do order_type (workaround para INSERT não salvar corretamente)
        $this->orderRepo->updateOrderType($orderId, $orderType);

        // Mesa - ocupar apenas se for pedido para PAGAR DEPOIS (status 'aberto')
        // Se já foi pago (status 'concluido'), não ocupa mesa
        if ($tableId && $orderStatus === OrderStatus::ABERTO) {
            $this->tableRepo->occupy($tableId, $orderId);
        }

        // Inserir itens
        $this->itemRepo->insert($orderId, $cart);

        // Baixa Estoque
        foreach ($cart as $item) {
            $this->stockRepo->decrement($item['id'], $item['quantity']);
        }

        // Inserir Taxa de Entrega como Item para aparecer na impressão
        if ($deliveryFee > 0) {
            $this->itemRepo->insert($orderId, [[
                'product_id' => 0, // 0 pois banco não aceita NULL
                'name' => 'Taxa de Entrega',
                'price' => $deliveryFee,
                'quantity' => 1,
                'extras' => null,
                'observation' => null
            ]]);
        }

        // Registrar pagamentos
        $this->paymentService->registerPayments($conn, $orderId, $payments);

        // Registrar movimento de caixa se pago
        if ($isPaid == 1 && !empty($payments)) {
            $desc = 'Venda ' . ucfirst($orderType) . ' #' . $orderId;
            $finalAmount = max(0, $totalVenda - $discount);

            $this->cashRegisterService->registerMovement(
                $conn,
                $caixaId,
                $finalAmount,
                $desc,
                $orderId
            );
        }

        return $orderId;
    }
}
