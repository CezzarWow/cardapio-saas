<?php

/**
 * ============================================
 * ORDER API CONTROLLER - DDD Lite
 * Recebe pedidos do cardápio público (web)
 * ============================================
 */

namespace App\Controllers\Api;

use App\Core\Logger;
use App\Services\Order\CreateWebOrderService;

class OrderApiController
{
    private CreateWebOrderService $service;

    public function __construct(CreateWebOrderService $service)
    {
        $this->service = $service;
    }

    /**
     * Cria um novo pedido via API (cardápio web)
     * POST /api/v1/order/create  (ou /api/order/create legado)
     */
    public function create()
    {
        header('Content-Type: application/json');

        // Recebe dados JSON
        $rawInput = json_decode(file_get_contents('php://input'), true);
        $input = \App\Middleware\RequestSanitizerMiddleware::sanitize($rawInput ?? []);

        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        // Delega para o Service Injetado
        try {
            $result = $this->service->execute($input);
        } catch (\Throwable $e) {
            Logger::error('Order API execution failed', [
                'input' => $input,
                'message' => $e->getMessage()
            ]);
            $result = ['success' => false, 'message' => 'Erro interno ao processar o pedido'];
        }

        echo json_encode($result);
    }
}
