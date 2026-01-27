<?php
// scripts/migrate.php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Logger;

// 1. Load Environment Variables
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Ensure CLI output is visible
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting Migration Runner...\n";

try {
    // 2. Connect to Database
    $conn = Database::connect();
    echo "Connected to database.\n";

    // 3. Create History Table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS migrations_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 4. Scan for .sql files
    $migrationDir = __DIR__ . '/../database/migrations';
    $files = scandir($migrationDir);
    $pending = [];

    // Filter .sql files
    foreach ($files as $file) {
        if (str_ends_with($file, '.sql')) {
            $pending[] = $file;
        }
    }

    // Sort alphabetically (001_..., 002_...)
    sort($pending);

    // 5. Check which ones utilize
    $executedStmt = $conn->query("SELECT migration FROM migrations_history");
    $executed = $executedStmt->fetchAll(PDO::FETCH_COLUMN);

    $toRun = array_diff($pending, $executed);

    if (empty($toRun)) {
        echo "No new migrations to run.\n";
        exit(0);
    }

    echo "Found " . count($toRun) . " new migrations.\n";

    // 6. Execute pending migrations
    foreach ($toRun as $file) {
        echo "Migrating: $file ... ";
        
        $filePath = $migrationDir . '/' . $file;
        $sql = file_get_contents($filePath);

        if (trim($sql) === '') {
            echo "SKIPPED (Empty file)\n";
            // Record empty files too so we don't check them again? 
            // Better to record them to maintain history consistency.
            $stmt = $conn->prepare("INSERT INTO migrations_history (migration) VALUES (:name)");
            $stmt->execute(['name' => $file]);
            continue;
        }

        try {
            $conn->beginTransaction();
            
            // Execute SQL commands (support multiple statements if driver permits, otherwise might need splitting)
            // PDO MySQL usually handles multiple statements if ATTR_EMULATE_PREPARES is true (default is often true or false depending on config).
            // Safer: split by semicolon? Or just exec. simple .sql usually works with exec();
            // Let's rely on Database singleton config.
            
            $conn->exec($sql);
            
            // Record execution
            $stmt = $conn->prepare("INSERT INTO migrations_history (migration) VALUES (:name)");
            $stmt->execute(['name' => $file]);

            $conn->commit();
            echo "DONE\n";

        } catch (Exception $e) {
            $conn->rollBack();
            echo "FAILED\n";
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    echo "All migrations finished successfully.\n";

} catch (Exception $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
    exit(1);
}
