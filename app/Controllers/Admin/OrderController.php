<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class OrderController {

    public function store() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $conn = Database::connect();

        // 🛑 1. SEGURANÇA: Verifica se o CAIXA está ABERTO antes de qualquer coisa
        $stmtCaixa = $conn->prepare("SELECT id FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmtCaixa->execute(['rid' => $restaurant_id]);
        $caixa = $stmtCaixa->fetch(PDO::FETCH_ASSOC);

        if (!$caixa) {
            echo json_encode(['success' => false, 'message' => 'O Caixa está FECHADO! Abra o caixa para vender. 🔒']);
            exit;
        }
        // -------------------------------------------------------------------

        $input = json_decode(file_get_contents('php://input'), true);
        $cart = $input['cart'] ?? [];
        $table_id = $input['table_id'] ?? null;

        if (empty($cart)) {
            echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
            exit;
        }
        
        try {
            $conn->beginTransaction();

            $orderId = null;
            $totalVenda = 0;

            // Calcula o total do carrinho para usar depois
            foreach ($cart as $item) {
                $totalVenda += ($item['price'] * $item['quantity']);
            }

            // --- LÓGICA DE MESA ---
            if ($table_id) {
                $stmtTable = $conn->prepare("SELECT current_order_id FROM tables WHERE id = :tid");
                $stmtTable->execute(['tid' => $table_id]);
                $mesa = $stmtTable->fetch(PDO::FETCH_ASSOC);

                if ($mesa && $mesa['current_order_id']) {
                    // MESA OCUPADA: Usa o pedido existente
                    $orderId = $mesa['current_order_id'];
                } else {
                    // MESA LIVRE: Cria pedido novo
                    $stmtOrder = $conn->prepare("INSERT INTO orders (restaurant_id, total, status) VALUES (:rid, 0, 'aberto')");
                    $stmtOrder->execute(['rid' => $restaurant_id]);
                    $orderId = $conn->lastInsertId();

                    $conn->prepare("UPDATE tables SET current_order_id = :oid, status = 'ocupada' WHERE id = :tid")
                         ->execute(['oid' => $orderId, 'tid' => $table_id]);
                }
            } 
            // --- LÓGICA DE BALCÃO ---
            else {
                // Cria pedido novo já FECHADO
                // Por padrão assumimos 'dinheiro' no balcão rápido, depois podemos melhorar isso
                $stmtOrder = $conn->prepare("INSERT INTO orders (restaurant_id, total, status, payment_method) VALUES (:rid, 0, 'concluido', 'dinheiro')");
                $stmtOrder->execute(['rid' => $restaurant_id]);
                $orderId = $conn->lastInsertId();

                // 💰 MOVIMENTAÇÃO FINANCEIRA (Só no Balcão, pois Mesa paga só no final)
                // Lança a entrada no extrato do caixa
                $desc = "Venda Balcão #" . $orderId;
                $stmtMov = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, 'venda', :val, :desc, :oid, NOW())");
                $stmtMov->execute([
                    'cid' => $caixa['id'],
                    'val' => $totalVenda,
                    'desc' => $desc,
                    'oid' => $orderId
                ]);
            }

            // --- SALVA OS ITENS ---
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

            // Atualiza o total do pedido
            $conn->prepare("UPDATE orders SET total = total + :val WHERE id = :oid")
                 ->execute(['val' => $totalVenda, 'oid' => $orderId]);

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
        $restaurant_id = $_SESSION['loja_ativa_id'];

        if (!$table_id) {
            echo json_encode(['success' => false, 'message' => 'Mesa inválida']);
            exit;
        }

        $conn = Database::connect();
        
        // 🛑 VERIFICA CAIXA (Segurança também no fechamento de mesa)
        $stmtCaixa = $conn->prepare("SELECT id FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmtCaixa->execute(['rid' => $restaurant_id]);
        $caixa = $stmtCaixa->fetch(PDO::FETCH_ASSOC);

        if (!$caixa) {
            echo json_encode(['success' => false, 'message' => 'Caixa FECHADO! Não é possível receber o pagamento.']);
            exit;
        }

        try {
            $conn->beginTransaction();

            // 1. Busca dados da mesa e do pedido
            $stmt = $conn->prepare("SELECT t.current_order_id, o.total 
                                    FROM tables t 
                                    JOIN orders o ON t.current_order_id = o.id 
                                    WHERE t.id = :tid");
            $stmt->execute(['tid' => $table_id]);
            $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($mesa && $mesa['current_order_id']) {
                // 2. Marca o pedido como CONCLUIDO
                // Aqui estamos assumindo 'dinheiro' por padrão, mas você pode passar isso via JSON futuramente
                $conn->prepare("UPDATE orders SET status = 'concluido', payment_method = 'dinheiro' WHERE id = :oid")
                     ->execute(['oid' => $mesa['current_order_id']]);

                // 3. Libera a mesa
                $conn->prepare("UPDATE tables SET status = 'livre', current_order_id = NULL WHERE id = :tid")
                     ->execute(['tid' => $table_id]);
                
                // 💰 4. LANÇA NO CAIXA (A grana entrou agora!)
                $desc = "Pagamento Mesa #" . ($data['table_number'] ?? $table_id); // Se tiver o numero vindo do JS ajuda, senao usa ID
                $stmtMov = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, 'venda', :val, :desc, :oid, NOW())");
                $stmtMov->execute([
                    'cid' => $caixa['id'],
                    'val' => $mesa['total'],
                    'desc' => "Fechamento Mesa",
                    'oid' => $mesa['current_order_id']
                ]);

                $conn->commit();
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mesa já está livre']);
            }
        } catch (\Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
