<?php
/**
 * Test Script for getProductExtras API
 * Run this from browser or CLI to check what the controller returns.
 */
require __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['loja_ativa_id'] = 8; // Adjust to your restaurant ID

$_GET['product_id'] = 1; // Use a valid product ID

define('BASE_URL', '/cardapio-saas/public');

try {
    $container = require __DIR__ . '/app/Config/dependencies.php';
    
    echo "Container loaded successfully.\n";
    
    $controller = $container->get(\App\Controllers\Admin\AdditionalController::class);
    
    echo "Controller resolved successfully.\n";
    
    // Now call getProductExtras (will output JSON and exit)
    $controller->getProductExtras();
    
} catch (\Throwable $e) {
    echo "FATAL ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}
