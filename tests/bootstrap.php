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
