<?php

namespace Tests\Integration;

use App\Core\Database;
use App\Repositories\CashRegisterRepository;
use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderPaymentRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\TableRepository;
use App\Services\CashRegisterService;
use App\Services\Order\Flows\Balcao\CreateBalcaoSaleAction;
use App\Services\Order\Flows\Comanda\OpenComandaAction;
use App\Services\Order\Flows\Delivery\CreateDeliveryStandaloneAction;
use App\Services\Order\Flows\Mesa\OpenMesaAccountAction;
use App\Services\PaymentService;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

#[Group('integration')]
#[Group('database')]
class OrderFlowIntegrationTest extends TestCase
{
    private static PDO $conn;

    public static function setUpBeforeClass(): void
    {
        TestDatabase::setup();
        self::$conn = Database::connect();
    }

    protected function setUp(): void
    {
        TestDatabase::truncateAll();
    }

    public function testBalcaoSaleFlow(): void
    {
        $restaurantId = $this->seedRestaurant();
        $cashRegisterId = $this->seedCashRegister($restaurantId);
        $productId = $this->seedProduct($restaurantId, 10, 10.0, 'Burger');

        $action = new CreateBalcaoSaleAction(
            new PaymentService(new OrderPaymentRepository()),
            new CashRegisterService(new CashRegisterRepository()),
            new OrderRepository(),
            new OrderItemRepository(),
            new StockRepository()
        );

        $result = $action->execute($restaurantId, [
            'cart' => [
                [
                    'id' => $productId,
                    'product_id' => $productId,
                    'name' => 'Burger',
                    'price' => 10.0,
                    'quantity' => 2,
                ],
            ],
            'discount' => 0,
            'payments' => [
                ['method' => 'dinheiro', 'amount' => 20.0],
            ],
        ]);

        $stmt = self::$conn->prepare('SELECT status, is_paid, total, order_type FROM orders WHERE id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('concluido', $order['status']);
        $this->assertEquals(1, $order['is_paid']);
        $this->assertEquals('balcao', $order['order_type']);
        $this->assertEquals(20.0, (float) $order['total']);

        $stmt = self::$conn->prepare('SELECT quantity FROM order_items WHERE order_id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(2, (int) $item['quantity']);

        $stmt = self::$conn->prepare('SELECT stock FROM products WHERE id = :id');
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(8, (int) $product['stock']);

        $stmt = self::$conn->prepare('SELECT COUNT(*) as cnt FROM order_payments WHERE order_id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $this->assertEquals(1, (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt']);

        $stmt = self::$conn->prepare('SELECT COUNT(*) as cnt FROM cash_movements WHERE order_id = :oid AND cash_register_id = :cid');
        $stmt->execute(['oid' => $result['order_id'], 'cid' => $cashRegisterId]);
        $this->assertEquals(1, (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt']);
    }

    public function testMesaFlow(): void
    {
        $restaurantId = $this->seedRestaurant();
        $productId = $this->seedProduct($restaurantId, 10, 12.0, 'Pizza');
        $tableId = $this->seedTable($restaurantId, '1');

        $action = new OpenMesaAccountAction(
            new OrderRepository(),
            new OrderItemRepository(),
            new TableRepository(),
            new StockRepository()
        );

        $result = $action->execute($restaurantId, [
            'table_id' => $tableId,
            'cart' => [
                [
                    'id' => $productId,
                    'product_id' => $productId,
                    'name' => 'Pizza',
                    'price' => 12.0,
                    'quantity' => 1,
                ],
            ],
        ]);

        $stmt = self::$conn->prepare('SELECT status, order_type FROM orders WHERE id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('aberto', $order['status']);
        $this->assertEquals('mesa', $order['order_type']);

        $stmt = self::$conn->prepare('SELECT status, current_order_id FROM tables WHERE id = :id');
        $stmt->execute(['id' => $tableId]);
        $table = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('ocupada', $table['status']);
        $this->assertEquals($result['order_id'], (int) $table['current_order_id']);

        $stmt = self::$conn->prepare('SELECT COUNT(*) as cnt FROM order_items WHERE order_id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $this->assertEquals(1, (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt']);

        $stmt = self::$conn->prepare('SELECT stock FROM products WHERE id = :id');
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(9, (int) $product['stock']);
    }

    public function testComandaFlow(): void
    {
        $restaurantId = $this->seedRestaurant();
        $clientId = $this->seedClient($restaurantId, 'Client A');
        $productId = $this->seedProduct($restaurantId, 5, 7.5, 'Soda');

        $action = new OpenComandaAction(
            new OrderRepository(),
            new OrderItemRepository(),
            new ClientRepository(),
            new StockRepository()
        );

        $result = $action->execute($restaurantId, [
            'client_id' => $clientId,
            'cart' => [
                [
                    'id' => $productId,
                    'product_id' => $productId,
                    'name' => 'Soda',
                    'price' => 7.5,
                    'quantity' => 2,
                ],
            ],
        ]);

        $stmt = self::$conn->prepare('SELECT status, order_type, client_id FROM orders WHERE id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('aberto', $order['status']);
        $this->assertEquals('comanda', $order['order_type']);
        $this->assertEquals($clientId, (int) $order['client_id']);

        $stmt = self::$conn->prepare('SELECT COUNT(*) as cnt FROM order_items WHERE order_id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $this->assertEquals(1, (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt']);

        $stmt = self::$conn->prepare('SELECT stock FROM products WHERE id = :id');
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(3, (int) $product['stock']);
    }

    public function testDeliveryFlow(): void
    {
        $restaurantId = $this->seedRestaurant();
        $productId = $this->seedProduct($restaurantId, 10, 15.0, 'Pasta');

        $action = new CreateDeliveryStandaloneAction(
            new PaymentService(new OrderPaymentRepository()),
            new OrderRepository(),
            new OrderItemRepository(),
            new ClientRepository(),
            new StockRepository()
        );

        $result = $action->execute($restaurantId, [
            'client_name' => 'Delivery Client',
            'cart' => [
                [
                    'id' => $productId,
                    'product_id' => $productId,
                    'name' => 'Pasta',
                    'price' => 15.0,
                    'quantity' => 1,
                ],
            ],
            'delivery_fee' => 5.0,
            'discount' => 0,
            'payments' => [
                ['method' => 'pix', 'amount' => 20.0],
            ],
        ]);

        $stmt = self::$conn->prepare('SELECT status, order_type, is_paid, total FROM orders WHERE id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('aguardando', $order['status']);
        $this->assertEquals('delivery', $order['order_type']);
        $this->assertEquals(1, $order['is_paid']);
        $this->assertEquals(20.0, (float) $order['total']);

        $stmt = self::$conn->prepare('SELECT COUNT(*) as cnt FROM order_payments WHERE order_id = :id');
        $stmt->execute(['id' => $result['order_id']]);
        $this->assertEquals(1, (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt']);

        $stmt = self::$conn->prepare('SELECT COUNT(*) as cnt FROM clients');
        $stmt->execute();
        $this->assertEquals(1, (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt']);
    }

    private function seedRestaurant(): int
    {
        $userId = 1;
        $restaurantId = 1;

        self::$conn->prepare(
            "INSERT INTO users (id, name, email, password, created_at)
             VALUES (:id, 'Test User', 'test@example.com', 'password', NOW())"
        )->execute(['id' => $userId]);

        self::$conn->prepare(
            "INSERT INTO restaurants (id, user_id, name, slug, created_at)
             VALUES (:id, :uid, 'Test Restaurant', 'test-restaurant', NOW())"
        )->execute(['id' => $restaurantId, 'uid' => $userId]);

        return $restaurantId;
    }

    private function seedCashRegister(int $restaurantId): int
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO cash_registers (restaurant_id, status, opening_balance, opened_at)
             VALUES (:rid, 'aberto', 100.0, NOW())"
        );
        $stmt->execute(['rid' => $restaurantId]);
        return (int) self::$conn->lastInsertId();
    }

    private function seedProduct(int $restaurantId, int $stock, float $price, string $name): int
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO products (restaurant_id, name, stock, price)
             VALUES (:rid, :name, :stock, :price)"
        );
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $name,
            'stock' => $stock,
            'price' => $price,
        ]);

        return (int) self::$conn->lastInsertId();
    }

    private function seedTable(int $restaurantId, string $number): int
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO tables (restaurant_id, number, status)
             VALUES (:rid, :num, 'livre')"
        );
        $stmt->execute(['rid' => $restaurantId, 'num' => $number]);
        return (int) self::$conn->lastInsertId();
    }

    private function seedClient(int $restaurantId, string $name): int
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO clients (restaurant_id, name, type, credit_limit)
             VALUES (:rid, :name, 'fisica', 0)"
        );
        $stmt->execute(['rid' => $restaurantId, 'name' => $name]);
        return (int) self::$conn->lastInsertId();
    }
}
