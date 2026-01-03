<?php
require __DIR__ . '/../app/Core/Database.php';

$conn = App\Core\Database::connect();
$stmt = $conn->query('DESCRIBE orders');

echo "=== Estrutura da tabela ORDERS ===\n\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . ($row['Default'] ?? 'NULL') . "\n";
}

// Verifica se existe coluna 'source'
$checkSource = $conn->query("SELECT COUNT(*) as count FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'cardapio_saas' 
    AND TABLE_NAME = 'orders' 
    AND COLUMN_NAME = 'source'")->fetch(PDO::FETCH_ASSOC);

echo "\n\nColuna 'source' existe: " . ($checkSource['count'] > 0 ? 'SIM' : 'NÃO') . "\n";

if ($checkSource['count'] == 0) {
    echo "\nAdicione a coluna com:\n";
    echo "ALTER TABLE orders ADD COLUMN source VARCHAR(10) DEFAULT 'pdv' COMMENT 'Origem: web, pdv, app';\n";
}
