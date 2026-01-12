<?php

namespace App\Controllers\Api;

use App\Services\Order\Flows\Balcao\CreateBalcaoSaleAction;
use App\Services\Order\Flows\Balcao\BalcaoValidator;
use App\Middleware\RequestSanitizerMiddleware;

/**
 * Controller API: Fluxo Balcão
 * 
 * Endpoint ISOLADO para venda direta.
 * Não compartilha com Mesa, Comanda ou Delivery.
 * 
 * Dependências injetadas explicitamente via construtor.
 */
class BalcaoController
{
    private BalcaoValidator $validator;
    private CreateBalcaoSaleAction $action;
    private int $restaurantId;

    /**
     * Injeção explícita de dependências (ajuste obrigatório #1)
     */
    public function __construct(
        BalcaoValidator $validator,
        CreateBalcaoSaleAction $action
    ) {
        $this->validator = $validator;
        $this->action = $action;
        
        // Restaurant ID vem da sessão (já autenticado)
        $this->restaurantId = $_SESSION['user']['restaurant_id'] ?? 0;
    }

    /**
     * POST /api/v1/balcao/venda
     * 
     * Cria venda balcão (pagamento imediato)
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        
        // 1. Receber e sanitizar payload
        $rawInput = json_decode(file_get_contents('php://input'), true);
        $data = RequestSanitizerMiddleware::sanitize($rawInput ?? []);
        
        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'Payload inválido'], 400);
            return;
        }
        
        // 2. Validar restaurante
        if ($this->restaurantId <= 0) {
            $this->json(['success' => false, 'message' => 'Restaurante não identificado'], 401);
            return;
        }
        
        // 3. Validar contrato (flow_type morre aqui)
        $errors = $this->validator->validate($data);
        
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $errors
            ], 400);
            return;
        }
        
        // 4. Executar Action
        try {
            $result = $this->action->execute($this->restaurantId, $data);
            
            $this->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'status' => 'concluido',
                'total' => $result['total'],
                'message' => 'Venda realizada com sucesso'
            ], 201);
            
        } catch (\Throwable $e) {
            error_log("[BALCAO_CONTROLLER] Erro: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper para resposta JSON
     */
    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }
}
