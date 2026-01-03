<?php
require __DIR__ . '/../app/Core/Database.php';

try {
    $conn = App\Core\Database::connect();
    $conn->exec("ALTER TABLE orders ADD COLUMN observation TEXT NULL COMMENT 'Observações do pedido'");
    echo "✅ Coluna 'observation' adicionada à tabela orders!\n";
} catch(Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
