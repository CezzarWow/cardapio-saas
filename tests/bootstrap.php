<?php
/**
 * PHPUnit Bootstrap
 * 
 * Loads autoloader and environment for tests.
 */

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Force a test database configuration.
$drivers = \PDO::getAvailableDrivers();

if (in_array('sqlite', $drivers, true)) {
    if (!isset($_ENV['DB_DRIVER'])) {
        $_ENV['DB_DRIVER'] = 'sqlite';
    }

    if ($_ENV['DB_DRIVER'] === 'sqlite') {
        $dbPath = dirname(__DIR__) . '/storage/test.sqlite';
        if (!is_dir(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0777, true);
        }
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        $_ENV['DB_NAME'] = $dbPath;
    }
} else {
    $_ENV['DB_DRIVER'] = 'mysql';
    $baseName = $_ENV['DB_NAME'] ?? 'cardapio_saas';
    $testDb = $_ENV['DB_TEST_NAME'] ?? ($baseName . '_test');
    $_ENV['DB_NAME'] = $testDb;

    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $pdo = new \PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$testDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
}

require __DIR__ . '/Support/TestDatabase.php';
\Tests\Support\TestDatabase::setup();

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default session values for testing
$_SESSION['user_id'] = 1;
$_SESSION['loja_ativa_id'] = 8;

// Define BASE_URL for tests
if (!defined('BASE_URL')) {
    define('BASE_URL', '/cardapio-saas/public');
}
