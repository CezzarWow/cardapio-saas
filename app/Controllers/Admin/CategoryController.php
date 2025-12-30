<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

/**
 * Controller de Categorias - Layout Moderno
 * CRUD completo com padrão de sub-abas do Estoque
 */
class CategoryController {

    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :id ORDER BY name ASC");
        $stmt->execute(['id' => $restaurantId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/categories/index.php';
    }

    public function store() {
        $this->checkSession();
        
        $name = trim($_POST['name'] ?? '');
        $restaurantId = $_SESSION['loja_ativa_id'];

        if (!empty($name)) {
            $conn = Database::connect();
            $stmt = $conn->prepare("INSERT INTO categories (restaurant_id, name) VALUES (:rid, :name)");
            $stmt->execute([
                'rid' => $restaurantId,
                'name' => $name
            ]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/categorias?success=criado');
        exit;
    }

    public function edit() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        $id = intval($_GET['id'] ?? 0);
        
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            header('Location: ' . BASE_URL . '/admin/loja/categorias');
            exit;
        }

        require __DIR__ . '/../../../views/admin/categories/edit.php';
    }

    public function update() {
        $this->checkSession();
        
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id > 0 && !empty($name)) {
            $conn = Database::connect();
            $stmt = $conn->prepare("UPDATE categories SET name = :name WHERE id = :id AND restaurant_id = :rid");
            $stmt->execute([
                'name' => $name,
                'id' => $id,
                'rid' => $restaurantId
            ]);
        }

        header('Location: ' . BASE_URL . '/admin/loja/categorias?success=atualizado');
        exit;
    }
    
    public function delete() {
        $this->checkSession();
        
        $id = intval($_GET['id'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'];

        if ($id > 0) {
            $conn = Database::connect();
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id AND restaurant_id = :rid");
            $stmt->execute([
                'id' => $id,
                'rid' => $restaurantId
            ]);
        }
        
        header('Location: ' . BASE_URL . '/admin/loja/categorias?success=deletado');
        exit;
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
