<?php
/**
 * Script para criar coluna display_order se não existir
 */

require __DIR__ . '/../app/Config/Database.php';

echo "<h1>Verificação e Criação de Coluna display_order</h1>";

try {
    $db = new \App\Config\Database();
    $conn = $db->getConnection();
    
    // Verifica se a coluna existe
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'display_order'");
    $column = $stmt->fetch();
    
    if ($column) {
        echo "<p style='color:green'>✅ Coluna <strong>display_order</strong> já existe!</p>";
        
        // Mostra alguns valores atuais
        $stmt2 = $conn->query("SELECT id, name, display_order FROM products ORDER BY display_order LIMIT 10");
        $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Valores atuais:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nome</th><th>display_order</th></tr>";
        foreach ($products as $p) {
            echo "<tr><td>{$p['id']}</td><td>{$p['name']}</td><td>{$p['display_order']}</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color:orange'>⚠️ Coluna <strong>display_order</strong> NÃO existe. Criando...</p>";
        
        $conn->exec("ALTER TABLE products ADD COLUMN display_order INT DEFAULT 0");
        
        echo "<p style='color:green'>✅ Coluna criada com sucesso!</p>";
        echo "<p>Recarregue a página de configurações e tente novamente.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}
