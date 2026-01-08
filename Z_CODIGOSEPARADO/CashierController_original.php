<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class CashierController {

    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurant_id = $_SESSION['loja_ativa_id'];

        // 1. Busca o caixa ABERTO
        $stmt = $conn->prepare("SELECT * FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmt->execute(['rid' => $restaurant_id]);
        $caixa = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não tiver caixa aberto, mostra a tela de abrir
        if (!$caixa) {
            require __DIR__ . '/../../../views/admin/cashier/open.php';
            return;
        }

        // 2. TOTAIS DOS CARDS (Resumo do Dia)
        // Busca pagamentos da tabela order_payments (suporta múltiplos pagamentos por pedido)
        $sqlTotais = "SELECT op.method, SUM(op.amount) as total 
                      FROM order_payments op
                      INNER JOIN orders o ON o.id = op.order_id
                      WHERE o.restaurant_id = :rid 
                      AND o.created_at >= :opened_at 
                      AND o.status = 'concluido'
                      GROUP BY op.method";
        
        $stmtTotais = $conn->prepare($sqlTotais);
        $stmtTotais->execute(['rid' => $restaurant_id, 'opened_at' => $caixa['opened_at']]);
        $vendas = $stmtTotais->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna array ['pix' => 100.00, 'dinheiro' => 50.00]

        // Organiza os dados para a View
        $resumo = [
            'total_bruto' => 0,
            'dinheiro' => $vendas['dinheiro'] ?? 0,
            'credito' => $vendas['credito'] ?? 0,
            'debito' => $vendas['debito'] ?? 0,
            'pix' => $vendas['pix'] ?? 0,
        ];
        $resumo['total_bruto'] = array_sum($resumo);

        // Ajuste Dinheiro: Soma Vendas em Dinheiro + Suprimentos - Sangrias
        // Busca movimentações manuais (Sangrias e Suprimentos)
        $stmtMov = $conn->prepare("SELECT * FROM cash_movements WHERE cash_register_id = :cid ORDER BY created_at DESC");
        $stmtMov->execute(['cid' => $caixa['id']]);
        $movimentos = $stmtMov->fetchAll(PDO::FETCH_ASSOC);

        $totalSuprimentos = 0;
        $totalSangrias = 0;

        foreach ($movimentos as $mov) {
            if ($mov['type'] == 'suprimento') $totalSuprimentos += $mov['amount'];
            if ($mov['type'] == 'sangria') $totalSangrias += $mov['amount'];
        }

        // O valor físico na gaveta é: Saldo Inicial + Vendas Dinheiro + Suprimentos - Sangrias
        $dinheiroEmCaixa = $caixa['opening_balance'] + $resumo['dinheiro'] + $totalSuprimentos - $totalSangrias;

        // 3. Renderiza o Dashboard
        require __DIR__ . '/../../../views/admin/cashier/dashboard.php';
    }

    // ABRIR CAIXA
    public function open() {
        $this->checkSession();
        $saldo = str_replace(',', '.', $_POST['opening_balance']);
        $rid = $_SESSION['loja_ativa_id'];

        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO cash_registers (restaurant_id, opening_balance, status, opened_at) VALUES (:rid, :val, 'aberto', NOW())");
        $stmt->execute(['rid' => $rid, 'val' => $saldo]);

        header('Location: ../caixa');
    }

    // FECHAR CAIXA
    public function close() {
        $this->checkSession();
        $rid = $_SESSION['loja_ativa_id'];
        
        $conn = Database::connect();
        // Fecha o caixa aberto
        $conn->prepare("UPDATE cash_registers SET status = 'fechado', closed_at = NOW() WHERE restaurant_id = :rid AND status = 'aberto'")
             ->execute(['rid' => $rid]);

        header('Location: ../caixa');
    }

    // ADICIONAR SANGRIA OU SUPRIMENTO
    public function addMovement() {
        $this->checkSession();
        $rid = $_SESSION['loja_ativa_id'];
        $type = $_POST['type']; // sangria ou suprimento
        $amount = str_replace(',', '.', $_POST['amount']);
        $desc = $_POST['description'];

        $conn = Database::connect();
        
        // Pega ID do caixa aberto
        $stmt = $conn->prepare("SELECT id FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmt->execute(['rid' => $rid]);
        $caixa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($caixa) {
            $stmtInsert = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description) VALUES (:cid, :type, :amount, :desc)");
            $stmtInsert->execute(['cid' => $caixa['id'], 'type' => $type, 'amount' => $amount, 'desc' => $desc]);
        }

        header('Location: ../caixa');
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) header('Location: ../../admin');
    }
    // --- AÇÃO 1: EDITAR PEDIDO (Estorna + Manda pro PDV) ---
    // --- AÇÃO 1: EDITAR PEDIDO (COM BACKUP DE SEGURANÇA) ---
    public function reverseToPdv() {
        $this->checkSession();
        $movementId = $_GET['id'];
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Busca dados do movimento
            $stmt = $conn->prepare("SELECT * FROM cash_movements WHERE id = :id");
            $stmt->execute(['id' => $movementId]);
            $mov = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$mov || !$mov['order_id']) die('Movimento inválido.');

            // 2. Busca o pedido original e os itens
            $stmtOrder = $conn->prepare("SELECT * FROM orders WHERE id = :oid");
            $stmtOrder->execute(['oid' => $mov['order_id']]);
            $oldOrder = $stmtOrder->fetch(PDO::FETCH_ASSOC);

            $stmtItems = $conn->prepare("SELECT product_id as id, name, price, quantity FROM order_items WHERE order_id = :oid");
            $stmtItems->execute(['oid' => $mov['order_id']]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // --- CRIA O BACKUP NA SESSÃO (Para poder cancelar a edição) ---
            $_SESSION['edit_backup'] = [
                'movement' => $mov,
                'order' => $oldOrder,
                'items' => $items
            ];
            // -------------------------------------------------------------

            // 3. Devolve Estoque (Igual antes)
            foreach ($items as $item) {
                $conn->prepare("UPDATE products SET stock = stock + :qtd WHERE id = :pid")
                     ->execute(['qtd' => $item['quantity'], 'pid' => $item['id']]);
            }

            // 4. Apaga Registros (Igual antes)
            $conn->prepare("DELETE FROM cash_movements WHERE id = :id")->execute(['id' => $movementId]);
            $conn->prepare("DELETE FROM order_items WHERE order_id = :oid")->execute(['oid' => $mov['order_id']]);
            $conn->prepare("DELETE FROM orders WHERE id = :oid")->execute(['oid' => $mov['order_id']]);

            $conn->commit();

            // 5. Manda itens pro carrinho
            $_SESSION['cart_recovery'] = $items;
            
            header('Location: ../pdv?mode=edit'); // Avisa o PDV que é modo edição

        } catch (\Exception $e) {
            $conn->rollBack();
            die("Erro: " . $e->getMessage());
        }
    }

    // --- AÇÃO 2: RECUPERAR MESA (Estorna + Ocupa Mesa) ---
    public function reverseToTable() {
        $this->checkSession();
        $movementId = $_GET['id'];
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Busca dados
            $stmt = $conn->prepare("SELECT order_id, description FROM cash_movements WHERE id = :id");
            $stmt->execute(['id' => $movementId]);
            $mov = $stmt->fetch(PDO::FETCH_ASSOC);

            // Tenta descobrir o número da mesa pela descrição "Pagamento Mesa #5"
            preg_match('/#(\d+)/', $mov['description'], $matches);
            $mesaNumero = $matches[1] ?? null;

            if (!$mesaNumero) die("Não foi possível identificar o número da mesa.");

            // 2. Busca a mesa pelo número
            $stmtTable = $conn->prepare("SELECT id FROM tables WHERE number = :num AND restaurant_id = :rid");
            $stmtTable->execute(['num' => $mesaNumero, 'rid' => $_SESSION['loja_ativa_id']]);
            $mesa = $stmtTable->fetch(PDO::FETCH_ASSOC);

            // 3. Reverte Status do Pedido (Concluido -> Aberto)
            $conn->prepare("UPDATE orders SET status = 'aberto' WHERE id = :oid")->execute(['oid' => $mov['order_id']]);

            // 4. Ocupa a Mesa de novo
            $conn->prepare("UPDATE tables SET status = 'ocupada', current_order_id = :oid WHERE id = :tid")
                 ->execute(['oid' => $mov['order_id'], 'tid' => $mesa['id']]);

            // 5. Apaga o dinheiro do caixa
            $conn->prepare("DELETE FROM cash_movements WHERE id = :id")->execute(['id' => $movementId]);

            $conn->commit();

            header('Location: ../mesas'); // Manda pro mapa

        } catch (\Exception $e) {
            $conn->rollBack();
            die("Erro: " . $e->getMessage());
        }
    }
    // --- AÇÃO 3: REMOVER MOVIMENTO (Apagar Sangria/Suprimento ou Cancelar Venda) ---
    public function removeMovement() {
        $this->checkSession();
        $movementId = $_GET['id'];
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Busca o movimento
            $stmt = $conn->prepare("SELECT * FROM cash_movements WHERE id = :id");
            $stmt->execute(['id' => $movementId]);
            $mov = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$mov) die('Movimento não encontrado.');

            // 2. Se for VENDA, tem que cancelar o pedido e devolver estoque
            if ($mov['type'] == 'venda' && $mov['order_id']) {
                
                // Devolve Estoque
                $stmtItems = $conn->prepare("SELECT product_id as id, quantity FROM order_items WHERE order_id = :oid");
                $stmtItems->execute(['oid' => $mov['order_id']]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                foreach ($items as $item) {
                    $conn->prepare("UPDATE products SET stock = stock + :qtd WHERE id = :pid")
                         ->execute(['qtd' => $item['quantity'], 'pid' => $item['id']]);
                }

                // Marca pedido como cancelado
                $conn->prepare("UPDATE orders SET status = 'cancelado' WHERE id = :oid")
                     ->execute(['oid' => $mov['order_id']]);
            }

            // 3. Apaga o Movimento do Caixa
            $conn->prepare("DELETE FROM cash_movements WHERE id = :id")->execute(['id' => $movementId]);

            $conn->commit();
            header('Location: ../caixa');

        } catch (\Exception $e) {
            $conn->rollBack();
            die("Erro: " . $e->getMessage());
        }
    }
}
