<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class ConfigController {

    public function index() {
        $this->checkSession();
        $conn = Database::connect();

        // Busca os dados da loja atual
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['loja_ativa_id']]);
        $loja = $stmt->fetch(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../../views/admin/config/index.php';
    }

    public function update() {
        $this->checkSession();
        $id = $_SESSION['loja_ativa_id'];
        
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $address_number = $_POST['address_number'];
        $zip_code = $_POST['zip_code'];
        $primary_color = $_POST['primary_color'];

        $conn = Database::connect();

        // --- Upload da Logo ---
        $logoSql = ""; 
        $params = [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'address_number' => $address_number,
            'zip_code' => $zip_code,
            'color' => $primary_color,
            'id' => $id
        ];

        if (!empty($_FILES['logo']['name'])) {
            // Verifica Erros
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                // Erros comuns: 1 = File too large (php.ini), 2 = Too large (form), 3 = Partial, 4 = No file
                $errorMsg = 'Erro no upload: código ' . $_FILES['logo']['error'];
                echo "<script>alert('$errorMsg'); window.location.href='" . BASE_URL . "/admin/loja/config';</script>";
                exit;
            }

            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logoName = 'logo_' . $id . '_' . time() . '.' . $ext;
            $destination = __DIR__ . '/../../../public/uploads/' . $logoName;
            
            // Tenta mover
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $destination)) {
                $logoSql = ", logo = :logo";
                $params['logo'] = $logoName;
                $_SESSION['loja_ativa_logo'] = $logoName; // Atualiza Sessão
            } else {
                echo "<script>alert('Falha ao salvar arquivo no servidor. Verifique permissões.'); window.location.href='" . BASE_URL . "/admin/loja/config';</script>";
                exit;
            }
        }

        // Atualiza no Banco
        $sql = "UPDATE restaurants SET 
                name = :name, 
                phone = :phone, 
                address = :address, 
                address_number = :address_number,
                zip_code = :zip_code,
                primary_color = :color
                $logoSql 
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Atualiza a sessão para o nome mudar na hora lá em cima
        $_SESSION['loja_ativa_nome'] = $name;

        // Redireciona usando BASE_URL (Assumindo que BASE_URL está definido no index.php, mas aqui é PHP puro antes de output)
        // Como o header Location é relativo ao script ou absoluto, vamos usar ../config que é seguro se a estrutura de rotas se mantiver,
        // mas para garantir, vamos injetar o JS de alerta que o usuário pediu, que faz o redirect.
        
        echo "<script>alert('Configurações salvas! ⚙️✅'); window.location.href='" . BASE_URL . "/admin/loja/config';</script>";
    }

    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
