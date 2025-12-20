<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class StockController {

    // 1. LISTAR PRODUTOS
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

        require __DIR__ . '/../../../views/admin/stock/index.php';
    }

    // 2. TELA DE CRIAR (Formulário)
    public function create() {
        $this->checkSession();
        $conn = Database::connect();

        // Precisa listar as categorias para o <select>
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid");
        $stmt->execute(['rid' => $_SESSION['loja_ativa_id']]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/stock/create.php';
    }

    // 3. SALVAR NO BANCO (Recebe o POST)
    public function store() {
        $this->checkSession();
        
        $name = $_POST['name'];
        $price = str_replace(',', '.', $_POST['price']); // Troca vírgula por ponto
        $category_id = $_POST['category_id'];
        $description = $_POST['description'];
        $restaurant_id = $_SESSION['loja_ativa_id'];

        // --- Lógica de Upload de Imagem ---
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = md5(time() . rand(0,9999)) . '.' . $ext; // Nome único
            // Move para a pasta public/uploads
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../../public/uploads/' . $imageName);
        }

        $conn = Database::connect();
        $sql = "INSERT INTO products (restaurant_id, category_id, name, description, price, image, stock) 
                VALUES (:rid, :cid, :name, :desc, :price, :img, 100)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'rid' => $restaurant_id,
            'cid' => $category_id,
            'name' => $name,
            'desc' => $description,
            'price' => $price,
            'img' => $imageName
        ]);

        header('Location: ../produtos');
    }

    // 4. DELETAR
    public function delete() {
        $this->checkSession();
        $id = $_GET['id'];
        
        $conn = Database::connect();
        // Só apaga se for da loja logada
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $_SESSION['loja_ativa_id']]);

        header('Location: ../produtos');
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin'); // Usando BASE_URL ajustado
            exit;
        }
    }
}
