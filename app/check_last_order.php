<?php
require __DIR__ . '/../app/Core/Database.php';

$conn = App\Core\Database::connect();

// Busca último pedido
$stmt = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
$order = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== ÚLTIMO PEDIDO CRIADO ===\n\n";
if ($order) {
    foreach ($order as $key => $value) {
        echo "$key: $value\n";
    }
} else {
    echo "Nenhum pedido encontrado!\n";
}
