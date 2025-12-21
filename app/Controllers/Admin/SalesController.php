<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class SalesController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->checkSession();

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $conn = Database::connect();

        // Busca Vendas (Calculando total real)
        $sql = "SELECT o.*, t.number as table_number,
                COALESCE((SELECT SUM(i.price * i.quantity) FROM order_items i WHERE i.order_id = o.id), 0) as calculated_total
                FROM orders o 
                LEFT JOIN tables t ON t.current_order_id = o.id -- Tenta achar mesa atual (se houver)
                WHERE o.restaurant_id = :rid 
                ORDER BY o.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurant_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/sales/index.php';
    }

    // --- REABRIR MESA (Ocupa a mesa de novo e estorna o caixa) ---
    public function reopen() {
        // ... (Mantido por compatibilidade se necessário, mas usaremos reactivateTable)
    }

    // --- CANCELAR VENDA (Estorna Estoque + Caixa) ---
    public function cancel() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = $data['id'];

        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();

            // 1. Verifica o Pedido
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id AND status = 'concluido'");
            $stmt->execute(['id' => $orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new \Exception("Pedido não encontrado ou já cancelado.");
            }

            // 2. Devolve Estoque
            $stmtItems = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :oid");
            $stmtItems->execute(['oid' => $orderId]);
            $items = $stmtItems->fetchAll();

            foreach ($items as $item) {
                $conn->prepare("UPDATE products SET stock = stock + :qtd WHERE id = :pid")
                     ->execute(['qtd' => $item['quantity'], 'pid' => $item['product_id']]);
            }

            // 3. Estorna o Caixa (Apaga a entrada de dinheiro)
            $conn->prepare("DELETE FROM cash_movements WHERE order_id = :oid")
                 ->execute(['oid' => $orderId]);

            // 4. Marca como Cancelado
            $conn->prepare("UPDATE orders SET status = 'cancelado' WHERE id = :id")
                 ->execute(['id' => $orderId]);

            $conn->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- REABRIR MESA (Versão Inteligente) ---
    public function reactivateTable() {
        header('Content-Type: application/json');
        $this->checkSession();
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = $data['id'];
        
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Acha o movimento do caixa para saber qual mesa era (pela descrição "Mesa #X")
            $stmtMov = $conn->prepare("SELECT description FROM cash_movements WHERE order_id = :oid");
            $stmtMov->execute(['oid' => $orderId]);
            $mov = $stmtMov->fetch();

            if (!$mov) throw new \Exception("Pagamento não encontrado no caixa.");
            
            // Extrai o número da mesa da string "Pagamento Mesa #5"
            preg_match('/#(\d+)/', $mov['description'], $matches);
            $tableNum = $matches[1] ?? null;

            if (!$tableNum) throw new \Exception("Não foi possível identificar a mesa.");

            // 2. Verifica se a mesa está LIVRE
            $stmtTable = $conn->prepare("SELECT id, status FROM tables WHERE number = :num AND restaurant_id = :rid");
            $stmtTable->execute(['num' => $tableNum, 'rid' => $_SESSION['loja_ativa_id']]);
            $table = $stmtTable->fetch();

            if ($table['status'] == 'ocupada') {
                throw new \Exception("A Mesa $tableNum já está ocupada por outro cliente!");
            }

            // 3. Reverte tudo
            // Volta status do pedido
            $conn->prepare("UPDATE orders SET status = 'aberto' WHERE id = :oid")->execute(['oid' => $orderId]);
            
            // Ocupa a mesa de novo com esse pedido
            $conn->prepare("UPDATE tables SET status = 'ocupada', current_order_id = :oid WHERE id = :tid")
                 ->execute(['oid' => $orderId, 'tid' => $table['id']]);

            // Remove o dinheiro do caixa (pois a conta foi 'reaberta')
            $conn->prepare("DELETE FROM cash_movements WHERE order_id = :oid")->execute(['oid' => $orderId]);

            $conn->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getItems() {
        header('Content-Type: application/json');
        $order_id = $_GET['id'] ?? 0;
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $order_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            echo json_encode(['success'=>false, 'message'=>'Erro sessão']);
            exit;
        }
    }
}
