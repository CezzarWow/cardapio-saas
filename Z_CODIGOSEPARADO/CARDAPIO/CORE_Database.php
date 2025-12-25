/**
 * ═══════════════════════════════════════════════════════════════════════════
 * LOCALIZAÇÃO ORIGINAL: app/Core/Database.php
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * DESCRIÇÃO: Classe de conexão com o banco de dados MySQL
 * PADRÃO: Singleton (uma única conexão por requisição)
 * 
 * COMO USAR:
 * 
 * use App\Core\Database;
 * 
 * $conn = Database::connect();
 * 
 * // SELECT
 * $stmt = $conn->prepare("SELECT * FROM products WHERE restaurant_id = :rid");
 * $stmt->execute(['rid' => $_SESSION['loja_ativa_id']]);
 * $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
 * 
 * // INSERT
 * $stmt = $conn->prepare("INSERT INTO tabela (col1, col2) VALUES (:v1, :v2)");
 * $stmt->execute(['v1' => $valor1, 'v2' => $valor2]);
 * $novoId = $conn->lastInsertId();
 * 
 * // UPDATE
 * $stmt = $conn->prepare("UPDATE tabela SET col1 = :v1 WHERE id = :id");
 * $stmt->execute(['v1' => $valor, 'id' => $id]);
 * 
 * // DELETE
 * $stmt = $conn->prepare("DELETE FROM tabela WHERE id = :id");
 * $stmt->execute(['id' => $id]);
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    // Padrão Singleton: Garante que só existe UMA conexão aberta por vez (economiza memória)
    private static $instance = null;

    public static function connect() {
        // Configurações do XAMPP
        $host = 'localhost';
        $db   = 'cardapio_saas';
        $user = 'root';
        $pass = ''; // No XAMPP a senha padrão é vazia mesmo

        if (self::$instance === null) {
            try {
                // Tenta conectar
                self::$instance = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
                
                // Configura para o PHP avisar se der erro no SQL
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                // Se der erro, para tudo e mostra a mensagem
                die("Erro fatal de conexão: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
