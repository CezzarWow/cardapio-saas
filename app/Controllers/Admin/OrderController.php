<?php
/**
 * ============================================
 * ORDER CONTROLLER
 * Gerencia pedidos, mesas, comandas e pagamentos
 * 
 * DECISÃO TÉCNICA (2026-01-01):
 * Analisado em FASE 1 — NÃO modularizado.
 * Motivo: 8 de 10 métodos usam transactions.
 * Mover para Service quebraria rollback/commit.
 * ============================================
 */
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
        $caixa = $this->getCaixaAberto($conn, $restaurant_id);

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
            // Se finalize_now = true, ignora a lógica de mesa e finaliza direto
            $finalizeNow = isset($input['finalize_now']) && $input['finalize_now'] === true;
            
            if ($table_id && !$finalizeNow) {
                // APENAS SALVAR NA MESA (botão "Salvar")
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
            // --- LÓGICA DE SALVAR COMANDA (CLIENTE) ---
            elseif (!empty($input['save_account']) && !empty($input['client_id'])) {
                // Se já vier ID do pedido (edição), usa ele
                $orderId = $input['order_id'] ?? null;

                if ($orderId) {
                    // Verifica se existe e é do cliente
                    $chk = $conn->prepare("SELECT id FROM orders WHERE id = :oid AND client_id = :cid AND status = 'aberto'");
                    $chk->execute(['oid' => $orderId, 'cid' => $input['client_id']]);
                    if (!$chk->fetch()) $orderId = null; // Se não bater, cria novo
                }

                if (!$orderId) {
                    // Cria NOVO pedido ABERTO
                    $stmtOrder = $conn->prepare("INSERT INTO orders (restaurant_id, client_id, total, status) VALUES (:rid, :cid, 0, 'aberto')");
                    $stmtOrder->execute([
                        'rid' => $restaurant_id,
                        'cid' => $input['client_id']
                    ]);
                    $orderId = $conn->lastInsertId();
                }
            }
            // --- LÓGICA DE BALCÃO (FINALIZAR AGORA) ---
            else {
                
                // 1. Verifica se é RETIRADA (keep_open = true)
                $keepOpen = isset($input['keep_open']) && $input['keep_open'] === true;
                
                // 2. Cria o Pedido
                $payments = $input['payments'] ?? [];
                $discount = floatval($input['discount'] ?? 0); // [NOVO] Desconto
                
                // Se não vier pagamentos (ex: versão antiga do front), assume dinheiro
                if (empty($payments)) {
                    $payments = [['method' => 'dinheiro', 'amount' => $totalVenda - $discount]];
                }

                $mainMethod = $payments[0]['method'] ?? 'dinheiro';
                $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

                // Se for RETIRADA: status = aberto, is_paid = 1 (Pago mas aguardando retirada)
                // Se for LOCAL: status = concluido (Venda finalizada)
                $orderStatus = $keepOpen ? 'aberto' : 'concluido';
                
                // [NOVO] Usa is_paid do frontend se disponível, senão usa lógica legada
                $isPaid = isset($input['is_paid']) ? intval($input['is_paid']) : ($keepOpen ? 1 : 0);
                
                // [NOVO] Tipo de pedido: local, pickup (retirada), delivery (entrega)
                $orderType = $input['order_type'] ?? 'local';
                if (!in_array($orderType, ['local', 'pickup', 'delivery'])) {
                    $orderType = 'local';
                }
                
                // Para Retirada/Entrega, muda status para 'novo' para aparecer no Kanban
                if (in_array($orderType, ['pickup', 'delivery'])) {
                    $orderStatus = 'novo';
                }

                // [NOVO] Se não pagou (is_paid=0), usa payment_method_expected como forma esperada
                if ($isPaid == 0 && isset($input['payment_method_expected'])) {
                    $paymentMethodDesc = $input['payment_method_expected'];
                }

                $stmtOrder = $conn->prepare("INSERT INTO orders (restaurant_id, client_id, total, discount, status, payment_method, is_paid, order_type) VALUES (:rid, :cid, 0, :discount, :status, :method, :paid, :otype)");
                $stmtOrder->execute([
                    'rid' => $restaurant_id, 
                    'cid' => $input['client_id'] ?? null, 
                    'discount' => $discount,
                    'status' => $orderStatus,
                    'method' => $paymentMethodDesc,
                    'paid' => $isPaid,
                    'otype' => $orderType
                ]);
                $orderId = $conn->lastInsertId();

                // [NOVO] Se for Entrega com dados de delivery_data, cria/atualiza cliente
                if ($orderType === 'delivery' && isset($input['delivery_data'])) {
                    $deliveryData = $input['delivery_data'];
                    $deliveryName = trim($deliveryData['name'] ?? '');
                    $deliveryAddress = trim($deliveryData['address'] ?? '');
                    $deliveryNumber = trim($deliveryData['number'] ?? '');
                    $deliveryNeighborhood = trim($deliveryData['neighborhood'] ?? '');
                    $deliveryPhone = trim($deliveryData['phone'] ?? '');
                    $deliveryComplement = trim($deliveryData['complement'] ?? '');

                    $clientIdForOrder = $input['client_id'] ?? null;

                    // Se já tem cliente vinculado, atualiza os dados de entrega
                    if ($clientIdForOrder) {
                        $stmtUpdateClient = $conn->prepare("
                            UPDATE clients SET 
                                address = :addr,
                                address_number = :num,
                                neighborhood = :neigh,
                                phone = COALESCE(:phone, phone)
                            WHERE id = :cid
                        ");
                        $stmtUpdateClient->execute([
                            'addr' => $deliveryAddress,
                            'num' => $deliveryNumber,
                            'neigh' => $deliveryNeighborhood,
                            'phone' => $deliveryPhone ?: null,
                            'cid' => $clientIdForOrder
                        ]);
                    } 
                    // Se não tem cliente e tem nome, cria um novo
                    elseif ($deliveryName) {
                        $stmtNewClient = $conn->prepare("
                            INSERT INTO clients (restaurant_id, name, phone, address, address_number, neighborhood)
                            VALUES (:rid, :name, :phone, :addr, :num, :neigh)
                        ");
                        $stmtNewClient->execute([
                            'rid' => $restaurant_id,
                            'name' => $deliveryName,
                            'phone' => $deliveryPhone,
                            'addr' => $deliveryAddress,
                            'num' => $deliveryNumber,
                            'neigh' => $deliveryNeighborhood
                        ]);
                        $clientIdForOrder = $conn->lastInsertId();
                        
                        // Vincula o novo cliente ao pedido
                        $conn->prepare("UPDATE orders SET client_id = :cid WHERE id = :oid")
                             ->execute(['cid' => $clientIdForOrder, 'oid' => $orderId]);
                    }
                }

                // 2. Salva os Pagamentos na tabela nova (order_payments)
                $stmtPay = $conn->prepare("INSERT INTO order_payments (order_id, method, amount) VALUES (:oid, :method, :amount)");
                
                // Salva pagamentos (só se houver)
                foreach ($payments as $pay) {
                    $stmtPay->execute(['oid' => $orderId, 'method' => $pay['method'], 'amount' => $pay['amount']]);
                }

                // 3. Lança UMA entrada no Caixa APENAS SE PAGO
                if ($isPaid == 1 && !empty($payments)) {
                    $desc = "Venda Balcão #" . $orderId;
                    $finalAmount = max(0, $totalVenda - $discount);

                    $stmtMov = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, 'venda', :val, :desc, :oid, NOW())");
                    $stmtMov->execute([
                        'cid' => $caixa['id'],
                        'val' => $finalAmount,
                        'desc' => $desc,
                        'oid' => $orderId
                    ]);
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

            // Atualiza o total do pedido (Total final = Soma itens - Desconto + Taxa entrega)
            // Lógica: total = $totalVenda - $discount + $deliveryFee
            // Mas o INSERT lá me cima setou total = 0.
            // Aqui fazemos UPDATE. 
            // CUIDADO: O código original fazia `SET total = total + :val`.
            // Se eu mudar para `SET total = :val - :discount` fica mais seguro.
            
            // [NOVO] Taxa de entrega (apenas para delivery)
            $deliveryFee = floatval($input['delivery_fee'] ?? 0);
            
            $finalTotalOrder = max(0, $totalVenda - ($discount ?? 0) + $deliveryFee);
            $conn->prepare("UPDATE orders SET total = :val WHERE id = :oid")
                 ->execute(['val' => $finalTotalOrder, 'oid' => $orderId]);

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
        $caixa = $this->getCaixaAberto($conn, $restaurant_id);

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

                // Salva pagamentos
                foreach ($payments as $pay) {
                    $stmtPay->execute(['oid' => $orderId, 'method' => $pay['method'], 'amount' => $pay['amount']]);
                }

                // Lança UMA entrada no Caixa com o TOTAL da venda
                $desc = "Mesa #" . $mesa['number'];
                $stmtMov = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, 'venda', :val, :desc, :oid, NOW())");
                $stmtMov->execute([
                    'cid' => $caixa['id'],
                    'val' => $mesa['total'], // TOTAL da mesa
                    'desc' => $desc,
                    'oid' => $orderId
                ]);

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



    // --- FECHAR COMANDA (SEM MESA) ---
    public function closeCommand() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $data = json_decode(file_get_contents('php://input'), true);
        $order_id = $data['order_id'] ?? null;
        $restaurant_id = $_SESSION['loja_ativa_id'];

        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Pedido inválido']);
            exit;
        }

        $conn = Database::connect();
        
        // 🛑 VERIFICA CAIXA
        $caixa = $this->getCaixaAberto($conn, $restaurant_id);

        if (!$caixa) {
            echo json_encode(['success' => false, 'message' => 'Caixa FECHADO! Abra o caixa para receber.']);
            exit;
        }

        try {
            $conn->beginTransaction();

            $keepOpen = $data['keep_open'] ?? false;
            
            // Verifica status atual
            $stmtCheck = $conn->prepare("SELECT is_paid, total FROM orders WHERE id = :oid");
            $stmtCheck->execute(['oid' => $order_id]);
            $currentOrder = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            // SE JÁ ESTÁ PAGO e NÃO é pra manter aberto -> Apenas Finaliza (Entrega)
            if ($currentOrder['is_paid'] == 1 && !$keepOpen) {
                 $conn->prepare("UPDATE orders SET status = 'concluido' WHERE id = :oid")
                      ->execute(['oid' => $order_id]);
                 $conn->commit();
                 echo json_encode(['success' => true]);
                 exit;
            }

            // SE NÃO ESTÁ PAGO, PRECISA DE PAGAMENTOS
            $payments = $data['payments'] ?? [];
            if ($currentOrder['is_paid'] == 0 && empty($payments)) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Nenhum pagamento informado']);
                exit;
            }

            // Se tem pagamentos, processa
            if (!empty($payments)) {
                $mainMethod = $payments[0]['method'] ?? 'dinheiro';
                $paymentMethodDesc = (count($payments) > 1) ? 'multiplo' : $mainMethod;

                // Registra Pagamentos
                $stmtPay = $conn->prepare("INSERT INTO order_payments (order_id, method, amount) VALUES (:oid, :method, :amount)");

                // Salva pagamentos e calcula total
                $totalPago = 0;
                foreach ($payments as $pay) {
                    $stmtPay->execute(['oid' => $order_id, 'method' => $pay['method'], 'amount' => $pay['amount']]);
                    $totalPago += $pay['amount'];
                }

                // Lança UMA entrada no Caixa com o TOTAL
                $desc = "Comanda #" . $order_id;
                $stmtMov = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, 'venda', :val, :desc, :oid, NOW())");
                $stmtMov->execute([
                    'cid' => $caixa['id'],
                    'val' => $totalPago, // TOTAL pago
                    'desc' => $desc,
                    'oid' => $order_id
                ]);
                
                // Define como PAGO
                $conn->prepare("UPDATE orders SET is_paid = 1, payment_method = :method WHERE id = :oid")
                     ->execute(['oid' => $order_id, 'method' => $paymentMethodDesc]);
            }

            // SE NÃO FOR PRA MANTER ABERTO, FECHA
            if (!$keepOpen) {
                $conn->prepare("UPDATE orders SET status = 'concluido' WHERE id = :oid")
                     ->execute(['oid' => $order_id]);
            }

            $conn->commit();
            echo json_encode(['success' => true]);

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

    // --- ENTREGAR PEDIDO (Retirada - Finalizar pedido pago aguardando) ---
    public function deliverOrder() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;

        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'ID do pedido não informado']);
            exit;
        }

        $conn = Database::connect();
        
        try {
            // Verifica se o pedido existe e está pago
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :oid AND restaurant_id = :rid");
            $stmt->execute(['oid' => $orderId, 'rid' => $_SESSION['loja_ativa_id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
                exit;
            }

            // Marca como concluído (entregue)
            $conn->prepare("UPDATE orders SET status = 'concluido' WHERE id = :oid")
                 ->execute(['oid' => $orderId]);

            echo json_encode(['success' => true, 'message' => 'Pedido entregue com sucesso!']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- CANCELAR PEDIDO (Remove do caixa e marca como cancelado) ---
    public function cancelOrder() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;

        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'ID do pedido não informado']);
            exit;
        }

        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();
            
            // Verifica se o pedido existe
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :oid AND restaurant_id = :rid");
            $stmt->execute(['oid' => $orderId, 'rid' => $_SESSION['loja_ativa_id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
                exit;
            }

            // Remove do cash_movements (estorna)
            $conn->prepare("DELETE FROM cash_movements WHERE order_id = :oid")
                 ->execute(['oid' => $orderId]);

            // Remove pagamentos
            $conn->prepare("DELETE FROM order_payments WHERE order_id = :oid")
                 ->execute(['oid' => $orderId]);

            // Marca como cancelado
            $conn->prepare("UPDATE orders SET status = 'cancelado' WHERE id = :oid")
                 ->execute(['oid' => $orderId]);

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Pedido cancelado com sucesso!']);

        } catch (\Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- INCLUIR ITENS EM PEDIDO PAGO (Adiciona itens, registra pagamento, atualiza total) ---
    public function includePaidOrderItems() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        $cart = $input['cart'] ?? [];
        $payments = $input['payments'] ?? [];

        if (!$orderId || empty($cart)) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
            exit;
        }

        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();
            
            // Verifica se o pedido existe
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :oid AND restaurant_id = :rid");
            $stmt->execute(['oid' => $orderId, 'rid' => $_SESSION['loja_ativa_id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
                exit;
            }

            // Calcula total dos novos itens
            $newTotal = 0;
            
            // Adiciona os novos itens
            foreach ($cart as $item) {
                $qty = intval($item['quantity'] ?? 1);
                $price = floatval($item['price'] ?? 0);
                $itemTotal = $qty * $price;
                $newTotal += $itemTotal;
                
                $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:oid, :pid, :qty, :price)")
                     ->execute([
                         'oid' => $orderId,
                         'pid' => $item['id'],
                         'qty' => $qty,
                         'price' => $price
                     ]);
            }

            // Registra os pagamentos dos novos itens
            foreach ($payments as $p) {
                $conn->prepare("INSERT INTO order_payments (order_id, method, amount) VALUES (:oid, :method, :amount)")
                     ->execute([
                         'oid' => $orderId,
                         'method' => $p['method'],
                         'amount' => floatval($p['amount'])
                     ]);
            }

            // Atualiza o total do pedido
            $updatedTotal = floatval($order['total']) + $newTotal;
            $conn->prepare("UPDATE orders SET total = :total WHERE id = :oid")
                 ->execute(['total' => $updatedTotal, 'oid' => $orderId]);

            // Registra no cash_movements
            if (!empty($payments)) {
                $paymentTotal = array_sum(array_column($payments, 'amount'));
                $conn->prepare("INSERT INTO cash_movements (restaurant_id, type, amount, description, date, order_id) VALUES (:rid, 'entrada', :amount, :desc, NOW(), :oid)")
                     ->execute([
                         'rid' => $_SESSION['loja_ativa_id'],
                         'amount' => $paymentTotal,
                         'desc' => 'Inclusão Pedido #' . $orderId,
                         'oid' => $orderId
                     ]);
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Itens incluídos com sucesso!', 'new_total' => $updatedTotal]);

        } catch (\Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Helper: Retorna o caixa aberto ou null se não existir
     * Centraliza verificação que se repete em vários métodos
     */
    private function getCaixaAberto($conn, $restaurantId) {
        $stmt = $conn->prepare("SELECT id FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
