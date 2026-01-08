<?php
namespace App\Controllers\Admin;

use App\Services\Delivery\DeliveryService;
use App\Validators\DeliveryValidator;

/**
 * DeliveryController - Super Thin
 * Gerencia kanban e histórico de delivery
 */
class DeliveryController extends BaseController {

    private DeliveryService $service;
    private DeliveryValidator $v;

    public function __construct() {
        $this->service = new DeliveryService();
        $this->v = new DeliveryValidator();
    }

    // === VIEWS ===
    public function index() {
        $rid = $this->getRestaurantId();
        // Filtro opcional na URL
        $statusFilter = $_GET['status'] ?? null;
        
        $orders = $this->service->getOrders($rid, $statusFilter);
        
        require __DIR__ . '/../../../views/admin/delivery/index.php';
    }

    public function list() {
        // Método AJAX para polling do Kanban
        // Como é um partial view, precisamos garantir sessão mas talvez não redirect
        // O BaseController->getRestaurantId() redireciona se falhar? Sim.
        // Para AJAX puro talvez fosse melhor retornar 401, mas vamos manter simples por enquanto.
        $rid = $this->getRestaurantId();
        $statusFilter = $_GET['status'] ?? null;
        
        $orders = $this->service->getOrders($rid, $statusFilter);
        
        require __DIR__ . '/../../../views/admin/delivery/partials/order_list_kanban.php';
    }

    public function history() {
        $rid = $this->getRestaurantId();
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        
        $result = $this->service->getOrdersByOperationalDay($rid, $selectedDate);
        
        // Extrai variáveis para a view
        $orders = $result['orders'];
        $businessHour = $result['business_hour'];
        $periodStart = $result['period_start'];
        $periodEnd = $result['period_end'];
        
        require __DIR__ . '/../../../views/admin/delivery/history.php';
    }

    // === APIs JSON ===

    public function getOrderDetails() {
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

    public function updateStatus() {
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

    public function sendToTable() {
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
