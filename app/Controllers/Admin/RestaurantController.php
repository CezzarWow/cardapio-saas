<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class RestaurantController {

    public function create() {
        // Agora que você arrumou a pasta, essa linha vai funcionar
        require __DIR__ . '/../../../views/admin/restaurants/create.php';
    }

    public function store() {
        $name = $_POST['name'];
        $slug = $_POST['slug'];
        
        $conn = Database::connect();
        
        // Verifica/Cria usuário dono (Gambiarra temporária pro teste)
        $check = $conn->query("SELECT id FROM users WHERE id = 1");
        if ($check->rowCount() == 0) {
            $conn->query("INSERT INTO users (id, email, password) VALUES (1, 'admin@teste.com', '123')");
        }

        try {
            $sql = "INSERT INTO restaurants (user_id, name, slug) VALUES (1, :name, :slug)";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['name' => $name, 'slug' => $slug]);

            echo "<h1>✅ Sucesso!</h1><p>Restaurante <strong>$name</strong> criado.</p>";
            echo "<a href='../../admin'>Voltar ao Painel</a>";

        } catch (\PDOException $e) {
            echo "<h1>❌ Erro!</h1><p>" . $e->getMessage() . "</p>";
        }
    }
    // --- 3. EDITAR (Busca dados e abre Formulário Preenchido) ---
    // [NOVA FUNÇÃO QUE ADICIONAMOS AGORA]
    public function edit() {
        // Pega o ID que veio na URL (?id=1)
        $id = $_GET['id'] ?? null;

        if (!$id) {
            die("ID do restaurante não informado!");
        }

        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            die("Restaurante não encontrado!");
        }

        // Carrega a view de edição
        require __DIR__ . '/../../../views/admin/restaurants/edit.php';
    }
    
    // --- 4. ATUALIZAR (Recebe o POST e atualiza no Banco) ---
    public function update() {
        // Recebe os dados do formulário
        $id = $_POST['id'];
        $name = $_POST['name'];
        $slug = $_POST['slug'];

        $conn = Database::connect();

        try {
            // SQL de Atualização
            $sql = "UPDATE restaurants SET name = :name, slug = :slug WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'slug' => $slug,
                'id' => $id
            ]);

            // Redireciona de volta para o painel com sucesso
            // O header manda o navegador mudar de página
            // Sai de 'atualizar', sai de 'restaurantes', entra em 'admin'

            header('Location: ../../admin');
            exit;

        } catch (\PDOException $e) {
            echo "<h1>❌ Erro ao atualizar!</h1><p>" . $e->getMessage() . "</p>";
        }
    }
    // --- 5. DELETAR (Apaga do Banco) ---
    public function delete() {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $conn = Database::connect();
            // Prepara a guilhotina
            $stmt = $conn->prepare("DELETE FROM restaurants WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }

        // Volta para a lista (como se nada tivesse acontecido)
        header('Location: ../../admin');
        exit;
    }
    // --- 6. ALTERAR STATUS (Suspender/Ativar) ---
    public function toggleStatus() {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $conn = Database::connect();
            
            // Esse comando SQL mágico inverte o valor:
            // Se for 1 vira 0. Se for 0 vira 1. (NOT is_active)
            $sql = "UPDATE restaurants SET is_active = NOT is_active WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $id]);
        }

        // Volta para o painel
        header('Location: ../../admin');
        exit;
    }
}