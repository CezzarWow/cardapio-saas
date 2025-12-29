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

        // Busca TODAS as categorias cadastradas (para chips)
        $stmtCat = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmtCat->execute(['rid' => $_SESSION['loja_ativa_id']]);
        $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/stock/index.php';
    }

    // 2. TELA DE CRIAR (Formulário)
    public function create() {
        $this->checkSession();
        $conn = Database::connect();

        // Precisa listar as categorias para o <select>
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $_SESSION['loja_ativa_id']]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // [NOVO] Busca Grupos de Adicionais disponíveis
        $stmtGroups = $conn->prepare("SELECT * FROM additional_groups WHERE restaurant_id = :rid ORDER BY name");
        $stmtGroups->execute(['rid' => $_SESSION['loja_ativa_id']]);
        $additionalGroups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/stock/create.php';
    }

    // 3. SALVAR NO BANCO (Recebe o POST)
    public function store() {
        $this->checkSession();
        
        $name = $_POST['name'];
        $price = str_replace(',', '.', $_POST['price']); // Troca vírgula por ponto
        $category_id = $_POST['category_id'];
        $description = $_POST['description'] ?? '';
        $restaurant_id = $_SESSION['loja_ativa_id'];
        
        // [FASE 1] Estoque vem do formulário (padrão 0 se vazio)
        $stock = isset($_POST['stock']) && $_POST['stock'] !== '' ? intval($_POST['stock']) : 0;
        
        // Arrays de grupos selecionados
        $selectedGroups = $_POST['additional_groups'] ?? [];

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
                VALUES (:rid, :cid, :name, :desc, :price, :img, :stock)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'rid' => $restaurant_id,
            'cid' => $category_id,
            'name' => $name,
            'desc' => $description,
            'price' => $price,
            'img' => $imageName,
            'stock' => $stock
        ]);
        
        // [NOVO] Salvar Vínculos
        $newProductId = $conn->lastInsertId();
        if ($newProductId && !empty($selectedGroups)) {
            $stmtIns = $conn->prepare("INSERT INTO product_additional_relations (product_id, group_id) VALUES (:pid, :gid)");
            foreach ($selectedGroups as $gid) {
                $stmtIns->execute(['pid' => $newProductId, 'gid' => $gid]);
            }
        }

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

    // 5. TELA DE EDITAR (Formulário) [FASE 1]
    public function edit() {
        $this->checkSession();
        $id = $_GET['id'];
        $conn = Database::connect();

        // Busca o produto (respeitando multi-tenant)
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $_SESSION['loja_ativa_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            header('Location: ../produtos');
            exit;
        }

        // Busca categorias para o select
        $stmtCat = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmtCat->execute(['rid' => $_SESSION['loja_ativa_id']]);
        $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        // [NOVO] Busca Grupos de Adicionais disponíveis
        $stmtGroups = $conn->prepare("SELECT * FROM additional_groups WHERE restaurant_id = :rid ORDER BY name");
        $stmtGroups->execute(['rid' => $_SESSION['loja_ativa_id']]);
        $additionalGroups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        // [NOVO] Busca Grupos já vinculados ao produto
        $stmtLinked = $conn->prepare("SELECT group_id FROM product_additional_relations WHERE product_id = :pid");
        $stmtLinked->execute(['pid' => $id]);
        $linkedGroups = $stmtLinked->fetchAll(PDO::FETCH_COLUMN);

        require __DIR__ . '/../../../views/admin/stock/edit.php';
    }

    // 6. ATUALIZAR NO BANCO (Recebe o POST) [FASE 1]
    public function update() {
        $this->checkSession();
        
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = str_replace(',', '.', $_POST['price']);
        $category_id = $_POST['category_id'];
        $description = $_POST['description'] ?? '';
        $stock = isset($_POST['stock']) && $_POST['stock'] !== '' ? intval($_POST['stock']) : 0;
        $restaurant_id = $_SESSION['loja_ativa_id'];
        
        // Arrays de grupos selecionados
        $selectedGroups = $_POST['additional_groups'] ?? [];

        $conn = Database::connect();

        // Verifica se pertence à loja
        $stmt = $conn->prepare("SELECT id, image FROM products WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurant_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            header('Location: ../produtos');
            exit;
        }

        // Lógica de Upload de Imagem (só se enviar nova)
        $imageName = $product['image']; // Mantém a atual
        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = md5(time() . rand(0,9999)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../../public/uploads/' . $imageName);
        }

        // Atualiza o produto
        $sql = "UPDATE products SET 
                    name = :name, 
                    price = :price, 
                    category_id = :cid, 
                    description = :desc, 
                    stock = :stock, 
                    image = :img 
                WHERE id = :id AND restaurant_id = :rid";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'cid' => $category_id,
            'desc' => $description,
            'stock' => $stock,
            'img' => $imageName,
            'id' => $id,
            'rid' => $restaurant_id
        ]);

        // [NOVO] Atualiza Vínculos com Adicionais
        // 1. Limpa anteriores
        $stmtDel = $conn->prepare("DELETE FROM product_additional_relations WHERE product_id = :pid");
        $stmtDel->execute(['pid' => $id]);

        // 2. Insere novos
        if (!empty($selectedGroups)) {
            $stmtIns = $conn->prepare("INSERT INTO product_additional_relations (product_id, group_id) VALUES (:pid, :gid)");
            foreach ($selectedGroups as $gid) {
                $stmtIns->execute(['pid' => $id, 'gid' => $gid]);
            }
        }

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
