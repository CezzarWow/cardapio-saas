<?php

namespace App\Controllers\Admin;

use App\Services\SalesService;
use App\Validators\SalesValidator;

/**
 * SalesController - Super Thin
 * 
 * Gerencia histórico de vendas, cancelamento e reativação de mesas.
 * Lógica de negócio no SalesService.
 * Validações no SalesValidator.
 */
class SalesController extends BaseController
{
    private SalesService $service;
    private SalesValidator $validator;

    public function __construct()
    {
        $this->service = new SalesService();
        $this->validator = new SalesValidator();
    }

    /**
     * Lista todas as vendas
     */
    public function index(): void
    {
        $rid = $this->getRestaurantId();
        $orders = $this->service->listOrders($rid);
        
        require __DIR__ . '/../../../views/admin/sales/index.php';
    }

    /**
     * Retorna itens de um pedido (API JSON)
     */
    public function getItems(): void
    {
        $orderId = $this->getInt('id');
        
        $errors = $this->validator->validateGetId($orderId);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }
        
        $items = $this->service->getOrderItems($orderId);
        $this->json($items);
    }

    /**
     * Cancela uma venda (estorna estoque e caixa)
     */
    public function cancel(): void
    {
        $this->getRestaurantId(); // Valida sessão
        $data = $this->getJsonBody();

        $errors = $this->validator->validateOrderId($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        $result = $this->service->cancelOrder((int)$data['id']);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Reativa mesa (volta pedido para aberto)
     */
    public function reactivateTable(): void
    {
        $rid = $this->getRestaurantId();
        $data = $this->getJsonBody();

        $errors = $this->validator->validateOrderId($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        $result = $this->service->reactivateTable((int)$data['id'], $rid);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * @deprecated Use reactivateTable() instead
     */
    public function reopen(): void
    {
        $this->reactivateTable();
    }
}
