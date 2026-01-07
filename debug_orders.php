<?php
/**
 * Debug: Verifica is_paid dos pedidos recentes
 */

require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

echo "<pre>";
echo "=== Últimos 10 pedidos ===\n\n";

try {
    $conn = Database::connect();
    
    $stmt = $conn->query("SELECT id, order_type, is_paid, payment_method, status, created_at FROM orders ORDER BY id DESC LIMIT 10");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo str_pad("ID", 6) . str_pad("Tipo", 10) . str_pad("is_paid", 10) . str_pad("Pagamento", 15) . str_pad("Status", 12) . "Data\n";
    echo str_repeat("-", 75) . "\n";
    
    foreach ($orders as $order) {
        echo str_pad($order['id'], 6);
        echo str_pad($order['order_type'] ?? '--', 10);
        echo str_pad($order['is_paid'] ?? 'NULL', 10);
        echo str_pad($order['payment_method'] ?? '--', 15);
        echo str_pad($order['status'], 12);
        echo $order['created_at'] . "\n";
    }
    
    echo "\n=== Resumo ===\n";
    echo "Se is_paid = 1 → Deve aparecer como PAGO\n";
    echo "Se is_paid = 0 ou NULL → Mostra forma de pagamento esperada\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "</pre>";
