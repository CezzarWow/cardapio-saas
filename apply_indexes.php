<?php
// apply_indexes.php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "Starting Index Optimization...\n";

try {
    $pdo = Database::connect();
    
    // Read SQL
    $sqlFile = __DIR__ . '/database/performance_indexes.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at $sqlFile");
    }
    
    $statements = array_filter(
        array_map('trim', explode(';', file_get_contents($sqlFile))),
        fn($s) => !empty($s)
    );

    foreach ($statements as $stmt) {
        if (strpos($stmt, '--') === 0) continue; // Skip comments if any remain

        try {
            // Attempt to create index
            // Note: MySQL doesn't support "IF NOT EXISTS" for indexes in older versions efficiently 
            // without a procedure, so we catch "Duplicate key name" error (Code 42000 or specific MySQL error code)
            $pdo->exec($stmt);
            echo "[OK] Executed: " . substr($stmt, 0, 50) . "...\n";
        } catch (\PDOException $e) {
            // Error 1061: Duplicate key name
            if (strpos($e->getMessage(), 'Duplicate key name') !== false || $e->getCode() == '42000') {
                echo "[SKIP] Index already exists: " . substr($stmt, 0, 50) . "...\n";
            } else {
                echo "[ERROR] Failed: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "Optimization Complete.\n";

} catch (\Exception $e) {
    die("Fatal Error: " . $e->getMessage());
}
