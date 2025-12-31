<?php
require_once 'app/Core/Database.php';
use App\Core\Database;
try {
    $conn = Database::connect();
    $stmt = $conn->query("DESCRIBE combo_items");
    echo "COLUMNS:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    $stmtData = $conn->query("SELECT * FROM combo_items LIMIT 5");
    echo "\nDATA:\n";
    print_r($stmtData->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
