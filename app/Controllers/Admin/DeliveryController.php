<?php
/**
 * ============================================
 * DELIVERY CONTROLLER
 * Gerencia pedidos de delivery (order_type = 'delivery')
 * 
 * FASE 1: Apenas index() com mock/query simples
 * ============================================
 */
namespace App\Controllers\Admin;

use App\Core\Database;

class DeliveryController {

    /**
     * Lista pedidos de delivery
     * FASE 5: Suporte a filtros via querystring
     */
    public function index() {
        $this->checkSession();
        
        $restaurant_id = $_SESSION['loja_ativa_id'];
        $statusFilter = $_GET['status'] ?? null;
        $orders = $this->fetchOrders($restaurant_id, $statusFilter);
        
        require __DIR__ . '/../../../views/admin/delivery/index.php';
    }

    /**
     * FASE 5: Retorna apenas o HTML da lista (para polling)
     * GET /admin/loja/delivery/list
     */
    public function list() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            http_response_code(401);
            echo 'Sessão expirada';
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $statusFilter = $_GET['status'] ?? null;
        $orders = $this->fetchOrders($restaurant_id, $statusFilter);
        
        // Retorna apenas o partial da lista (Kanban)
        require __DIR__ . '/../../../views/admin/delivery/partials/order_list_kanban.php';
    }

    /**
     * Busca pedidos de delivery (reutilizado por index e list)
     */
    private function fetchOrders($restaurant_id, $statusFilter = null) {
        $orders = [];
        
        try {
            $conn = Database::connect();
            
            // Query base - busca TODOS os status
            $sql = "
                SELECT o.id, o.total, o.status, o.created_at, o.payment_method,
                       c.name as client_name, 
                       c.phone as client_phone,
                       c.address as client_address,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                FROM orders o
                LEFT JOIN clients c ON o.client_id = c.id
                WHERE o.restaurant_id = :rid 
                  AND o.order_type = 'delivery'
            ";
            
            $params = ['rid' => $restaurant_id];
            
            // Filtro por status (se fornecido)
            $validStatuses = ['novo', 'preparo', 'rota', 'entregue', 'cancelado'];
            if ($statusFilter && in_array($statusFilter, $validStatuses)) {
                $sql .= " AND o.status = :status";
                $params['status'] = $statusFilter;
            }
            
            $sql .= " ORDER BY 
                CASE o.status 
                    WHEN 'novo' THEN 1 
                    WHEN 'preparo' THEN 2 
                    WHEN 'rota' THEN 3 
                    WHEN 'entregue' THEN 4 
                    WHEN 'cancelado' THEN 5 
                END,
                o.created_at DESC
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $orders = [];
        }
        
        return $orders;
    }

    /**
     * Verifica sessão ativa
     */
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }

    /**
     * Retorna detalhes completos do pedido (para modal e impressão)
     * GET /admin/loja/delivery/details?id=X
     */
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

        try {
            $conn = Database::connect();
            
            // Busca pedido com dados do cliente
            $stmt = $conn->prepare("
                SELECT o.*, 
                       c.name as client_name, 
                       c.phone as client_phone,
                       c.address as client_address,
                       r.name as restaurant_name,
                       r.phone as restaurant_phone
                FROM orders o
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.id = :oid AND o.restaurant_id = :rid
            ");
            $stmt->execute(['oid' => $order_id, 'rid' => $restaurant_id]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
                exit;
            }

            // Busca itens do pedido
            $stmtItems = $conn->prepare("
                SELECT name, quantity, price 
                FROM order_items 
                WHERE order_id = :oid
            ");
            $stmtItems->execute(['oid' => $order_id]);
            $items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ============================================
    // FASE 3: ATUALIZAÇÃO DE STATUS
    // ============================================

    /**
     * Transições permitidas (backend decide, frontend apenas reflete)
     */
    private const ALLOWED_TRANSITIONS = [
        'novo'    => ['preparo', 'cancelado'],
        'preparo' => ['rota', 'cancelado'],
        'rota'    => ['entregue'],
    ];

    /**
     * Atualiza status do pedido delivery
     * POST /admin/loja/delivery/status
     */
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

        // Validação básica
        if (!$order_id || !$new_status) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        try {
            $conn = Database::connect();
            
            // Busca pedido atual
            $stmt = $conn->prepare("
                SELECT id, status, order_type 
                FROM orders 
                WHERE id = :oid AND restaurant_id = :rid
            ");
            $stmt->execute(['oid' => $order_id, 'rid' => $restaurant_id]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
                exit;
            }

            if ($order['order_type'] !== 'delivery') {
                echo json_encode(['success' => false, 'message' => 'Este pedido não é delivery']);
                exit;
            }

            $current_status = $order['status'];

            // Valida transição permitida
            $allowed = self::ALLOWED_TRANSITIONS[$current_status] ?? [];
            if (!in_array($new_status, $allowed)) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Transição não permitida: {$current_status} → {$new_status}"
                ]);
                exit;
            }

            // Executa UPDATE
            $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :oid");
            $stmt->execute(['status' => $new_status, 'oid' => $order_id]);

            echo json_encode([
                'success' => true, 
                'message' => 'Status atualizado',
                'new_status' => $new_status
            ]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
