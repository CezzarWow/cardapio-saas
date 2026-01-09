<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
use App\Core\Database;

try {
    $conn = Database::connect();
    
    // Create additional_categories
    $conn->exec("CREATE TABLE IF NOT EXISTS additional_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        restaurant_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(restaurant_id)
    )");
    echo "Table additional_categories created.\n";

    // Create additional_group_categories (pivot)
    $conn->exec("CREATE TABLE IF NOT EXISTS additional_group_categories (
        group_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (group_id, category_id)
    )");
    echo "Table additional_group_categories created.\n";

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
