<?php

namespace App\Controllers\Admin;

use App\Services\Delivery\DeliveryService;
use App\Validators\DeliveryValidator;
use App\Core\View;

/**
 * DeliveryController - Super Thin
 * Gerencia kanban e histórico de delivery
 */
class DeliveryController extends BaseController
{
    private DeliveryService $service;
    private DeliveryValidator $v;

    public function __construct(DeliveryService $service, DeliveryValidator $validator)
    {
        $this->service = $service;
        $this->v = $validator;
    }

    // === VIEWS ===
    public function index()
    {
        $rid = $this->getRestaurantId();
        // Filtro opcional na URL
        $statusFilter = $_GET['status'] ?? null;

        $orders = $this->service->getOrders($rid, $statusFilter);

        View::renderFromScope('admin/delivery/index', get_defined_vars());
    }

    public function list()
    {
        // Método AJAX para polling do Kanban
        // Como é um partial view, precisamos garantir sessão mas talvez não redirect
        // O BaseController->getRestaurantId() redireciona se falhar? Sim.
        // Para AJAX puro talvez fosse melhor retornar 401, mas vamos manter simples por enquanto.
        $rid = $this->getRestaurantId();
        $statusFilter = $_GET['status'] ?? null;

        $orders = $this->service->getOrders($rid, $statusFilter);

        View::renderFromScope('admin/delivery/partials/order_list_kanban', get_defined_vars());
    }

    public function check()
    {
        // Polling Otimizado (Option A)
        // Retorna apenas um hash do estado atual. O front só baixa o HTML se o hash mudar.
        $rid = $this->getRestaurantId();
        $hash = $this->service->checkOrdersState($rid);
        
        $this->json(['success' => true, 'hash' => $hash]);
    }

    public function history()
    {
        $rid = $this->getRestaurantId();
        $selectedDate = $_GET['date'] ?? date('Y-m-d');

        $result = $this->service->getOrdersByOperationalDay($rid, $selectedDate);

        $rawOrders = $result['orders'];
        $businessHour = $result['business_hour'];
        $periodStart = $result['period_start'];
        $periodEnd = $result['period_end'];

        // --- PREPARAÇÃO DO VIEWMODEL (Lógica de Apresentação) ---

        // 1. Definições de Status (Centralizado no Controller)
        $statusLabels = [
            'novo' => ['label' => 'Novo', 'color' => '#3b82f6'],
            'preparo' => ['label' => 'Preparo', 'color' => '#8b5cf6'],
            'rota' => ['label' => 'Em Rota', 'color' => '#22c55e'],
            'entregue' => ['label' => 'Entregue', 'color' => '#059669'],
            'cancelado' => ['label' => 'Cancelado', 'color' => '#dc2626'],
        ];

        // 2. Formatação de Cabeçalho (Datas)
        $timestamp = strtotime($selectedDate);
        $displayDate = date('d/m/Y', $timestamp);
        $dayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        $dayName = $dayNames[date('w', $timestamp)];

        // 3. Cálculos de Totais e Preparação de Pedidos
        $totalPedidos = count($rawOrders);
        $totalValorVal = 0;
        $totalCanceladoVal = 0;

        $orders = array_map(function ($order) use (&$totalValorVal, &$totalCanceladoVal, $statusLabels) {
            $status = $order['status'] ?? 'novo';
            $val = floatval($order['total'] ?? 0);

            // Lógica de Totais
            if ($status === 'entregue') {
                $totalValorVal += $val;
            } elseif ($status === 'cancelado') {
                $totalCanceladoVal += $val;
            }

            // Status Badge
            $statusInfo = $statusLabels[$status] ?? ['label' => $status, 'color' => '#64748b'];

            return array_merge($order, [
                'formatted_date' => date('d/m/Y', strtotime($order['created_at'])),
                'formatted_time' => date('H:i', strtotime($order['created_at'])),
                'formatted_total' => 'R$ ' . number_format($val, 2, ',', '.'),
                'payment_method_label' => ucfirst($order['payment_method'] ?? '-'),
                'status_label' => $statusInfo['label'],
                'status_color' => $statusInfo['color'],
                'status_bg_rgba' => $statusInfo['color'] . '20' // 20 hex = ~12% opacity
            ]);
        }, $rawOrders);

        $totalValorFormatted = number_format($totalValorVal, 2, ',', '.');
        $totalCanceladoFormatted = number_format($totalCanceladoVal, 2, ',', '.');

        // Dados prontos para a View
        $viewData = compact(
            'orders',
            'displayDate',
            'dayName',
            'totalPedidos',
            'totalValorFormatted',
            'totalCanceladoFormatted',
            'selectedDate'
        );
        extract($viewData);

        View::renderFromScope('admin/delivery/history', get_defined_vars());
    }

    // === APIs JSON ===

    public function getOrderDetails()
    {
        $rid = $this->getRestaurantId();
        $orderId = $this->getInt('id');

        if ($orderId <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido'], 400);
        }

        $data = $this->service->getOrderDetails($orderId, $rid);

        if (!$data) {
            $this->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        $this->json([
            'success' => true,
            'order' => $data['order'],
            'items' => $data['items']
        ]);
    }

    public function updateStatus()
    {
        $rid = $this->getRestaurantId();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = $this->v->validateStatusUpdate($data);
        if ($this->v->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => reset($errors)], 400);
            return;
        }

        $result = $this->service->updateStatus((int)$data['order_id'], $data['new_status'], $rid);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function sendToTable()
    {
        $rid = $this->getRestaurantId();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = $this->v->validateSendToTable($data);
        if ($this->v->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => reset($errors)], 400);
            return;
        }

        $result = $this->service->sendToTable((int)$data['order_id'], $rid);
        $this->json($result, $result['success'] ? 200 : 400);
    }
}
