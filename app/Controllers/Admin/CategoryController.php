<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class CategoryController {

    // 1. LISTAR (Mostra a tabela de categorias da loja)
    public function index() {
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ../../admin'); // Chuta de volta se não tiver logado
            exit;
        }

        $restaurant_id = $_SESSION['loja_ativa_id'];
        $conn = Database::connect();

        // Busca apenas as categorias DESTA loja
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :id ORDER BY ordem ASC");
        $stmt->execute(['id' => $restaurant_id]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/categories/index.php';
    }

    // 2. SALVAR (Recebe o formulário e grava)
    public function store() {
        if (!isset($_SESSION['loja_ativa_id'])) {
            die("Erro de sessão.");
        }

        $name = $_POST['name'];
        $restaurant_id = $_SESSION['loja_ativa_id']; // Pega o ID da sessão (Segurança)

        $conn = Database::connect();
        
        try {
            $stmt = $conn->prepare("INSERT INTO categories (restaurant_id, name) VALUES (:rid, :name)");
            $stmt->execute([
                'rid' => $restaurant_id,
                'name' => $name
            ]);

            // Volta para a lista
            header('Location: ../categories');
            exit;

        } catch (\PDOException $e) {
            echo "Erro ao salvar: " . $e->getMessage();
        }
    }
    
    // 3. DELETAR
    public function delete() {
        $id = $_GET['id'];
        $conn = Database::connect();
        // Só apaga se pertencer à loja ativa (Segurança Dupla)
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute([
            'id' => $id,
            'rid' => $_SESSION['loja_ativa_id']
        ]);
        
        header('Location: ../categories');
    }
}
