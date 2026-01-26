<?php
// public/diag_tables.php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

use App\Core\Database;

$conn = Database::connect();
$stmt = $conn->query("SELECT id, name FROM restaurants");
$rests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Restaurantes encontrados:\n";
print_r($rests);

// Check ocupadas em TODOS os restaurantes
foreach ($rests as $r) {
    $rid = $r['id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as qtd FROM tables WHERE restaurant_id = ? AND status = 'ocupada'");
    $stmt->execute([$rid]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Restaurante ID {$rid} ({$r['name']}): {$res['qtd']} mesas ocupadas.\n";
    
    if ($res['qtd'] > 0) {
        $stmt = $conn->prepare("SELECT * FROM tables WHERE restaurant_id = ? AND status = 'ocupada'");
        $stmt->execute([$rid]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($tables);
        
        foreach ($tables as $t) {
            if ($t['current_order_id']) {
                $stmt2 = $conn->prepare("SELECT id, order_type, status FROM orders WHERE id = ?");
                $stmt2->execute([$t['current_order_id']]);
                $o = $stmt2->fetch(PDO::FETCH_ASSOC);
                echo "   -> ORDER INFO: "; print_r($o);
            }
        }
    }
}
