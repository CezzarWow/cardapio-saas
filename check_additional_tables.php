<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Core\Database;

try {
    $conn = Database::connect();
    echo "Database Connected.\n";

    $tables = [
        'additional_groups',
        'additional_items',
        'additional_group_items',
        'product_additional_relations',
        'additional_categories'
    ];

    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT 1 FROM $table LIMIT 1");
            echo "[OK] Table '$table' exists.\n";
        } catch (\PDOException $e) {
            echo "[MISSING] Table '$table' DOES NOT exist. Error: " . $e->getMessage() . "\n";
        }
    }

} catch (\Throwable $e) {
    echo "Fatal Error: " . $e->getMessage();
}
