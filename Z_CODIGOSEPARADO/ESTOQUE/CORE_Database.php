<?php
// LOCALIZACAO ORIGINAL: app/Core/Database.php
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
