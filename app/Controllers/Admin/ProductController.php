<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class ProductController {

    public function index() {
        // 1. Segurança: Só entra se estiver logado numa loja
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ../../admin');
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $conn = Database::connect();

        // 2. Busca as categorias da loja
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY ordem ASC");
        $stmt->execute(['rid' => $restaurant_id]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Busca os produtos de cada categoria
        foreach ($categories as &$cat) {
            $stmtProd = $conn->prepare("SELECT * FROM products WHERE category_id = :cid AND is_active = 1");
            $stmtProd->execute(['cid' => $cat['id']]);
            $cat['products'] = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
        }

        // 4. Manda tudo para o Dashboard (View)
        // A variável $categories vai cheia de produtos para lá
        require __DIR__ . '/../../../views/admin/panel/dashboard.php';
    }
}
