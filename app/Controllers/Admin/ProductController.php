<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class ProductController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ../../admin');
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $mesa_id = $_GET['mesa_id'] ?? null;
        $mesa_numero = $_GET['mesa_numero'] ?? null;
        
        $conn = Database::connect();

        // 1. Se for Mesa, busca se tem pedido ABERTO nela
        $contaAberta = null;
        $itensJaPedidos = [];

        if ($mesa_id) {
            $stmtMesa = $conn->prepare("SELECT * FROM tables WHERE id = :tid AND restaurant_id = :rid");
            $stmtMesa->execute(['tid' => $mesa_id, 'rid' => $restaurant_id]);
            $mesaDados = $stmtMesa->fetch(PDO::FETCH_ASSOC);

            // Se a mesa estiver ocupada e tiver um pedido vinculado
            if ($mesaDados && $mesaDados['status'] == 'ocupada' && $mesaDados['current_order_id']) {
                
                // Busca o valor total do pedido
                $stmtOrder = $conn->prepare("SELECT * FROM orders WHERE id = :oid");
                $stmtOrder->execute(['oid' => $mesaDados['current_order_id']]);
                $contaAberta = $stmtOrder->fetch(PDO::FETCH_ASSOC);

                // Busca os itens já lançados
                $stmtItens = $conn->prepare("SELECT * FROM order_items WHERE order_id = :oid");
                $stmtItens->execute(['oid' => $mesaDados['current_order_id']]);
                $itensJaPedidos = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

                // RECÁLCULO DE SEGURANÇA: Soma os itens para garantir que o total bata
                $totalReal = 0;
                foreach ($itensJaPedidos as $item) {
                    $totalReal += ($item['price'] * $item['quantity']);
                }
                $contaAberta['total'] = $totalReal;
            }
        }

        // 2. Busca Categorias e Produtos (Padrão)
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY ordem ASC");
        $stmt->execute(['rid' => $restaurant_id]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categories as &$cat) {
            $stmtProd = $conn->prepare("SELECT * FROM products WHERE category_id = :cid AND is_active = 1");
            $stmtProd->execute(['cid' => $cat['id']]);
            $cat['products'] = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
        }

        require __DIR__ . '/../../../views/admin/panel/dashboard.php';
    }
}
