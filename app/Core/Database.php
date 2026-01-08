<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    // Padrão Singleton: Garante que só existe UMA conexão aberta por vez (economiza memória)
    private static $instance = null;

    public static function connect() {
        // Configurações via Environment (.env)
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $db   = $_ENV['DB_NAME'] ?? 'cardapio_saas';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        if (self::$instance === null) {
            try {
                // Tenta conectar (utf8mb4 para suporte a emoji)
                self::$instance = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
                
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