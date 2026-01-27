<?php

namespace App\Core;

use App\Core\Logger;
use App\Exceptions\DatabaseConnectionException;
use PDO;
use PDOException;

class Database
{
    // Singleton: keep a single connection open.
    private static $instance = null;

    /**
     * Connects to the database (singleton).
     */
    public static function connect(): PDO
    {
        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $db = $_ENV['DB_NAME'] ?? 'cardapio_saas';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        if (self::$instance === null) {
            try {
                if ($driver === 'sqlite') {
                    self::$instance = new PDO("sqlite:$db");
                } else {
                    self::$instance = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
                }

                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                if ($driver === 'sqlite' && method_exists(self::$instance, 'sqliteCreateFunction')) {
                    self::$instance->sqliteCreateFunction('NOW', function (): string {
                        return date('Y-m-d H:i:s');
                    });
                    self::$instance->sqliteCreateFunction('GREATEST', function ($a, $b) {
                        return max($a, $b);
                    });
                }
            } catch (PDOException $e) {
                Logger::error('Database connection failed', [
                    'host' => $host,
                    'database' => $db,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);

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
