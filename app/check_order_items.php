<?php
require __DIR__ . '/../app/Core/Database.php';

$conn = App\Core\Database::connect();
$stmt = $conn->query('DESCRIBE order_items');

echo "=== Estrutura da tabela ORDER_ITEMS ===\n\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
