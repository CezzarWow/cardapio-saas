<?php

namespace App\Core;

use App\Core\Logger;
use App\Exceptions\DatabaseConnectionException;
use PDO;
use PDOException;

class Database
{
    // Padrão Singleton: Garante que só existe UMA conexão aberta por vez (economiza memória)
    private static $instance = null;

    /**
     * Conecta ao banco de dados (Singleton)
     * 
     * @return PDO Instância da conexão PDO
     * @throws DatabaseConnectionException Se a conexão falhar
     */
    public static function connect(): PDO
    {
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
                // Log do erro completo (apenas em logs, não exposto ao usuário)
                Logger::error('Database connection failed', [
                    'host' => $host,
                    'database' => $db,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);

                // Lança exceção customizada (permite tratamento adequado)
                throw new DatabaseConnectionException(
                    'Erro ao conectar ao banco de dados',
                    $e->getCode(),
                    $e
                );
            }
        }

        return self::$instance;
    }
}
