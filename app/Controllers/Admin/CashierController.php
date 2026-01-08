<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use App\Validators\CashierValidator;
use PDO;

/**
 * CashierController - Gerenciamento de Caixa (Refatorado v2)
 */
class CashierController extends BaseController {

    private const BASE = '/admin/loja/caixa';
    private CashierValidator $v;

    public function __construct() {
        $this->v = new CashierValidator();
    }

    // === DASHBOARD DO CAIXA ===
    public function index() {
        $rid = $this->getRestaurantId();
        $conn = Database::connect();

        // Busca caixa aberto
        $caixa = $this->getOpenCashier($conn, $rid);
        if (!$caixa) {
            require __DIR__ . '/../../../views/admin/cashier/open.php';
            return;
        }

        // Totais de vendas por método de pagamento
        $resumo = $this->calculateSalesSummary($conn, $rid, $caixa['opened_at']);
        
        // Movimentações (sangrias/suprimentos)
        $movimentos = $this->getMovements($conn, $caixa['id']);
        list($totalSuprimentos, $totalSangrias) = $this->sumMovements($movimentos);
        
        // Dinheiro físico em caixa
        $dinheiroEmCaixa = $caixa['opening_balance'] + $resumo['dinheiro'] + $totalSuprimentos - $totalSangrias;

        require __DIR__ . '/../../../views/admin/cashier/dashboard.php';
    }

    // === ABRIR CAIXA ===
    public function open() {
        $this->handleValidatedPost(
            fn() => $this->v->validateOpenCashier($_POST),
            fn() => $this->v->sanitizeOpenCashier($_POST),
            fn($data, $rid) => $this->openCashier($rid, $data['opening_balance']),
            self::BASE, 'aberto'
        );
    }

    // === FECHAR CAIXA ===
    public function close() {
        $rid = $this->getRestaurantId();
        $conn = Database::connect();
        
        $conn->prepare("UPDATE cash_registers SET status = 'fechado', closed_at = NOW() WHERE restaurant_id = :rid AND status = 'aberto'")
             ->execute(['rid' => $rid]);

        $this->redirect(self::BASE);
    }

    // === ADICIONAR SANGRIA/SUPRIMENTO ===
    public function addMovement() {
        $this->handleValidatedPost(
            fn() => $this->v->validateMovement($_POST),
            fn() => $this->v->sanitizeMovement($_POST),
            fn($data, $rid) => $this->createMovement($rid, $data),
            self::BASE, 'movimento_adicionado'
        );
    }

    // === REVERTER PARA PDV (Edição) ===
    public function reverseToPdv() {
        $rid = $this->getRestaurantId();
        $movementId = $this->getInt('id');
        
        if ($movementId <= 0) {
            $this->redirect(self::BASE . '?error=id_invalido');
        }
        
        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();
            
            $mov = $this->getMovement($conn, $movementId);
            if (!$mov || !$mov['order_id']) {
                throw new \Exception('Movimento inválido');
            }
            
            $order = $this->getOrder($conn, $mov['order_id']);
            $items = $this->getOrderItems($conn, $mov['order_id']);
            
            // Backup na sessão
            $_SESSION['edit_backup'] = ['movement' => $mov, 'order' => $order, 'items' => $items];
            
            // Devolve estoque
            $this->restoreStock($conn, $items);
            
            // Apaga registros
            $this->deleteMovementAndOrder($conn, $movementId, $mov['order_id']);
            
            $conn->commit();
            
            // Manda itens pro carrinho
            $_SESSION['cart_recovery'] = $items;
            $this->redirect('/admin/loja/pdv?mode=edit');
            
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log('reverseToPdv Error: ' . $e->getMessage());
            $this->redirect(self::BASE . '?error=operacao_falhou');
        }
    }

    // === REVERTER PARA MESA ===
    public function reverseToTable() {
        $rid = $this->getRestaurantId();
        $movementId = $this->getInt('id');
        
        if ($movementId <= 0) {
            $this->redirect(self::BASE . '?error=id_invalido');
        }
        
        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();
            
            $mov = $this->getMovement($conn, $movementId);
            
            // Extrai número da mesa da descrição
            preg_match('/#(\d+)/', $mov['description'], $matches);
            $mesaNumero = $matches[1] ?? null;
            
            if (!$mesaNumero) {
                throw new \Exception('Não foi possível identificar a mesa');
            }
            
            $mesa = $this->getTableByNumber($conn, $mesaNumero, $rid);
            
            // Reverte status do pedido
            $conn->prepare("UPDATE orders SET status = 'aberto' WHERE id = :oid")
                 ->execute(['oid' => $mov['order_id']]);
            
            // Ocupa a mesa novamente
            $conn->prepare("UPDATE tables SET status = 'ocupada', current_order_id = :oid WHERE id = :tid")
                 ->execute(['oid' => $mov['order_id'], 'tid' => $mesa['id']]);
            
            // Apaga movimento
            $conn->prepare("DELETE FROM cash_movements WHERE id = :id")->execute(['id' => $movementId]);
            
            $conn->commit();
            $this->redirect('/admin/loja/mesas');
            
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log('reverseToTable Error: ' . $e->getMessage());
            $this->redirect(self::BASE . '?error=operacao_falhou');
        }
    }

    // === REMOVER MOVIMENTO ===
    public function removeMovement() {
        $rid = $this->getRestaurantId();
        $movementId = $this->getInt('id');
        
        if ($movementId <= 0) {
            $this->redirect(self::BASE . '?error=id_invalido');
        }
        
        $conn = Database::connect();
        
        try {
            $conn->beginTransaction();
            
            $mov = $this->getMovement($conn, $movementId);
            if (!$mov) {
                throw new \Exception('Movimento não encontrado');
            }
            
            // Se for venda, cancela pedido e devolve estoque
            if ($mov['type'] == 'venda' && $mov['order_id']) {
                $items = $this->getOrderItemsSimple($conn, $mov['order_id']);
                $this->restoreStock($conn, $items);
                
                $conn->prepare("UPDATE orders SET status = 'cancelado' WHERE id = :oid")
                     ->execute(['oid' => $mov['order_id']]);
            }
            
            // Apaga movimento
            $conn->prepare("DELETE FROM cash_movements WHERE id = :id")->execute(['id' => $movementId]);
            
            $conn->commit();
            $this->redirect(self::BASE);
            
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log('removeMovement Error: ' . $e->getMessage());
            $this->redirect(self::BASE . '?error=operacao_falhou');
        }
    }

    // ============================================
    // MÉTODOS PRIVADOS
    // ============================================

    private function openCashier(int $rid, float $balance): void {
        $conn = Database::connect();
        $conn->prepare("INSERT INTO cash_registers (restaurant_id, opening_balance, status, opened_at) VALUES (:rid, :val, 'aberto', NOW())")
             ->execute(['rid' => $rid, 'val' => $balance]);
    }

    private function createMovement(int $rid, array $data): void {
        $conn = Database::connect();
        $caixa = $this->getOpenCashier($conn, $rid);
        
        if (!$caixa) {
            throw new \Exception('Nenhum caixa aberto');
        }
        
        $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description) VALUES (:cid, :type, :amount, :desc)")
             ->execute(['cid' => $caixa['id'], 'type' => $data['type'], 'amount' => $data['amount'], 'desc' => $data['description']]);
    }

    private function getOpenCashier($conn, int $rid): ?array {
        $stmt = $conn->prepare("SELECT * FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmt->execute(['rid' => $rid]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function calculateSalesSummary($conn, int $rid, string $openedAt): array {
        $stmt = $conn->prepare("
            SELECT op.method, SUM(op.amount) as total 
            FROM order_payments op
            INNER JOIN orders o ON o.id = op.order_id
            WHERE o.restaurant_id = :rid 
            AND o.created_at >= :opened_at 
            AND o.status = 'concluido'
            GROUP BY op.method
        ");
        $stmt->execute(['rid' => $rid, 'opened_at' => $openedAt]);
        $vendas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $resumo = [
            'total_bruto' => 0,
            'dinheiro' => $vendas['dinheiro'] ?? 0,
            'credito' => $vendas['credito'] ?? 0,
            'debito' => $vendas['debito'] ?? 0,
            'pix' => $vendas['pix'] ?? 0,
        ];
        $resumo['total_bruto'] = array_sum($resumo);
        
        return $resumo;
    }

    private function getMovements($conn, int $cashierId): array {
        $stmt = $conn->prepare("SELECT * FROM cash_movements WHERE cash_register_id = :cid ORDER BY created_at DESC");
        $stmt->execute(['cid' => $cashierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function sumMovements(array $movimentos): array {
        $suprimentos = 0;
        $sangrias = 0;
        foreach ($movimentos as $mov) {
            if ($mov['type'] == 'suprimento') $suprimentos += $mov['amount'];
            if ($mov['type'] == 'sangria') $sangrias += $mov['amount'];
        }
        return [$suprimentos, $sangrias];
    }

    private function getMovement($conn, int $id): ?array {
        $stmt = $conn->prepare("SELECT * FROM cash_movements WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getOrder($conn, int $orderId): ?array {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getOrderItems($conn, int $orderId): array {
        $stmt = $conn->prepare("SELECT product_id as id, name, price, quantity FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getOrderItemsSimple($conn, int $orderId): array {
        $stmt = $conn->prepare("SELECT product_id as id, quantity FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTableByNumber($conn, int $number, int $rid): ?array {
        $stmt = $conn->prepare("SELECT id FROM tables WHERE number = :num AND restaurant_id = :rid");
        $stmt->execute(['num' => $number, 'rid' => $rid]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function restoreStock($conn, array $items): void {
        foreach ($items as $item) {
            $conn->prepare("UPDATE products SET stock = stock + :qtd WHERE id = :pid")
                 ->execute(['qtd' => $item['quantity'], 'pid' => $item['id']]);
        }
    }

    private function deleteMovementAndOrder($conn, int $movementId, int $orderId): void {
        $conn->prepare("DELETE FROM cash_movements WHERE id = :id")->execute(['id' => $movementId]);
        $conn->prepare("DELETE FROM order_items WHERE order_id = :oid")->execute(['oid' => $orderId]);
        $conn->prepare("DELETE FROM orders WHERE id = :oid")->execute(['oid' => $orderId]);
    }
}
