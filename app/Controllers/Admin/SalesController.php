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

    public function __construct(SalesService $service, SalesValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    /**
     * Lista todas as vendas
     */
    public function index(): void
    {
        $rid = $this->getRestaurantId();
        $rawOrders = $this->service->listOrders($rid);

        // --- PREPARAÇÃO DO VIEWMODEL ---
        // Calcular total para a View
        $totalSalesVal = array_sum(array_column($rawOrders, 'calculated_total'));
        $totalSalesFormatted = number_format($totalSalesVal, 2, ',', '.');

        $orders = array_map(function ($sale) {
            $isConcluido = ($sale['status'] === 'concluido');
            $isCancelado = ($sale['status'] === 'cancelado');

            return array_merge($sale, [
                'formatted_id' => '#' . str_pad($sale['id'], 4, '0', STR_PAD_LEFT),
                'formatted_date' => date('d/m/Y H:i', strtotime($sale['created_at'])),
                'formatted_total' => 'R$ ' . number_format($sale['calculated_total'], 2, ',', '.'),
                // Flags de permissão/estado
                'can_reopen' => $isConcluido,
                'can_cancel' => $isConcluido,
                'is_canceled' => $isCancelado,
                'status_label' => ucfirst($sale['status']) // Fallback simples se precisar
            ]);
        }, $rawOrders);

        // Passa variáveis prontas para a View
        $viewData = [
            'orders' => $orders,
            'totalSales' => $totalSalesVal, // Mantendo original por compatibilidade se algo usar
            'totalSalesFormatted' => $totalSalesFormatted
        ];
        extract($viewData);

        View::renderFromScope('admin/sales/index', get_defined_vars());
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
