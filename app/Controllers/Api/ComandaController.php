<?php

namespace App\Controllers\Api;

use App\Middleware\RequestSanitizerMiddleware;
use App\Services\Order\Flows\Comanda\AddItemsToComandaAction;
use App\Services\Order\Flows\Comanda\CloseComandaAction;
use App\Services\Order\Flows\Comanda\ComandaValidator;
use App\Services\Order\Flows\Comanda\OpenComandaAction;

/**
 * Controller API: Fluxo Comanda
 *
 * Endpoints ISOLADOS para operações de comanda.
 * Não compartilha com Balcão, Mesa ou Delivery.
 *
 * Dependências injetadas explicitamente via construtor.
 */
class ComandaController
{
    private ComandaValidator $validator;
    private OpenComandaAction $openAction;
    private AddItemsToComandaAction $addItemsAction;
    private CloseComandaAction $closeAction;
    private int $restaurantId;

    public function __construct(
        ComandaValidator $validator,
        OpenComandaAction $openAction,
        AddItemsToComandaAction $addItemsAction,
        CloseComandaAction $closeAction
    ) {
        $this->validator = $validator;
        $this->openAction = $openAction;
        $this->addItemsAction = $addItemsAction;
        $this->closeAction = $closeAction;

        $this->restaurantId = $_SESSION['user']['restaurant_id'] ?? 0;
    }

    /**
     * POST /api/v1/comanda/abrir
     */
    public function open(): void
    {
        header('Content-Type: application/json');

        $data = $this->getPayload();

        if (!$this->validateRestaurant()) {
            return;
        }

        $errors = $this->validator->validateOpen($data);

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors], 400);
            return;
        }

        try {
            $result = $this->openAction->execute($this->restaurantId, $data);

            $this->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'client_id' => $result['client_id'],
                'client_name' => $result['client_name'],
                'total' => $result['total'],
                'status' => 'aberto',
                'message' => 'Comanda aberta com sucesso'
            ], 201);

        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    /**
     * POST /api/v1/comanda/itens
     */
    public function addItems(): void
    {
        header('Content-Type: application/json');

        $data = $this->getPayload();

        if (!$this->validateRestaurant()) {
            return;
        }

        $errors = $this->validator->validateAddItems($data);

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors], 400);
            return;
        }

        try {
            $result = $this->addItemsAction->execute($this->restaurantId, $data);

            $this->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'items_added' => $result['items_added'],
                'added_value' => $result['added_value'],
                'new_total' => $result['new_total'],
                'message' => 'Itens adicionados com sucesso'
            ], 200);

        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    /**
     * POST /api/v1/comanda/fechar
     */
    public function close(): void
    {
        header('Content-Type: application/json');

        $data = $this->getPayload();

        if (!$this->validateRestaurant()) {
            return;
        }

        $errors = $this->validator->validateClose($data);

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors], 400);
            return;
        }

        try {
            $result = $this->closeAction->execute($this->restaurantId, $data);

            $this->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'client_id' => $result['client_id'],
                'total' => $result['total'],
                'status' => $result['status'],
                'message' => 'Comanda fechada com sucesso'
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
        error_log('[COMANDA_CONTROLLER] Erro: ' . $e->getMessage());
        $code = str_contains($e->getMessage(), 'não encontrad') ? 404 : 500;
        $this->json(['success' => false, 'message' => $e->getMessage()], $code);
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }
}
