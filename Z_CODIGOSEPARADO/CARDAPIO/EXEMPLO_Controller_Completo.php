/**
 * ═══════════════════════════════════════════════════════════════════════════
 * EXEMPLO DE CONTROLLER COMPLETO (BASEADO NO ESTOQUE)
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * ARQUIVO DE REFERÊNCIA: app/Controllers/Admin/StockController.php
 * 
 * Este é um exemplo de como criar um controller funcional.
 * Use como modelo para desenvolver o CardapioController.
 * ═══════════════════════════════════════════════════════════════════════════
 */

<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class ExemploController {

    /**
     * MÉTODO: index()
     * Exibe a listagem principal
     */
    public function index() {
        $this->checkSession();
        
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        // Buscar dados do banco
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.restaurant_id = :rid 
            ORDER BY p.name ASC
        ");
        $stmt->execute(['rid' => $restaurantId]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar categorias para filtros
        $stmtCat = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmtCat->execute(['rid' => $restaurantId]);
        $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
        
        // Renderizar view
        require __DIR__ . '/../../../views/admin/exemplo/index.php';
    }

    /**
     * MÉTODO: create()
     * Exibe formulário de criação
     */
    public function create() {
        $this->checkSession();
        
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        // Dados para preencher selects, etc
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $restaurantId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../../../views/admin/exemplo/create.php';
    }

    /**
     * MÉTODO: store()
     * Salva novo registro (POST)
     */
    public function store() {
        $this->checkSession();
        
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        // Pegar dados do formulário
        $name = trim($_POST['name'] ?? '');
        $price = floatval(str_replace(',', '.', str_replace('.', '', $_POST['price'] ?? '0')));
        $description = trim($_POST['description'] ?? '');
        
        // Validação básica
        if (empty($name)) {
            header('Location: ' . BASE_URL . '/admin/loja/exemplo/novo?erro=Nome obrigatório');
            exit;
        }
        
        // Inserir no banco
        $stmt = $conn->prepare("
            INSERT INTO tabela (name, price, description, restaurant_id) 
            VALUES (:name, :price, :description, :rid)
        ");
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'description' => $description,
            'rid' => $restaurantId
        ]);
        
        // Redirecionar com sucesso
        header('Location: ' . BASE_URL . '/admin/loja/exemplo');
        exit;
    }

    /**
     * MÉTODO: edit($id)
     * Exibe formulário de edição
     */
    public function edit() {
        $this->checkSession();
        
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . BASE_URL . '/admin/loja/exemplo');
            exit;
        }
        
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        // Buscar registro
        $stmt = $conn->prepare("SELECT * FROM tabela WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            header('Location: ' . BASE_URL . '/admin/loja/exemplo');
            exit;
        }
        
        require __DIR__ . '/../../../views/admin/exemplo/edit.php';
    }

    /**
     * MÉTODO: update()
     * Atualiza registro existente (POST)
     */
    public function update() {
        $this->checkSession();
        
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = floatval(str_replace(',', '.', str_replace('.', '', $_POST['price'] ?? '0')));
        
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        $stmt = $conn->prepare("
            UPDATE tabela 
            SET name = :name, price = :price 
            WHERE id = :id AND restaurant_id = :rid
        ");
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'id' => $id,
            'rid' => $restaurantId
        ]);
        
        header('Location: ' . BASE_URL . '/admin/loja/exemplo');
        exit;
    }

    /**
     * MÉTODO: delete()
     * Remove registro
     */
    public function delete() {
        $this->checkSession();
        
        $id = intval($_GET['id'] ?? 0);
        
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        // Segurança: sempre filtrar por restaurant_id
        $stmt = $conn->prepare("DELETE FROM tabela WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        
        header('Location: ' . BASE_URL . '/admin/loja/exemplo');
        exit;
    }

    /**
     * MÉTODO: checkSession()
     * Verifica se usuário está logado em um restaurante
     * SEMPRE CHAMAR NO INÍCIO DE CADA MÉTODO!
     */
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * DICAS IMPORTANTES:
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * 1. SEMPRE filtrar por restaurant_id para garantir isolamento de dados
 * 2. SEMPRE chamar checkSession() no início de cada método
 * 3. Usar prepared statements (:param) para evitar SQL Injection
 * 4. Usar htmlspecialchars() nas views para evitar XSS
 * 5. Redirecionar após POST para evitar resubmissão do formulário
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */
