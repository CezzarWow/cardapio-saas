<?php
/**
 * ============================================
 * DELIVERY CONTROLLER - DDD Lite
 * Gerencia pedidos de delivery/pickup/local
 * ============================================
 */
namespace App\Controllers\Admin;

use App\Services\Delivery\DeliveryQueryService;
use App\Services\Delivery\UpdateOrderStatusService;
use App\Services\Delivery\SendToTableService;

class DeliveryController {

    // ==========================================
    // LISTAGEM (VIEW)
    // ==========================================
    public function index() {
        $this->checkSession();
        
        $restaurant_id = $_SESSION['loja_ativa_id'];
        $statusFilter = $_GET['status'] ?? null;
        
        $queryService = new DeliveryQueryService();
        $orders = $queryService->getOrders($restaurant_id, $statusFilter);
        
        require __DIR__ . '/../../../views/admin/delivery/index.php';
    }

    // ==========================================
    // LISTAGEM PARCIAL (POLLING AJAX)
    // ==========================================
    public function list() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            http_response_code(401);
            echo 'Sessão expirada';
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $statusFilter = $_GET['status'] ?? null;
        
        $queryService = new DeliveryQueryService();
        $orders = $queryService->getOrders($restaurant_id, $statusFilter);
        
        require __DIR__ . '/../../../views/admin/delivery/partials/order_list_kanban.php';
    }

    // ==========================================
    // HISTÓRICO POR DIA OPERACIONAL
    // ==========================================
    public function history() {
        $this->checkSession();
        
        $restaurant_id = $_SESSION['loja_ativa_id'];
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        
        $queryService = new DeliveryQueryService();
        $result = $queryService->getOrdersByOperationalDay($restaurant_id, $selectedDate);
        
        $orders = $result['orders'];
        $businessHour = $result['business_hour'];
        $periodStart = $result['period_start'];
        $periodEnd = $result['period_end'];
        
        require __DIR__ . '/../../../views/admin/delivery/history.php';
    }

    // ==========================================
    // DETALHES DO PEDIDO (API)
    // ==========================================
    public function getOrderDetails() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $order_id = $_GET['id'] ?? null;

        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'ID não informado']);
            exit;
        }

        $queryService = new DeliveryQueryService();
        $data = $queryService->getOrderDetails((int)$order_id, $restaurant_id);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'order' => $data['order'],
            'items' => $data['items']
        ]);
    }

    // ==========================================
    // ATUALIZAR STATUS (API)
    // ==========================================
    public function updateStatus() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        $order_id = $input['order_id'] ?? null;
        $new_status = $input['new_status'] ?? null;

        if (!$order_id || !$new_status) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $service = new UpdateOrderStatusService();
        $result = $service->execute((int)$order_id, $new_status, $restaurant_id);

        echo json_encode($result);
    }

    // ==========================================
    // ENVIAR PARA MESA (API)
    // ==========================================
    public function sendToTable() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        $order_id = $input['order_id'] ?? null;

        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'ID do pedido não informado']);
            exit;
        }

        $service = new SendToTableService();
        $result = $service->execute((int)$order_id, $restaurant_id);

        echo json_encode($result);
    }

    // ==========================================
    // SESSÃO
    // ==========================================
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
