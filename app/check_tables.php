<?php
require __DIR__ . '/../app/Core/Database.php';

$conn = App\Core\Database::connect();

echo "=== Estrutura da tabela CLIENTS ===\n\n";
$stmt = $conn->query('DESCRIBE clients');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n\n=== Estrutura da tabela ORDERS ===\n\n";
$stmt2 = $conn->query('DESCRIBE orders');
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
