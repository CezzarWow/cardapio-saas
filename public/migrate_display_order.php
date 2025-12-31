<?php
/**
 * Script de migração: Adiciona coluna display_order à tabela categories
 * Execute uma única vez acessando: http://localhost/cardapio-saas/public/migrate_display_order.php
 * Depois delete este arquivo.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $conn = Database::connect();
    
    // Verifica se a coluna já existe
    $stmt = $conn->prepare("SHOW COLUMNS FROM categories LIKE 'display_order'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        die("✅ Coluna 'display_order' já existe na tabela 'categories'. Você pode deletar este arquivo.");
    }
    
    // Adiciona a coluna
    $conn->exec("ALTER TABLE categories ADD COLUMN display_order INT DEFAULT 0");
    
    echo "✅ Coluna 'display_order' adicionada com sucesso!<br>";
    echo "🗑️ Você pode deletar este arquivo agora.<br>";
    echo "🔄 <a href='/cardapio-saas/public/admin/loja/cardapio'>Voltar para o Admin</a>";
    
} catch (Exception $e) {
    die("❌ Erro: " . $e->getMessage());
}
