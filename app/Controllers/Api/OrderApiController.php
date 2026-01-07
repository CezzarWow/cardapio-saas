<?php
/**
 * ============================================
 * ORDER API CONTROLLER - DDD Lite
 * Recebe pedidos do cardápio público (web)
 * ============================================
 */
namespace App\Controllers\Api;

use App\Services\Order\CreateWebOrderService;

class OrderApiController {
    
    /**
     * Cria um novo pedido via API (cardápio web)
     * POST /api/order/create
     */
    public function create() {
        header('Content-Type: application/json');
        
        // Recebe dados JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }
        
        // Delega para o Service
        $service = new CreateWebOrderService();
        $result = $service->execute($input);
        
        echo json_encode($result);
    }
}
