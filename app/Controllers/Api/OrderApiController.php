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
    
    private CreateWebOrderService $service;

    public function __construct(CreateWebOrderService $service) {
        $this->service = $service;
    }

    /**
     * Cria um novo pedido via API (cardápio web)
     * POST /api/order/create
     */
    public function create() {
        header('Content-Type: application/json');
        
        // Recebe dados JSON
        $rawInput = json_decode(file_get_contents('php://input'), true);
        $input = \App\Middleware\RequestSanitizerMiddleware::sanitize($rawInput ?? []);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }
        
        // Delega para o Service Injetado
        $result = $this->service->execute($input);
        
        echo json_encode($result);
    }
}
