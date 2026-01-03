<?php
/**
 * Script de Migração de Imagens para WebP
 * Converte todas as imagens existentes (PNG, JPG, JPEG) para WebP
 * e atualiza as referências no banco de dados
 * 
 * EXECUTE UMA VEZ: php migrate_images_webp.php
 */

// Configurações
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('QUALITY', 85);

// Carrega o helper
require_once __DIR__ . '/Helpers/ImageConverter.php';

// Conexão direta com o banco
$host = 'localhost';
$dbname = 'cardapio_saas';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage() . "\n");
}

echo "=== Migração de Imagens para WebP ===\n\n";

// 1. Migrar imagens de produtos
echo "[1/2] Processando imagens de produtos...\n";
$stmt = $conn->query("SELECT id, image FROM products WHERE image IS NOT NULL AND image != ''");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$converted = 0;
$skipped = 0;
$errors = 0;

foreach ($products as $product) {
    $imageName = $product['image'];
    $imagePath = UPLOAD_DIR . $imageName;
    
    // Pula se já for WebP
    if (strtolower(pathinfo($imageName, PATHINFO_EXTENSION)) === 'webp') {
        $skipped++;
        echo "  [SKIP] {$imageName} (já é WebP)\n";
        continue;
    }
    
    // Pula se arquivo não existir
    if (!file_exists($imagePath)) {
        $errors++;
        echo "  [ERRO] {$imageName} (arquivo não encontrado)\n";
        continue;
    }
    
    // Converte
    $webpName = ImageConverter::toWebp($imagePath, QUALITY);
    
    if ($webpName) {
        // Atualiza banco
        $stmtUpdate = $conn->prepare("UPDATE products SET image = :img WHERE id = :id");
        $stmtUpdate->execute(['img' => $webpName, 'id' => $product['id']]);
        
        // Remove arquivo original
        @unlink($imagePath);
        
        $converted++;
        echo "  [OK] {$imageName} -> {$webpName}\n";
    } else {
        $errors++;
        echo "  [ERRO] {$imageName} (falha na conversão)\n";
    }
}

echo "\n  Produtos: {$converted} convertidos, {$skipped} pulados, {$errors} erros\n\n";

// 2. Migrar logos de restaurantes
echo "[2/2] Processando logos de restaurantes...\n";
$stmt = $conn->query("SELECT id, logo FROM restaurants WHERE logo IS NOT NULL AND logo != ''");
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$convertedLogos = 0;
$skippedLogos = 0;
$errorsLogos = 0;

foreach ($restaurants as $restaurant) {
    $logoName = $restaurant['logo'];
    $logoPath = UPLOAD_DIR . $logoName;
    
    // Pula se já for WebP ou AVIF
    $ext = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));
    if ($ext === 'webp' || $ext === 'avif') {
        $skippedLogos++;
        echo "  [SKIP] {$logoName} (já é {$ext})\n";
        continue;
    }
    
    // Pula se arquivo não existir
    if (!file_exists($logoPath)) {
        $errorsLogos++;
        echo "  [ERRO] {$logoName} (arquivo não encontrado)\n";
        continue;
    }
    
    // Converte
    $webpName = ImageConverter::toWebp($logoPath, QUALITY);
    
    if ($webpName) {
        // Atualiza banco
        $stmtUpdate = $conn->prepare("UPDATE restaurants SET logo = :logo WHERE id = :id");
        $stmtUpdate->execute(['logo' => $webpName, 'id' => $restaurant['id']]);
        
        // Remove arquivo original
        @unlink($logoPath);
        
        $convertedLogos++;
        echo "  [OK] {$logoName} -> {$webpName}\n";
    } else {
        $errorsLogos++;
        echo "  [ERRO] {$logoName} (falha na conversão)\n";
    }
}

echo "\n  Logos: {$convertedLogos} convertidos, {$skippedLogos} pulados, {$errorsLogos} erros\n\n";

// Resumo final
$totalConverted = $converted + $convertedLogos;
$totalSkipped = $skipped + $skippedLogos;
$totalErrors = $errors + $errorsLogos;

echo "=== Migração Concluída ===\n";
echo "Total: {$totalConverted} convertidos, {$totalSkipped} pulados, {$totalErrors} erros\n";

// Calcula economia de espaço (aproximada)
echo "\nDica: A economia média de espaço é de 40-60% em relação aos arquivos originais.\n";
