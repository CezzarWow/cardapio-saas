<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\StockRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\TableRepository;
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

    public function __construct(
        PaymentService $paymentService,
        CashRegisterService $cashRegisterService,
        StockRepository $stockRepo,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        TableRepository $tableRepo
    ) {
        $this->paymentService = $paymentService;
        $this->cashRegisterService = $cashRegisterService;
        $this->stockRepo = $stockRepo;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->tableRepo = $tableRepo;
    }

    public function execute(int $restaurantId, int $userId, array $data): int
    {
        $conn = Database::connect();
        
        // 1. Validação de Caixa
        $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

        try {
            $conn->beginTransaction();

            $cart = $data['cart'] ?? [];
            if (empty($cart)) {
                throw new Exception('O carrinho está vazio');
            }

            $orderType = $data['order_type'] ?? 'balcao';
            $tableId = $data['table_id'] ?? null;
            $commandId = $data['command_id'] ?? null;
            $existingOrderId = $data['order_id'] ?? null;

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
            $orderStatus = $saveAccount ? 'aberto' : 'novo';

            // VERIFICAR SE É INCREMENTO DE PEDIDO EXISTENTE
            if ($existingOrderId && $saveAccount) {
                // Buscar pedido existente
                $existingOrder = $this->orderRepo->find($existingOrderId);
                
                if ($existingOrder && $existingOrder['status'] === 'aberto') {
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
                    return $existingOrderId;
                }
            }

            // CRIAR NOVO PEDIDO (comportamento original)
            $orderId = $this->orderRepo->create([
                'restaurant_id' => $restaurantId,
                'client_id' => $data['client_id'] ?? null,
                'total' => $finalTotal,
                'status' => $orderStatus, 
                'order_type' => $orderType,
                'payment_method' => $paymentMethod,
                'observation' => $data['observation'] ?? null,
                'change_for' => $data['change_for'] ?? null,
                'is_paid' => $isPaid 
            ]);
            
            // Correção Pós-Criação
            if ($orderStatus !== 'novo') {
                $this->orderRepo->updateStatus($orderId, $orderStatus);
            }
            if ($isPaid) {
                $this->orderRepo->updatePayment($orderId, true, $paymentMethod);
            }

            // Mesa
            if ($orderType === 'mesa' && $tableId) {
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
