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
            // --- LÓGICA DE BALCÃO (FINALIZAR AGORA) ---
            else {
                
                // 1. Cria o Pedido (Status Concluido)
                $payments = $input['payments'] ?? [];
                
                // Se não vier pagamentos (ex: versão antiga do front), assume dinheiro
                if (empty($payments)) {
                    $payments = [['method' => 'dinheiro', 'amount' => $totalVenda]];
                }

                $mainMethod = $payments[0]['method'] ?? 'dinheiro';
                $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

                $stmtOrder = $conn->prepare("INSERT INTO orders (restaurant_id, client_id, total, status, payment_method) VALUES (:rid, :cid, 0, 'concluido', :method)");
                $stmtOrder->execute([
                    'rid' => $restaurant_id, 
                    'cid' => $input['client_id'] ?? null, 
                    'method' => $paymentMethodDesc
                ]);
                $orderId = $conn->lastInsertId();

                // 2. Salva os Pagamentos na tabela nova (order_payments)
                $stmtPay = $conn->prepare("INSERT INTO order_payments (order_id, method, amount) VALUES (:oid, :method, :amount)");
                
                // 3. Lança no Caixa (cash_movements) SOMENTE O QUE FOR DINHEIRO
                $stmtMov = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, 'venda', :val, :desc, :oid, NOW())");

                foreach ($payments as $pay) {
                    // Salva na tabela de pagamentos
                    $stmtPay->execute(['oid' => $orderId, 'method' => $pay['method'], 'amount' => $pay['amount']]);

                    // Se for dinheiro, entra na gaveta do caixa
                    if ($pay['method'] == 'dinheiro') {
                        $desc = "Venda Balcão #" . $orderId;
                        $stmtMov->execute([
                            'cid' => $caixa['id'],
                            'val' => $pay['amount'],
                            'desc' => $desc,
                            'oid' => $orderId
                        ]);
                    }
                }
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
            $stmt = $conn->prepare("SELECT t.current_order_id, t.number, o.total 
                                    FROM tables t 
                                    JOIN orders o ON t.current_order_id = o.id 
                                    WHERE t.id = :tid");
            $stmt->execute(['tid' => $table_id]);
            $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($mesa && $mesa['current_order_id']) {
                $orderId = $mesa['current_order_id'];
                
                // 2. Processa Pagamentos
                $payments = $data['payments'] ?? [];
                
                // Fallback para versão antiga ou pagamento único implícito
                if (empty($payments)) {
                    $payments = [['method' => 'dinheiro', 'amount' => $mesa['total']]];
                }

                $mainMethod = $payments[0]['method'] ?? 'dinheiro';
                $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

                $conn->prepare("UPDATE orders SET status = 'concluido', payment_method = :method WHERE id = :oid")
                     ->execute(['oid' => $orderId, 'method' => $paymentMethodDesc]);

                // 3. Salva os Pagamentos na tabela order_payments
                $stmtPay = $conn->prepare("INSERT INTO order_payments (order_id, method, amount) VALUES (:oid, :method, :amount)");
                $stmtMov = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, 'venda', :val, :desc, :oid, NOW())");

                foreach ($payments as $pay) {
                    // Salva Detalhe
                    $stmtPay->execute(['oid' => $orderId, 'method' => $pay['method'], 'amount' => $pay['amount']]);

                    // Se for dinheiro, lança no caixa
                    if ($pay['method'] == 'dinheiro') {
                        $desc = "Pagamento Mesa #" . $mesa['number'];
                        $stmtMov->execute([
                            'cid' => $caixa['id'],
                            'val' => $pay['amount'],
                            'desc' => $desc,
                            'oid' => $orderId
                        ]);
                    }
                }

                // 4. Libera a mesa
                $conn->prepare("UPDATE tables SET status = 'livre', current_order_id = NULL WHERE id = :tid")
                     ->execute(['tid' => $table_id]);

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

    // --- REMOVER ITEM SALVO (MESA) ---
    public function removeItem() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $data = json_decode(file_get_contents('php://input'), true);
        $item_id = $data['item_id'] ?? null;
        $order_id = $data['order_id'] ?? null;

        if (!$item_id || !$order_id) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Busca dados do item para processar
            $stmtItem = $conn->prepare("SELECT product_id, quantity, price FROM order_items WHERE id = :id AND order_id = :oid");
            $stmtItem->execute(['id' => $item_id, 'oid' => $order_id]);
            $item = $stmtItem->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                echo json_encode(['success' => false, 'message' => 'Item não encontrado']);
                $conn->rollBack();
                exit;
            }

            // 2. Lógica de Remover (Decrementar ou Deletar)
            if ($item['quantity'] > 1) {
                // Diminui 1
                $conn->prepare("UPDATE order_items SET quantity = quantity - 1 WHERE id = :id")->execute(['id' => $item_id]);
                // Valor a abater = Preço Unitário
                $valueToDeduct = $item['price'];
            } else {
                // Remove a linha
                $conn->prepare("DELETE FROM order_items WHERE id = :id")->execute(['id' => $item_id]);
                // Valor a abater = Preço Unitário (que é o total dessa linha de qtd 1)
                $valueToDeduct = $item['price'];
            }

            // 3. Devolve 1 unidade ao Estoque
            $conn->prepare("UPDATE products SET stock = stock + 1 WHERE id = :pid")
                 ->execute(['pid' => $item['product_id']]);

            // 4. Atualiza o Total do Pedido
            // Garante que não fique negativo (sanity check)
            $conn->prepare("UPDATE orders SET total = GREATEST(0, total - :val) WHERE id = :oid")
                 ->execute(['val' => $valueToDeduct, 'oid' => $order_id]);

            $conn->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- CANCELAR PEDIDO DA MESA (Apagar todos os itens salvos) ---
    public function cancelTableOrder() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $data = json_decode(file_get_contents('php://input'), true);
        $table_id = $data['table_id'] ?? null;
        $order_id = $data['order_id'] ?? null;

        if (!$table_id || !$order_id) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Busca itens para devolver estoque
            $stmtItems = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :oid");
            $stmtItems->execute(['oid' => $order_id]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $conn->prepare("UPDATE products SET stock = stock + :qtd WHERE id = :pid")
                     ->execute(['qtd' => $item['quantity'], 'pid' => $item['product_id']]);
            }

            // 2. Remove Itens e Pedido
            $conn->prepare("DELETE FROM order_items WHERE order_id = :oid")->execute(['oid' => $order_id]);
            $conn->prepare("DELETE FROM orders WHERE id = :oid")->execute(['oid' => $order_id]);

            // 3. Libera a Mesa
            $conn->prepare("UPDATE tables SET status = 'livre', current_order_id = NULL WHERE id = :tid")
                 ->execute(['tid' => $table_id]);

            $conn->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
