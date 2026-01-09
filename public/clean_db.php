<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

try {
    $conn = Database::connect();
    
    echo "Limpando pagamentos...\n";
    $conn->query("DELETE FROM order_payments");

    echo "Limpando itens...\n";
    $conn->query("DELETE FROM order_items");

    echo "Limpando pedidos...\n";
    $conn->query("DELETE FROM orders");

    echo "Liberando mesas...\n";
    $conn->query("UPDATE tables SET current_order_id = NULL, status = 'livre'");

    echo "Limpeza concluída com sucesso!\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
