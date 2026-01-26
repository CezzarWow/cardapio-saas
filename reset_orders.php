<?php
// reset_orders.php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Simples
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Core\Database;

try {
    $conn = Database::connect();
    
    // Iniciar Transação
    $conn->beginTransaction();

    echo "Cleaning order_items...\n";
    $conn->exec("DELETE FROM order_items");

    echo "Cleaning order_payments...\n";
    $conn->exec("DELETE FROM order_payments");

    echo "Cleaning orders...\n";
    $conn->exec("DELETE FROM orders");

    echo "Resetting tables...\n";
    $conn->exec("UPDATE tables SET status = 'livre', current_order_id = NULL");
    
    $conn->commit();
    echo "SUCCESS: All orders cleared and tables reset.\n";

} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
