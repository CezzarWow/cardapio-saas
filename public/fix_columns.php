<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

try {
    $conn = Database::connect();
    echo "Connected to database.\n";

    // Check and Add address_number
    $check = $conn->query("SHOW COLUMNS FROM restaurants LIKE 'address_number'");
    if($check->rowCount() == 0) {
        $conn->exec("ALTER TABLE restaurants ADD COLUMN address_number VARCHAR(20) NULL AFTER address");
        echo "Added column 'address_number'.\n";
    } else {
        echo "Column 'address_number' already exists.\n";
    }
    
    // Check and Add zip_code
    $check2 = $conn->query("SHOW COLUMNS FROM restaurants LIKE 'zip_code'");
    if($check2->rowCount() == 0) {
        $conn->exec("ALTER TABLE restaurants ADD COLUMN zip_code VARCHAR(20) NULL AFTER address_number");
        echo "Added column 'zip_code'.\n";
    } else {
        echo "Column 'zip_code' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
