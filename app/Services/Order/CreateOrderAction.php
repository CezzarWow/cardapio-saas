<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\StockRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\TableRepository;
use App\Repositories\ClientRepository;
use PDO;
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

            $orderType = $data['order_type'] ?? 'balcao';
            $tableId = $data['table_id'] ?? null;
            $commandId = $data['command_id'] ?? null;



            if ($orderType === 'mesa') {
                if (!$tableId) throw new Exception('Mesa não identificada');
            }

            $totalVenda = 0;
            foreach ($cart as $item) {
                $totalVenda += $item['price'] * $item['quantity'];
            }

            $discount = floatval($data['discount'] ?? 0);
            $deliveryFee = floatval($data['delivery_fee'] ?? 0);
            $finalTotal = max(0, $totalVenda + $deliveryFee - $discount);
            
            $isPaid = isset($data['is_paid']) && $data['is_paid'] == 1 ? 1 : 0;
            $paymentMethod = $data['payment_method'] ?? 'dinheiro';
            $payments = $data['payments'] ?? [];
            
            
            // SAVE_ACCOUNT: Cria como comanda aberta
            $saveAccount = isset($data['save_account']) && $data['save_account'] == true;
            $finalizeNow = isset($data['finalize_now']) && $data['finalize_now'] == true;
            
            // Determinar status inicial do pedido
            $orderStatus = 'novo';
            if ($saveAccount) {
                $orderStatus = 'aberto';
            } elseif ($finalizeNow && $isPaid && !in_array($orderType, ['delivery', 'pickup'])) {
                // Se finalizou e pagou (e não é delivery/pickup que precisam do Kanban), conclui direto
                $orderStatus = 'concluido';
            } elseif ($finalizeNow && in_array($orderType, ['delivery', 'pickup'])) {
                // Delivery e Retirada SEMPRE começam como 'novo' para aparecer no Kanban
                // (independente de pago ou não)
                $orderStatus = 'novo'; 
            } elseif ($finalizeNow && !$isPaid && !in_array($orderType, ['delivery', 'pickup'])) {
                // Finalizou mas não pagou (ex: Marcar na conta) -> Aberto (Comanda)
                // Exceto delivery/pickup que devem ir pro Kanban
                $orderStatus = 'aberto';
            }

            // VERIFICAR SE É INCREMENTO OU FINALIZAÇÃO DE PEDIDO EXISTENTE
            if ($existingOrderId && ($saveAccount || $finalizeNow)) {
                // Buscar pedido existente
                $existingOrder = $this->orderRepo->find($existingOrderId);
                
                if ($existingOrder && ($existingOrder['status'] === 'aberto' || $existingOrder['status'] === 'novo')) {
                    
                    // Se for finalização, atualiza status
                    if ($finalizeNow) {
                        $this->orderRepo->updateStatus($existingOrderId, $orderStatus);
                    }
                    // Adicionar itens ao pedido existente
                    $this->itemRepo->insert($existingOrderId, $cart);
                    
                    // Atualizar total do pedido (soma com o existente)
                    $newTotal = floatval($existingOrder['total']) + $totalVenda;
                    $this->orderRepo->updateTotal($existingOrderId, $newTotal);
                    
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
                            
                            $desc = "Venda " . ucfirst($orderType) . " #" . $existingOrderId;
                            $finalAmount = max(0, $totalVenda - $discount);
                            
                            $this->cashRegisterService->registerMovement(
                                $conn,
                                $caixa['id'],
                                $finalAmount,
                                $desc,
                                $existingOrderId
                            );
                        }
                    }

                    return $existingOrderId;
                }
            }

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

            // CRIAR NOVO PEDIDO com status inicial correto
            $orderId = $this->orderRepo->create([
                'restaurant_id' => $restaurantId,
                'client_id' => $clientId,
                'table_id' => $tableId ?: null,
                'total' => $finalTotal,
                'order_type' => $orderType,
                'payment_method' => $paymentMethod,
                'observation' => $orderObservation,
                'change_for' => $data['change_for'] ?? null
            ], $orderStatus); // Status passado como segundo parâmetro
            
            // Atualiza payment se pago
            if ($isPaid) {
                $this->orderRepo->updatePayment($orderId, true, $paymentMethod);
            }
            
            // FORÇA atualização do order_type (workaround para INSERT não salvar corretamente)
            $this->orderRepo->updateOrderType($orderId, $orderType);

            // Mesa - ocupar apenas se for pedido para PAGAR DEPOIS (status 'aberto')
            // Se já foi pago (status 'concluido'), não ocupa mesa
            if ($tableId && $orderStatus === 'aberto') {
                $this->tableRepo->occupy($tableId, $orderId);
            }

            // Itens com Repo
            $this->itemRepo->insert($orderId, $cart);

            // Baixa Estoque via Repository
            foreach ($cart as $item) {
                $this->stockRepo->decrement($item['id'], $item['quantity']);
            }

            $this->paymentService->registerPayments($conn, $orderId, $payments);

            if ($isPaid == 1 && !empty($payments)) {
                $desc = "Venda " . ucfirst($orderType) . " #" . $orderId;
                $finalAmount = max(0, $totalVenda - $discount);
                
                $this->cashRegisterService->registerMovement(
                    $conn,
                    $caixa['id'],
                    $finalAmount,
                    $desc,
                    $orderId
                );
            }

            $conn->commit();
            
            return $orderId;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
