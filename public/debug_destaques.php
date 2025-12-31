<?php
/**
 * Script de Debug - Verificar estrutura do banco e dados POST
 * Acesse: http://localhost/cardapio-saas/debug_destaques.php
 */

require __DIR__ . '/../app/Config/Database.php';

echo "<h1>Debug Destaques</h1>";

try {
    $db = new \App\Config\Database();
    $conn = $db->getConnection();
    
    // 1. Verifica coluna display_order em products
    echo "<h2>1. Verificação de Coluna</h2>";
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'display_order'");
    $column = $stmt->fetch();
    
    if ($column) {
        echo "<p style='color:green'>✅ Coluna <strong>display_order</strong> EXISTE na tabela products.</p>";
        echo "<pre>" . print_r($column, true) . "</pre>";
    } else {
        echo "<p style='color:red'>❌ Coluna <strong>display_order</strong> NÃO EXISTE na tabela products.</p>";
        echo "<p>Criando coluna...</p>";
        try {
            $conn->exec("ALTER TABLE products ADD COLUMN display_order INT DEFAULT 0");
            echo "<p style='color:green'>✅ Coluna criada com sucesso!</p>";
        } catch (Exception $e) {
            echo "<p style='color:red'>Erro ao criar: " . $e->getMessage() . "</p>";
        }
    }
    
    // 2. Verifica coluna is_featured em products
    echo "<h2>2. Verificação is_featured</h2>";
    $stmt2 = $conn->query("SHOW COLUMNS FROM products LIKE 'is_featured'");
    $column2 = $stmt2->fetch();
    
    if ($column2) {
        echo "<p style='color:green'>✅ Coluna <strong>is_featured</strong> EXISTE.</p>";
    } else {
        echo "<p style='color:red'>❌ Coluna <strong>is_featured</strong> NÃO EXISTE.</p>";
        try {
            $conn->exec("ALTER TABLE products ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
            echo "<p style='color:green'>✅ Coluna criada!</p>";
        } catch (Exception $e) {
            echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
        }
    }
    
    // 3. Verifica coluna is_active em categories
    echo "<h2>3. Verificação is_active (categories)</h2>";
    $stmt3 = $conn->query("SHOW COLUMNS FROM categories LIKE 'is_active'");
    $column3 = $stmt3->fetch();
    
    if ($column3) {
        echo "<p style='color:green'>✅ Coluna <strong>is_active</strong> EXISTE em categories.</p>";
    } else {
        echo "<p style='color:red'>❌ Coluna <strong>is_active</strong> NÃO EXISTE.</p>";
        try {
            $conn->exec("ALTER TABLE categories ADD COLUMN is_active TINYINT(1) DEFAULT 1");
            echo "<p style='color:green'>✅ Coluna criada!</p>";
        } catch (Exception $e) {
            echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
        }
    }
    
    // 4. Mostra alguns produtos com seus valores atuais
    echo "<h2>4. Produtos (amostra)</h2>";
    $stmt4 = $conn->query("SELECT id, name, display_order, is_featured FROM products LIMIT 10");
    $products = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nome</th><th>display_order</th><th>is_featured</th></tr>";
    foreach ($products as $p) {
        echo "<tr><td>{$p['id']}</td><td>{$p['name']}</td><td>{$p['display_order']}</td><td>{$p['is_featured']}</td></tr>";
    }
    echo "</table>";
    
    echo "<h2>✅ Verificação concluída!</h2>";
    echo "<p>Se todas as colunas existem, o problema pode estar no JavaScript ou no envio do formulário.</p>";
    echo "<p>Verifique o console do navegador (F12) para erros de JS.</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Erro geral: " . $e->getMessage() . "</p>";
}
