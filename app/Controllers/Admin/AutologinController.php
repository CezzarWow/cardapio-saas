<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class AutologinController {

    public function login() {
        // 1. Inicia a sessão (caso não esteja iniciada)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Pega o ID da URL (?id=1)
        $id = $_GET['id'] ?? null;

        if (!$id) {
            die('ID da loja não informado.');
        }

        // 3. Verifica no banco se a loja existe
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $loja = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($loja) {
            // 4. A MÁGICA: Salva na sessão que estamos gerenciando ESSA loja
            $_SESSION['loja_ativa_id'] = $loja['id'];
            $_SESSION['loja_ativa_nome'] = $loja['name'];

            // 5. Redireciona para o Painel da Loja (onde cadastraremos produtos)
            // Obs: Vamos criar essa rota '/admin/loja/painel' no próximo passo
            header('Location: ../loja/painel');
            exit;
        } else {
            die('Loja não encontrada.');
        }
    }
}
