<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class OrderController {

    public function store() {
        header('Content-Type: application/json');
        
        // Verifica se a sessão já foi iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $cart = $input['cart'] ?? [];
        $table_id = $input['table_id'] ?? null; // Recebe o ID da mesa

        if (empty($cart)) {
            echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
            exit;
        }

        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();

            $orderId = null;

            // --- LÓGICA DE MESA ---
            if ($table_id) {
                // 1. Verifica se a mesa já tem um pedido aberto
                $stmtTable = $conn->prepare("SELECT current_order_id FROM tables WHERE id = :tid");
                $stmtTable->execute(['tid' => $table_id]);
                $mesa = $stmtTable->fetch(PDO::FETCH_ASSOC);

                if ($mesa && $mesa['current_order_id']) {
                    // MESA OCUPADA: Usa o pedido existente
                    $orderId = $mesa['current_order_id'];
                } else {
                    // MESA LIVRE: Cria pedido novo (Status 'aberto')
                    $stmtOrder = $conn->prepare("INSERT INTO orders (restaurant_id, total, status) VALUES (:rid, 0, 'aberto')");
                    $stmtOrder->execute(['rid' => $_SESSION['loja_ativa_id']]);
                    $orderId = $conn->lastInsertId();

                    // Vincula pedido à mesa e marca como ocupada
                    $conn->prepare("UPDATE tables SET current_order_id = :oid, status = 'ocupada' WHERE id = :tid")
                         ->execute(['oid' => $orderId, 'tid' => $table_id]);
                }
            } 
            // --- LÓGICA DE BALCÃO ---
            else {
                // Cria pedido novo já fechado (Status 'concluido')
                $stmtOrder = $conn->prepare("INSERT INTO orders (restaurant_id, total, status) VALUES (:rid, 0, 'concluido')");
                $stmtOrder->execute(['rid' => $_SESSION['loja_ativa_id']]);
                $orderId = $conn->lastInsertId();
            }

            // --- SALVA OS ITENS ---
            $totalAdicional = 0;
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, quantity, price) VALUES (:oid, :pid, :name, :qtd, :price)");
            $stmtStock = $conn->prepare("UPDATE products SET stock = stock - :qtd WHERE id = :pid");

            foreach ($cart as $item) {
                $stmtItem->execute([
                    'oid' => $orderId,
                    'pid' => $item['id'],
                    'name' => $item['name'],
                    'qtd' => $item['quantity'],
                    'price' => $item['price']
                ]);
                
                $stmtStock->execute(['qtd' => $item['quantity'], 'pid' => $item['id']]);
            }

            // Atualiza o valor total do pedido (soma o que já tinha + o novo)
            $conn->prepare("UPDATE orders SET total = total + :val WHERE id = :oid")
                 ->execute(['val' => $totalAdicional, 'oid' => $orderId]);

            $conn->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- FECHAR CONTA DA MESA ---
    public function closeTable() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $data = json_decode(file_get_contents('php://input'), true);
        $table_id = $data['table_id'] ?? null;

        if (!$table_id) {
            echo json_encode(['success' => false, 'message' => 'Mesa inválida']);
            exit;
        }

        $conn = Database::connect();
        try {
            // 1. Busca qual o pedido dessa mesa
            $stmt = $conn->prepare("SELECT current_order_id FROM tables WHERE id = :tid");
            $stmt->execute(['tid' => $table_id]);
            $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($mesa && $mesa['current_order_id']) {
                // 2. Marca o pedido como CONCLUIDO
                $conn->prepare("UPDATE orders SET status = 'concluido' WHERE id = :oid")
                     ->execute(['oid' => $mesa['current_order_id']]);

                // 3. Libera a mesa (Status LIVRE, Pedido NULL)
                $conn->prepare("UPDATE tables SET status = 'livre', current_order_id = NULL WHERE id = :tid")
                     ->execute(['tid' => $table_id]);
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mesa já está livre']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
