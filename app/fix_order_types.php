<?php
require __DIR__ . '/../app/Core/Database.php';

$conn = App\Core\Database::connect();

// Atualiza pedidos com order_type vazio para 'delivery'
$stmt = $conn->exec("UPDATE orders SET order_type = 'delivery' WHERE order_type = '' OR order_type IS NULL");

echo "✅ Atualizados $stmt pedidos com order_type vazio para 'delivery'\n";
