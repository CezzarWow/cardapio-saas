<?php

namespace Tests\Integration;

use App\Core\Database;
use App\Repositories\CashRegisterRepository;
use App\Repositories\Order\OrderPaymentRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\CashRegisterService;
use App\Services\Order\CloseCommandAction;
use App\Services\PaymentService;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

#[Group('integration')]
#[Group('database')]
class CloseCommandActionIntegrationTest extends TestCase
{
    private static $conn;
    private $orderRepo;
    private $cashRegisterRepo;
    private $paymentService;
    private $cashRegisterService;
    private $action;

    private $testOrderId;
    private $testCaixaId;
    private static $testUserId = 1;
    private static $testRestaurantId = 1;

    public static function setUpBeforeClass(): void
    {
        TestDatabase::setup();
        self::$conn = Database::connect();
    }

    protected function setUp(): void
    {
        TestDatabase::truncateAll();

        $this->orderRepo = new OrderRepository();
        $this->cashRegisterRepo = new CashRegisterRepository();
        $this->paymentService = new PaymentService(new OrderPaymentRepository());
        $this->cashRegisterService = new CashRegisterService($this->cashRegisterRepo);

        $this->action = new CloseCommandAction(
            $this->paymentService,
            $this->cashRegisterService,
            $this->orderRepo
        );

        $this->ensureRestaurantExists();
        $this->ensureOpenCashRegister();
        $this->createTestOrder();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    public function testCloseCommandUpdatesStatusToConcluido(): void
    {
        $payments = [
            ['method' => 'pix', 'amount' => 50.00],
        ];

        $this->action->execute(self::$testRestaurantId, $this->testOrderId, $payments);

        $stmt = self::$conn->prepare('SELECT status, is_paid FROM orders WHERE id = :id');
        $stmt->execute(['id' => $this->testOrderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('concluido', $order['status']);
        $this->assertEquals(1, $order['is_paid']);
    }

    public function testCloseCommandWithoutPaymentWhenNotPaidThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Nenhum pagamento informado');

        $this->action->execute(self::$testRestaurantId, $this->testOrderId, []);
    }

    public function testCloseAlreadyClosedCommandThrowsException(): void
    {
        $payments = [['method' => 'dinheiro', 'amount' => 50.00]];
        $this->action->execute(self::$testRestaurantId, $this->testOrderId, $payments);

        $closedOrderId = $this->createClosedTestOrder();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('aberta');

        $this->action->execute(self::$testRestaurantId, $closedOrderId, $payments);
    }

    private function ensureOpenCashRegister(): void
    {
        $stmt = self::$conn->prepare(
            "SELECT id FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto' LIMIT 1"
        );
        $stmt->execute(['rid' => self::$testRestaurantId]);
        $caixa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($caixa) {
            $this->testCaixaId = $caixa['id'];
            return;
        }

        $stmt = self::$conn->prepare(
            "INSERT INTO cash_registers (restaurant_id, status, opening_balance, opened_at)
             VALUES (:rid, 'aberto', 100.00, NOW())"
        );
        $stmt->execute(['rid' => self::$testRestaurantId]);
        $this->testCaixaId = self::$conn->lastInsertId();
    }

    private function createTestOrder(): void
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO orders (restaurant_id, status, order_type, total, is_paid, payment_method, created_at)
             VALUES (:rid, 'aberto', 'comanda', 50.00, 0, 'dinheiro', NOW())"
        );
        $stmt->execute(['rid' => self::$testRestaurantId]);
        $this->testOrderId = self::$conn->lastInsertId();
    }

    private function createClosedTestOrder(): int
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO orders (restaurant_id, status, order_type, total, is_paid, payment_method, created_at)
             VALUES (:rid, 'concluido', 'comanda', 50.00, 1, 'pix', NOW())"
        );
        $stmt->execute(['rid' => self::$testRestaurantId]);
        return (int) self::$conn->lastInsertId();
    }

    private function cleanupTestData(): void
    {
        if ($this->testOrderId) {
            self::$conn->prepare('DELETE FROM order_payments WHERE order_id = :id')
                ->execute(['id' => $this->testOrderId]);
            self::$conn->prepare('DELETE FROM cash_movements WHERE order_id = :id')
                ->execute(['id' => $this->testOrderId]);
            self::$conn->prepare('DELETE FROM orders WHERE id = :id')
                ->execute(['id' => $this->testOrderId]);
        }

        $driver = self::$conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            self::$conn->prepare(
                "DELETE FROM orders
                 WHERE restaurant_id = :rid
                 AND order_type = 'comanda'
                 AND total = 50.00
                 AND created_at > datetime('now', '-1 hour')"
            )->execute(['rid' => self::$testRestaurantId]);
        } else {
            self::$conn->prepare(
                "DELETE FROM orders
                 WHERE restaurant_id = :rid
                 AND order_type = 'comanda'
                 AND total = 50.00
                 AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            )->execute(['rid' => self::$testRestaurantId]);
        }
    }

    private function ensureRestaurantExists(): void
    {
        $this->ensureUserExists();

        $stmt = self::$conn->prepare('SELECT id FROM restaurants WHERE id = :id');
        $stmt->execute(['id' => self::$testRestaurantId]);

        if (!$stmt->fetch()) {
            $stmt = self::$conn->prepare(
                "INSERT INTO restaurants (id, user_id, name, slug, created_at)
                 VALUES (:id, :uid, 'Test Restaurant', 'test-restaurant', NOW())"
            );
            $stmt->execute([
                'id' => self::$testRestaurantId,
                'uid' => self::$testUserId,
            ]);
        }
    }

    private function ensureUserExists(): void
    {
        $stmt = self::$conn->prepare('SELECT id FROM users WHERE id = :id');
        $stmt->execute(['id' => self::$testUserId]);

        if (!$stmt->fetch()) {
            $stmt = self::$conn->prepare(
                "INSERT INTO users (id, name, email, password, created_at)
                 VALUES (:id, 'Test User', 'test@example.com', 'password', NOW())"
            );
            $stmt->execute(['id' => self::$testUserId]);
        }
    }
}
