<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\CashRegisterService;
use App\Services\PaymentService;
use Exception;

class IncludePaidItemsAction
{
    private PaymentService $paymentService;
    private CashRegisterService $cashRegisterService;
    private StockRepository $stockRepo;
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;

    public function __construct(
        PaymentService $paymentService,
        CashRegisterService $cashRegisterService,
        StockRepository $stockRepo,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo
    ) {
        $this->paymentService = $paymentService;
        $this->cashRegisterService = $cashRegisterService;
        $this->stockRepo = $stockRepo;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
    }

    public function execute(int $orderId, array $cart, array $payments, int $restaurantId): float
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $order = $this->orderRepo->find($orderId);
            if (!$order) {
                throw new Exception('Pedido não encontrado');
            }

            $newTotal = 0;

            foreach ($cart as $item) {
                $qty = intval($item['quantity'] ?? 1);
                $price = floatval($item['price'] ?? 0);
                $itemTotal = $qty * $price;
                $newTotal += $itemTotal;

                $this->itemRepo->add($orderId, [
                    'product_id' => $item['id'],
                    'qty' => $qty,
                    'price' => $price,
                    // 'name' => ... Repository defaults to 'Produto' if not set. Cart items usually have names.
                    'name' => $item['name'] ?? 'Produto'
                ]);

                $this->stockRepo->decrement($item['id'], $qty);
            }

            $this->paymentService->registerPayments($conn, $orderId, $payments);

            $updatedTotal = floatval($order['total']) + $newTotal;
            $this->orderRepo->updateTotal($orderId, $updatedTotal);

            if (!empty($payments)) {
                $paymentTotal = array_sum(array_column($payments, 'amount'));
                $desc = 'Inclusão Pedido #' . $orderId;

                // Agora delegamos ao CashRegisterService em vez de SQL direto
                // Mas preciso do ID do caixa aberto.
                $caixa = $this->cashRegisterService->assertOpen($conn, $restaurantId);

                $this->cashRegisterService->registerMovement(
                    $conn,
                    $caixa['id'],
                    $paymentTotal,
                    $desc,
                    $orderId
                );
            }

            $conn->commit();
            return $updatedTotal;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
