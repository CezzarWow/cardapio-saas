<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class MenuController {

    public function index($slug) {
        $conn = Database::connect();

        // 1. Busca o restaurante pelo Link (slug)
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Se não achar a loja, erro 404
        if (!$restaurant) {
            echo "<h1>404 - Restaurante não encontrado 😢</h1>";
            return;
        }

        // --- AQUI ESTÁ A MÁGICA DO SUSPENDER (NOVO) ---
        // Se is_active for 0, a gente mata o processo aqui e mostra aviso.
        if ($restaurant['is_active'] == 0) {
            echo "<div style='display:flex; height:100vh; justify-content:center; align-items:center; background:#f8d7da; color:#721c24; font-family:sans-serif; flex-direction:column; text-align:center;'>";
            echo "<h1 style='font-size:3em;'>🔒</h1>";
            echo "<h1>Loja Temporariamente Indisponível</h1>";
            echo "<p>O cardápio desta loja está suspenso no momento.</p>";
            echo "</div>";
            return; // O código PARA aqui. Não carrega o cardápio.
        }
        // -----------------------------------------------

        // 3. Se estiver tudo OK (Ativo), carrega o cardápio
        require __DIR__ . '/../../views/cardapio_publico.php';
    }
}