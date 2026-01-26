<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
use App\Core\Database;

try {
    $conn = Database::connect();
    $stmt = $conn->query("SELECT id, status, order_type FROM orders ORDER BY id DESC LIMIT 5");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
