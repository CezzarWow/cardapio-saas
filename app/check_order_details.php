<?php
require __DIR__ . '/../app/Core/Database.php';

$conn = App\Core\Database::connect();

// Busca último pedido com dados do cliente
$stmt = $conn->query("
    SELECT o.*, c.name as client_name, c.phone, c.address, c.neighborhood 
    FROM orders o
    LEFT JOIN clients c ON o.client_id = c.id
    ORDER BY o.id DESC 
    LIMIT 1
");
$order = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== ÚLTIMO PEDIDO COM CLIENTE ===\n\n";
if ($order) {
    echo "ID: {$order['id']}\n";
    echo "Cliente: {$order['client_name']}\n";
    echo "Telefone: {$order['phone']}\n";
    echo "Endereço: {$order['address']}\n";
    echo "Bairro: {$order['neighborhood']}\n";
    echo "Pagamento: {$order['payment_method']}\n";
    echo "Total: R$ {$order['total']}\n";
    echo "Status: {$order['status']}\n";
    
    // Busca itens
    $stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = :oid");
    $stmtItems->execute(['oid' => $order['id']]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nITENS:\n";
    foreach ($items as $item) {
        echo "- {$item['quantity']}x {$item['name']} - R$ {$item['price']}\n";
    }
} else {
    echo "Nenhum pedido encontrado!\n";
}
