<?php

namespace App\Controllers\Api;

use App\Core\Logger;
use App\Middleware\RequestSanitizerMiddleware;
use App\Services\Order\Flows\Mesa\AddItemsToMesaAction;
use App\Services\Order\Flows\Mesa\CloseMesaAccountAction;
use App\Services\Order\Flows\Mesa\MesaValidator;
use App\Services\Order\Flows\Mesa\OpenMesaAccountAction;

/**
 * Controller API: Fluxo Mesa
 *
 * Endpoints ISOLADOS para operações de mesa.
 * Não compartilha com Balcão, Comanda ou Delivery.
 *
 * Dependências injetadas explicitamente via construtor.
 */
class MesaController
{
    private MesaValidator $validator;
    private OpenMesaAccountAction $openAction;
    private AddItemsToMesaAction $addItemsAction;
    private CloseMesaAccountAction $closeAction;
    private int $restaurantId;

    /**
     * Injeção explícita de dependências
     */
    public function __construct(
        MesaValidator $validator,
        OpenMesaAccountAction $openAction,
        AddItemsToMesaAction $addItemsAction,
        CloseMesaAccountAction $closeAction
    ) {
        $this->validator = $validator;
        $this->openAction = $openAction;
        $this->addItemsAction = $addItemsAction;
        $this->closeAction = $closeAction;

        // Restaurant ID vem da sessão
        $this->restaurantId = $_SESSION['user']['restaurant_id'] ?? 0;
    }

    /**
     * POST /api/v1/mesa/abrir
     *
     * Abre conta de mesa
     */
    public function open(): void
    {
        header('Content-Type: application/json');

        $data = $this->getPayload();

        if (!$this->validateRestaurant()) {
            return;
        }

        // Validar contrato
        $errors = $this->validator->validateOpen($data);

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors], 400);
            return;
        }

        // Executar Action
        try {
            $result = $this->openAction->execute($this->restaurantId, $data);

            $this->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'table_id' => $result['table_id'],
                'table_number' => $result['table_number'],
                'total' => $result['total'],
                'status' => 'aberto',
                'message' => 'Mesa aberta com sucesso'
            ], 201);

        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    /**
     * POST /api/v1/mesa/{id}/itens
     *
     * Adiciona itens a mesa aberta
     */
    public function addItems(): void
    {
        header('Content-Type: application/json');

        $data = $this->getPayload();

        if (!$this->validateRestaurant()) {
            return;
        }

        // Validar contrato
        $errors = $this->validator->validateAddItems($data);

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors], 400);
            return;
        }

        // Executar Action
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
     * POST /api/v1/mesa/{id}/fechar
     *
     * Fecha conta de mesa com pagamento
     */
    public function close(): void
    {
        header('Content-Type: application/json');

        $data = $this->getPayload();

        if (!$this->validateRestaurant()) {
            return;
        }

        // Validar contrato
        $errors = $this->validator->validateClose($data);

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors], 400);
            return;
        }

        // Executar Action
        try {
            $result = $this->closeAction->execute($this->restaurantId, $data);

            $this->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'table_id' => $result['table_id'],
                'table_number' => $result['table_number'],
                'total' => $result['total'],
                'status' => $result['status'],
                'message' => 'Mesa fechada com sucesso'
            ], 200);

        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    // ============ Helpers ============

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
        Logger::error('MesaController erro', ['message' => $e->getMessage()]);

        $code = str_contains($e->getMessage(), 'não encontrad') ? 404 : 500;

        $this->json([
            'success' => false,
            'message' => $e->getMessage()
        ], $code);
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }
}
