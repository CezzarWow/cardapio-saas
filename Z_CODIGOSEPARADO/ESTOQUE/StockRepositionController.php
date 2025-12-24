<?php
// LOCALIZAÇÃO ORIGINAL: app/Controllers/Admin/StockRepositionController.php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

/**
 * [FASE 3] Controller de Reposição de Estoque
 * Responsável pelo ajuste operacional de estoque (entrada/saída)
 * Separado do cadastro de produtos (StockController)
 */
class StockRepositionController {

    // 1. LISTAR PRODUTOS PARA REPOSIÇÃO
    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        
        // Busca produtos com o nome da categoria
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.restaurant_id = :rid 
                ORDER BY p.name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $_SESSION['loja_ativa_id']]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Busca categorias para o filtro
        $stmtCat = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmtCat->execute(['rid' => $_SESSION['loja_ativa_id']]);
        $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/reposition/index.php';
    }

    // 2. AJUSTAR ESTOQUE (INCREMENTAL)
    public function adjust() {
        header('Content-Type: application/json');
        $this->checkSession();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $productId = intval($data['product_id'] ?? 0);
        $amount = intval($data['amount'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Produto inválido']);
            return;
        }

        if ($amount == 0) {
            echo json_encode(['success' => false, 'message' => 'Quantidade não pode ser zero']);
            return;
        }

        $conn = Database::connect();

        try {
            // Verifica se produto pertence à loja (multi-tenant)
            $stmt = $conn->prepare("SELECT id, stock, name FROM products WHERE id = :id AND restaurant_id = :rid");
            $stmt->execute(['id' => $productId, 'rid' => $restaurantId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
                return;
            }

            // Guarda estoque antes do ajuste
            $stockBefore = intval($product['stock']);
            $stockAfter = $stockBefore + $amount;

            // Ajuste INCREMENTAL: stock = stock + amount
            // (amount pode ser positivo ou negativo)
            $stmtUpdate = $conn->prepare("UPDATE products SET stock = stock + :amount WHERE id = :id AND restaurant_id = :rid");
            $stmtUpdate->execute([
                'amount' => $amount,
                'id' => $productId,
                'rid' => $restaurantId
            ]);

            // [FASE 4] Registra movimentação no histórico
            $movementType = $amount > 0 ? 'entrada' : 'saida';
            $movementQty = abs($amount); // Sempre positivo
            
            $stmtMov = $conn->prepare("INSERT INTO stock_movements 
                (restaurant_id, product_id, type, quantity, stock_before, stock_after, source) 
                VALUES (:rid, :pid, :type, :qty, :before, :after, 'reposicao')");
            $stmtMov->execute([
                'rid' => $restaurantId,
                'pid' => $productId,
                'type' => $movementType,
                'qty' => $movementQty,
                'before' => $stockBefore,
                'after' => $stockAfter
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Estoque ajustado com sucesso',
                'new_stock' => $stockAfter,
                'product_name' => $product['name']
            ]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao ajustar estoque: ' . $e->getMessage()]);
        }
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
