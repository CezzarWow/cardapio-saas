<?php
require 'app/Core/Database.php'; 
$conn = App\Core\Database::connect(); 
$conn->exec("ALTER TABLE orders ADD COLUMN is_paid TINYINT(1) DEFAULT 0 AFTER status");
echo "Column added.";
