<?php
require 'vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require 'app/Core/Database.php'; 
$conn = App\Core\Database::connect(); 
$stmt = $conn->query('DESCRIBE orders');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
