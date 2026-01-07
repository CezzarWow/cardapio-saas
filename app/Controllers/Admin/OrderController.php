<?php

namespace App\Controllers\Admin;

use App\Services\OrderOrchestratorService;
use Exception;

class OrderController {

    private $orchestrator;

    public function __construct() {
        $this->orchestrator = new OrderOrchestratorService();
    }

    // --- SALVAR NOVO PEDIDO (Balcão, Mesa ou Comanda) ---
    public function store() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $_SESSION['user_id'] ?? 1; // Default 1 se não logado (dev)
            $restaurantId = $_SESSION['loja_ativa_id'] ?? null;

            if (!$restaurantId) throw new Exception('Loja não selecionada');

            $orderId = $this->orchestrator->createOrder($restaurantId, $userId, $data);

            echo json_encode(['success' => true, 'order_id' => $orderId]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- FECHAR CONTA DA MESA ---
    public function closeTable() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $restaurantId = $_SESSION['loja_ativa_id'] ?? null;
            $tableId = $data['table_id'] ?? null;
            $payments = $data['payments'] ?? [];

            if (!$restaurantId) throw new Exception('Loja não selecionada');
            if (!$tableId) throw new Exception('Mesa inválida');

            $this->orchestrator->closeTable($restaurantId, $tableId, $payments);

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- FECHAR COMANDA ---
    public function closeCommand() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $restaurantId = $_SESSION['loja_ativa_id'] ?? null;
            $orderId = $data['order_id'] ?? null;
            $payments = $data['payments'] ?? [];

            if (!$restaurantId) throw new Exception('Loja não selecionada');
            if (!$orderId) throw new Exception('Pedido inválido');

            $this->orchestrator->closeCommand($restaurantId, $orderId, $payments);

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- REMOVER ITEM ---
    public function removeItem() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $itemId = $data['item_id'] ?? null;
            $orderId = $data['order_id'] ?? null;

            if (!$itemId || !$orderId) throw new Exception('Dados inválidos');

            $this->orchestrator->removeItem($itemId, $orderId);

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- CANCELAR PEDIDO DA MESA ---
    public function cancelTableOrder() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $tableId = $data['table_id'] ?? null;
            $orderId = $data['order_id'] ?? null;

            if (!$tableId || !$orderId) throw new Exception('Dados inválidos');

            $this->orchestrator->cancelOrder($orderId, $tableId);

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- ENTREGAR PEDIDO (Retirada) ---
    public function deliverOrder() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $restaurantId = $_SESSION['loja_ativa_id'] ?? null;
            $orderId = $data['order_id'] ?? null;

            if (!$orderId) throw new Exception('ID do pedido não informado');

            $this->orchestrator->deliverOrder($orderId, $restaurantId);

            echo json_encode(['success' => true, 'message' => 'Pedido entregue com sucesso!']);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- INCLUIR ITENS EM PEDIDO PAGO ---
    public function includePaidOrderItems() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $restaurantId = $_SESSION['loja_ativa_id'] ?? null;
            $orderId = $data['order_id'] ?? null;
            $cart = $data['cart'] ?? [];
            $payments = $data['payments'] ?? [];

            if (!$orderId) throw new Exception('Pedido não identificado');
            if (empty($cart)) throw new Exception('Carrinho vazio');

            $newTotal = $this->orchestrator->includePaidItems($orderId, $cart, $payments, $restaurantId);

            echo json_encode([
                'success' => true, 
                'message' => 'Itens incluídos com sucesso!', 
                'new_total' => $newTotal
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
