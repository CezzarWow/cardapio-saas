<?php

namespace App\Services\Order;

use App\Repositories\Order\OrderItemRepository;
use App\Repositories\TableRepository;
use Exception;

class CreateDeliveryLinkedAction
{
    private $createOrderAction;
    private $tableRepo;
    private $itemRepo;

    public function __construct(
        CreateOrderAction $createOrderAction,
        TableRepository $tableRepo,
        OrderItemRepository $itemRepo
    ) {
        $this->createOrderAction = $createOrderAction;
        $this->tableRepo = $tableRepo;
        $this->itemRepo = $itemRepo;
    }

    public function execute(int $restaurantId, int $userId, array $data): int
    {
        // 1. Criar o pedido de entrega normalmente
        // Remove table_id para não confundir a criação do pedido de entrega (que não tem mesa)
        $deliveryData = $data;
        $deliveryData['table_id'] = null;
        $deliveryData['order_type'] = 'delivery'; // Força delivery

        $deliveryOrderId = $this->createOrderAction->execute($restaurantId, $userId, $deliveryData);

        // 2. Buscar o pedido aberto da mesa vinculada (onde será cobrado)
        $tableId = $data['table_id'];
        $table = $this->tableRepo->findWithCurrentOrder($tableId);

        if (!$table || empty($table['order_id'])) {
            throw new Exception("Mesa #{$tableId} não possui conta aberta para vincular a entrega.");
        }

        $tableOrderId = $table['order_id'];

        // 3. Calcular valor total a cobrar na mesa
        // (Soma total dos itens + taxa entrega - desconto) do pedido de entrega
        $cartTotal = 0;
        foreach ($data['cart'] as $item) {
            $cartTotal += ($item['price'] * $item['quantity']);
        }
        
        $deliveryFee = $data['delivery_fee'] ?? 0;
        $discount = $data['discount'] ?? 0;
        
        $totalToCharge = $cartTotal + $deliveryFee - $discount;

        // 4. Adicionar item na mesa representando a entrega
        // Ex: "Entrega #123 (Marmita x2...)"
        // Se quiser detalhar, pode mudar a descrição
        
        $description = "Entrega #{$deliveryOrderId}";
        
        // Adiciona observação com resumo se houver
        $resumoItens = [];
        foreach ($data['cart'] as $item) {
            $resumoItens[] = "{$item['quantity']}x {$item['name']}";
        }
        $obs = implode(', ', $resumoItens);

        $this->itemRepo->add($tableOrderId, [
            'product_id' => 999999, // ID fictício para entrega vinculada
            'name' => $description,
            'quantity' => 1,
            'price' => $totalToCharge,
            'observation' => "Vinculado: {$obs}" . ($deliveryFee > 0 ? " (+Taxa: {$deliveryFee})" : "")
        ]);

        return $deliveryOrderId;
    }
}
