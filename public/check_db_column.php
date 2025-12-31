<?php
require __DIR__ . '/../app/Config/Database.php';

try {
    $db = new \App\Config\Database();
    $conn = $db->getConnection();
    
    // Verifica tabela products
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'display_order'");
    $column = $stmt->fetch();
    
    echo "<h1>Verificação de Banco de Dados</h1>";
    if ($column) {
        echo "<p style='color:green'>✅ Coluna <strong>display_order</strong> existe na tabela products.</p>";
    } else {
        echo "<p style='color:red'>❌ Coluna <strong>display_order</strong> NÃO existe na tabela products.</p>";
        
        // Tenta criar
        try {
            $conn->exec("ALTER TABLE products ADD COLUMN display_order INT DEFAULT 0");
            echo "<p style='color:blue'>Attempted to create column. Check again.</p>";
        } catch (Exception $e) {
            echo "<p>Error creating column: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
