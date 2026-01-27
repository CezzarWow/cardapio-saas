<?php

namespace Tests\Unit;

use App\Core\Database;
use App\Repositories\Order\OrderPaymentRepository;
use App\Services\PaymentService;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

class PaymentServiceTest extends TestCase
{
    private PDO $conn;
    private PaymentService $service;

    protected function setUp(): void
    {
        TestDatabase::truncateAll();
        $this->conn = Database::connect();
        $repo = new OrderPaymentRepository();
        $this->service = new PaymentService($repo);
    }

    public function testRegisterPaymentsPersistsEachMethodAndReturnsTotal(): void
    {
        $orderId = $this->seedOrder();

        $payments = [
            ['method' => 'dinheiro', 'amount' => 25.50],
            ['method' => 'pix', 'amount' => 10.00],
        ];

        $total = $this->service->registerPayments($this->conn, $orderId, $payments);

        $this->assertEquals(35.50, $total);
        $rows = $this->conn->prepare('SELECT method, amount FROM order_payments WHERE order_id = :oid ORDER BY method');
        $rows->execute(['oid' => $orderId]);
        $results = $rows->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $results);
        $this->assertEquals('dinheiro', $results[0]['method']);
        $this->assertEquals(25.50, (float) $results[0]['amount']);
        $this->assertEquals('pix', $results[1]['method']);
        $this->assertEquals(10.00, (float) $results[1]['amount']);
    }

    public function testRegisterPaymentsSkipsWhenEmpty(): void
    {
        $orderId = $this->seedOrder();

        $total = $this->service->registerPayments($this->conn, $orderId, []);

        $this->assertEquals(0.0, $total);
        $countStmt = $this->conn->prepare('SELECT COUNT(*) as cnt FROM order_payments WHERE order_id = :oid');
        $countStmt->execute(['oid' => $orderId]);
        $row = $countStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertSame(0, (int) $row['cnt']);
    }

    private function seedOrder(): int
    {
        $stmt = $this->conn->prepare('
            INSERT INTO orders (restaurant_id, total, status, order_type, created_at)
            VALUES (:rid, :total, :status, :order_type, NOW())
        ');
        $stmt->execute([
            'rid' => 1,
            'total' => 0,
            'status' => 'novo',
            'order_type' => 'balcao',
        ]);

        return (int) $this->conn->lastInsertId();
    }
}
