<?php
require __DIR__ . '/vendor/autoload.php';

use App\Services\CardapioPublico\CardapioPublicoQueryService;
use App\Core\Database;

// Primeiro, descobrir IDs válidos
$conn = Database::connect();
$stmt = $conn->query("SELECT id, slug, name FROM restaurants LIMIT 5");
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Restaurantes disponíveis:\n";
foreach ($restaurants as $r) {
    echo "  ID {$r['id']}: {$r['slug']} ({$r['name']})\n";
}

if (empty($restaurants)) {
    echo "ERRO: Nenhum restaurante encontrado\n";
    exit(1);
}

$testId = $restaurants[0]['id'];
echo "\nTestando com restaurante ID: $testId\n";

$service = new CardapioPublicoQueryService();
$data = $service->getCardapioData($testId);

if (!$data) {
    echo "ERRO: getCardapioData retornou null\n";
    exit(1);
}

echo "Keys retornadas: " . implode(', ', array_keys($data)) . "\n";
echo "Categories count: " . count($data['categories'] ?? []) . "\n";
echo "Products count: " . count($data['allProducts'] ?? []) . "\n";
echo "Combos count: " . count($data['combos'] ?? []) . "\n";

echo "\nSUCESSO!\n";
