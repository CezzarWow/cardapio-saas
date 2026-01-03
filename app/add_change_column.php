<?php
require __DIR__ . '/../app/Core/Database.php';

try {
    $conn = App\Core\Database::connect();
    $conn->exec("ALTER TABLE orders ADD COLUMN change_for DECIMAL(10,2) DEFAULT NULL COMMENT 'Troco para quanto'");
    echo "✅ Coluna 'change_for' adicionada com sucesso!\n";
} catch(Exception $e) {
    echo "ℹ️ Erro (provavelmente já existe): " . $e->getMessage() . "\n";
}

// Verifica último pedido para ver se tem observation
$stmt = $conn->query("SELECT id, observation, source FROM orders ORDER BY id DESC LIMIT 1");
$order = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nÚltimo Pedido (#{$order['id']}):\n";
echo "Observação no banco: " . ($order['observation'] ? "'{$order['observation']}'" : "VAZIO/NULL") . "\n";
echo "Origem: {$order['source']}\n";
