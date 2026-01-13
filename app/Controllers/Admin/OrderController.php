<?php

namespace App\Controllers\Admin;

use App\Services\OrderOrchestratorService;
use App\Validators\OrderValidator;
use Exception;

/**
 * OrderController - Super Thin
 * 
 * Gerencia operações de pedidos: criar, fechar mesa/comanda,
 * remover itens, cancelar, entregar e incluir em pedido pago.
 * 
 * Toda lógica de negócio está no OrderOrchestratorService.
 * Validações estão no OrderValidator.
 */
class OrderController extends BaseController
{
    private OrderOrchestratorService $service;
    private OrderValidator $validator;

    public function __construct(OrderOrchestratorService $service, OrderValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    /**
     * Criar novo pedido (Balcão, Mesa ou Comanda)
     */
    public function store(): void
    {
        $rid = $this->getRestaurantId();
        $data = $this->getJsonBody();

        $errors = $this->validator->validateStore($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $orderId = $this->service->createOrder($rid, $this->getUserId(), $data);
            $this->json(['success' => true, 'order_id' => $orderId]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fechar conta da mesa
     */
    public function closeTable(): void
    {
        $rid = $this->getRestaurantId();
        $data = $this->getJsonBody();

        $errors = $this->validator->validateCloseTable($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $this->service->closeTable($rid, (int)$data['table_id'], $data['payments'] ?? []);
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fechar comanda
     */
    public function closeCommand(): void
    {
        $rid = $this->getRestaurantId();
        $data = $this->getJsonBody();

        $errors = $this->validator->validateCloseCommand($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $this->service->closeCommand($rid, (int)$data['order_id'], $data['payments'] ?? []);
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remover item de pedido
     */
    public function removeItem(): void
    {
        $this->getRestaurantId(); // Valida sessão
        $data = $this->getJsonBody();

        $errors = $this->validator->validateRemoveItem($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $this->service->removeItem((int)$data['item_id'], (int)$data['order_id']);
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancelar pedido da mesa
     */
    public function cancelTableOrder(): void
    {
        $this->getRestaurantId(); // Valida sessão
        $data = $this->getJsonBody();

        $errors = $this->validator->validateCancelOrder($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $this->service->cancelOrder((int)$data['order_id'], (int)$data['table_id']);
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Entregar pedido (Retirada)
     */
    public function deliverOrder(): void
    {
        $rid = $this->getRestaurantId();
        $data = $this->getJsonBody();

        $errors = $this->validator->validateDeliverOrder($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $this->service->deliverOrder((int)$data['order_id'], $rid);
            $this->json(['success' => true, 'message' => 'Pedido entregue com sucesso!']);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Incluir itens em pedido pago
     */
    public function includePaidOrderItems(): void
    {
        $rid = $this->getRestaurantId();
        $data = $this->getJsonBody();

        $errors = $this->validator->validateIncludePaidItems($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $newTotal = $this->service->includePaidItems(
                (int)$data['order_id'],
                $data['cart'],
                $data['payments'] ?? [],
                $rid
            );
            $this->json([
                'success' => true,
                'message' => 'Itens incluídos com sucesso!',
                'new_total' => $newTotal
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
