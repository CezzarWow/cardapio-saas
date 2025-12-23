<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

/**
 * [FASE 4] Controller de Movimentações de Estoque
 * Responsável pela listagem do histórico de movimentações
 * Somente leitura - sem edição ou exclusão
 */
class StockMovementController {

    // LISTAR MOVIMENTAÇÕES
    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        // Filtros opcionais
        $productFilter = $_GET['product'] ?? '';
        $categoryFilter = $_GET['category'] ?? '';

        // Query base com JOIN para pegar nome do produto e categoria
        $sql = "SELECT m.*, p.name as product_name, p.image as product_image, c.name as category_name
                FROM stock_movements m
                INNER JOIN products p ON m.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE m.restaurant_id = :rid";
        
        $params = ['rid' => $restaurantId];

        // Filtro por produto
        if (!empty($productFilter)) {
            $sql .= " AND p.id = :pid";
            $params['pid'] = $productFilter;
        }

        // Filtro por categoria
        if (!empty($categoryFilter)) {
            $sql .= " AND c.name = :cat";
            $params['cat'] = $categoryFilter;
        }

        $sql .= " ORDER BY m.created_at DESC LIMIT 100";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Busca produtos para o filtro
        $stmtProd = $conn->prepare("SELECT id, name FROM products WHERE restaurant_id = :rid ORDER BY name");
        $stmtProd->execute(['rid' => $restaurantId]);
        $products = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

        // Busca categorias para o filtro
        $stmtCat = $conn->prepare("SELECT DISTINCT name FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmtCat->execute(['rid' => $restaurantId]);
        $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/movements/index.php';
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
