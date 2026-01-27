<?php

namespace App\Controllers\Api;

use App\Core\Logger;
use App\Middleware\RequestSanitizerMiddleware;
use App\Services\Order\Flows\Delivery\CreateDeliveryStandaloneAction;
use App\Services\Order\Flows\Delivery\DeliveryValidator;
use App\Services\Order\Flows\Delivery\UpdateDeliveryStatusAction;

/**
 * Controller API: Fluxo Delivery
 *
 * Endpoints ISOLADOS para operações de delivery.
 * Não compartilha com Balcão, Mesa ou Comanda.
 *
 * Dependências injetadas explicitamente via construtor.
 */
class DeliveryController
{
    private DeliveryValidator $validator;
    private CreateDeliveryStandaloneAction $createAction;
    private UpdateDeliveryStatusAction $statusAction;
    private int $restaurantId;

    public function __construct(
        DeliveryValidator $validator,
        CreateDeliveryStandaloneAction $createAction,
        UpdateDeliveryStatusAction $statusAction
    ) {
        $this->validator = $validator;
        $this->createAction = $createAction;
        $this->statusAction = $statusAction;

        $this->restaurantId = $_SESSION['user']['restaurant_id'] ?? 0;
    }

    /**
     * POST /api/v1/delivery/criar
     */
    public function create(): void
    {
        header('Content-Type: application/json');

        $data = $this->getPayload();

        if (!$this->validateRestaurant()) {
            return;
        }

        $errors = $this->validator->validateCreate($data);

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors], 400);
            return;
        }

        try {
            $result = $this->createAction->execute($this->restaurantId, $data);

            $this->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'client_id' => $result['client_id'],
                'total' => $result['total'],
                'status' => $result['status'],
                'is_paid' => $result['is_paid'],
                'message' => 'Pedido de delivery criado com sucesso'
            ], 201);

        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    /**
     * POST /api/v1/delivery/status
     */
    public function updateStatus(): void
    {
        header('Content-Type: application/json');

        $data = $this->getPayload();

        if (!$this->validateRestaurant()) {
            return;
        }

        $errors = $this->validator->validateStatusUpdate($data);

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors], 400);
            return;
        }

        try {
            $result = $this->statusAction->execute($this->restaurantId, $data);

            $this->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'old_status' => $result['old_status'],
                'new_status' => $result['new_status'],
                'message' => 'Status atualizado com sucesso'
            ], 200);

        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    private function getPayload(): array
    {
        $rawInput = json_decode(file_get_contents('php://input'), true);
        return RequestSanitizerMiddleware::sanitize($rawInput ?? []);
    }

    private function validateRestaurant(): bool
    {
        if ($this->restaurantId <= 0) {
            $this->json(['success' => false, 'message' => 'Restaurante não identificado'], 401);
            return false;
        }
        return true;
    }

    private function handleError(\Throwable $e): void
    {
        Logger::error('DeliveryController erro', ['message' => $e->getMessage()]);
        $code = str_contains($e->getMessage(), 'não encontrad') ? 404 : 500;
        $this->json(['success' => false, 'message' => $e->getMessage()], $code);
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }
}
