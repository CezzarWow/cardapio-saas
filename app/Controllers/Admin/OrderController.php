<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class OrderController {

    public function store() {
        // Define que a resposta será um JSON (para o Javascript entender)
        header('Content-Type: application/json');

        // 1. Segurança: Verifica sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
            exit;
        }

        // 2. Recebe o carrinho enviado pelo Javascript
        $input = json_decode(file_get_contents('php://input'), true);
        $cart = $input['cart'] ?? [];

        if (empty($cart)) {
            echo json_encode(['success' => false, 'message' => 'Carrinho vazio.']);
            exit;
        }

        $conn = Database::connect();
        
        try {
            // INÍCIO DA TRANSAÇÃO (Tudo ou Nada)
            $conn->beginTransaction();

            // 3. Calcula o total final
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // 4. Cria o Pedido (Cabeçalho)
            $stmt = $conn->prepare("INSERT INTO orders (restaurant_id, total) VALUES (:rid, :total)");
            $stmt->execute([
                'rid' => $_SESSION['loja_ativa_id'],
                'total' => $total
            ]);
            $orderId = $conn->lastInsertId(); // Pega o ID da venda gerada

            // 5. Salva os Itens e Baixa Estoque
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, quantity, price) VALUES (:oid, :pid, :name, :qtd, :price)");
            $stmtStock = $conn->prepare("UPDATE products SET stock = stock - :qtd WHERE id = :pid");

            foreach ($cart as $item) {
                // Grava item na nota
                $stmtItem->execute([
                    'oid' => $orderId,
                    'pid' => $item['id'],
                    'name' => $item['name'],
                    'qtd' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Diminui do estoque
                $stmtStock->execute([
                    'qtd' => $item['quantity'],
                    'pid' => $item['id']
                ]);
            }

            // Confirma a gravação no banco
            $conn->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            // Se der erro, desfaz tudo o que tentou gravar
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    }
}
