<?php
/**
 * Script para remover constraint de estoque não-negativo
 * Execute uma vez e depois delete este arquivo
 */

require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

echo "<pre>";
echo "=== Removendo Constraint de Estoque ===\n\n";

try {
    $conn = Database::connect();
    
    // Tenta diferentes sintaxes (MariaDB vs MySQL)
    $queries = [
        "ALTER TABLE products DROP CONSTRAINT chk_products_stock",
        "ALTER TABLE products DROP CONSTRAINT IF EXISTS chk_products_stock",
        "ALTER TABLE products DROP CHECK chk_products_stock",
    ];
    
    $success = false;
    
    foreach ($queries as $sql) {
        try {
            echo "Tentando: $sql\n";
            $conn->exec($sql);
            echo "✅ Sucesso!\n\n";
            $success = true;
            break;
        } catch (Exception $e) {
            echo "❌ " . $e->getMessage() . "\n\n";
        }
    }
    
    if ($success) {
        echo "=== Constraint removida! Estoque pode ficar negativo agora. ===\n";
        echo "Você pode deletar este arquivo.\n";
    } else {
        echo "=== Nenhum método funcionou ===\n";
        echo "Execute manualmente no phpMyAdmin:\n";
        echo "ALTER TABLE products DROP CONSTRAINT chk_products_stock;\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
}

echo "</pre>";
