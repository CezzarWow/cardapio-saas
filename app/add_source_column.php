<?php
require __DIR__ . '/../app/Core/Database.php';

try {
    $conn = App\Core\Database::connect();
    $conn->exec("ALTER TABLE orders ADD COLUMN source VARCHAR(10) DEFAULT 'pdv' COMMENT 'Origem: web, pdv, app'");
    echo "✅ Coluna 'source' adicionada com sucesso!\n";
} catch(Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
