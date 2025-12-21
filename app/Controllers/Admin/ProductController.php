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

        // --- VERIFICA SE TEM CARRINHO RECUPERADO (EDIÇÃO) ---
        $cartRecovery = [];
        $isEditing = false; // Variável nova

        if (isset($_SESSION['cart_recovery'])) {
            $cartRecovery = $_SESSION['cart_recovery'];
            $isEditing = true; // Estamos em modo edição!
            // NÃO unset a sessão aqui ainda, para permitir refresh da página
        }

        require __DIR__ . '/../../../views/admin/panel/dashboard.php';
    }

    // --- CANCELAR EDIÇÃO (RESTAURA TUDO) ---
    public function cancelEdit() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['edit_backup'])) {
            header('Location: ../pdv'); // Se não tem backup, só volta
            exit;
        }

        $backup = $_SESSION['edit_backup'];
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            // 1. Restaura o Pedido (Mantendo o ID original para consistência)
            $stmtOrder = $conn->prepare("INSERT INTO orders (id, restaurant_id, total, status, payment_method, created_at) VALUES (:id, :rid, :total, :status, :pay, :date)");
            $stmtOrder->execute([
                'id' => $backup['order']['id'],
                'rid' => $backup['order']['restaurant_id'],
                'total' => $backup['order']['total'],
                'status' => $backup['order']['status'],
                'pay' => $backup['order']['payment_method'],
                'date' => $backup['order']['created_at']
            ]);

            // 2. Restaura Itens e Baixa Estoque Novamente
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, quantity, price) VALUES (:oid, :pid, :name, :qtd, :price)");
            $stmtStock = $conn->prepare("UPDATE products SET stock = stock - :qtd WHERE id = :pid");

            foreach ($backup['items'] as $item) {
                $stmtItem->execute([
                    'oid' => $backup['order']['id'],
                    'pid' => $item['id'],
                    'name' => $item['name'],
                    'qtd' => $item['quantity'],
                    'price' => $item['price']
                ]);
                $stmtStock->execute(['qtd' => $item['quantity'], 'pid' => $item['id']]);
            }

            // 3. Restaura Movimento do Caixa
            $stmtMov = $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, :type, :amount, :desc, :oid, :date)");
            $stmtMov->execute([
                'cid' => $backup['movement']['cash_register_id'],
                'type' => $backup['movement']['type'],
                'amount' => $backup['movement']['amount'],
                'desc' => $backup['movement']['description'],
                'oid' => $backup['order']['id'],
                'date' => $backup['movement']['created_at']
            ]);

            $conn->commit();

            // Limpa sessões
            unset($_SESSION['edit_backup']);
            unset($_SESSION['cart_recovery']);

            header('Location: ../caixa'); // Volta pro caixa como se nada tivesse acontecido

        } catch (\Exception $e) {
            $conn->rollBack();
            die("Erro ao cancelar edição: " . $e->getMessage());
        }
    }
}
