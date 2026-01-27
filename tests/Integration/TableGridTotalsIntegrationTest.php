<?php

namespace Tests\Integration;

use App\Core\Database;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\TableRepository;
use App\Services\Order\Flows\Mesa\OpenMesaAccountAction;
use App\Services\TableService;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

#[Group('integration')]
#[Group('database')]
class TableGridTotalsIntegrationTest extends TestCase
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

    public function testMesaTotalsUseOrderItemsEvenWithZeroOrderTotal(): void
    {
        $restaurantId = $this->seedRestaurant();
        $tableId = $this->seedTable($restaurantId, '2');
        $productId = $this->seedProduct($restaurantId, 10, 18.5, 'Ribeye');

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
                    'name' => 'Ribeye',
                    'price' => 18.5,
                    'quantity' => 2,
                ],
            ],
        ]);

        self::$conn->prepare('UPDATE orders SET total = 0 WHERE id = :id')
            ->execute(['id' => $result['order_id']]);

        $tables = (new TableService(new TableRepository(), new OrderRepository()))
            ->getAllTables($restaurantId);

        $mesa = array_values(array_filter($tables, fn ($item) => (int) $item['id'] === $tableId))[0] ?? null;

        $this->assertNotNull($mesa, 'Mesa deveria estar no grid');
        $this->assertEquals('ocupada', $mesa['status']);
        $this->assertEqualsWithDelta(37.0, (float) ($mesa['order_total'] ?? 0), 0.01);
    }

    public function testMesaWithEmptyOrderIsFreedInGrid(): void
    {
        $restaurantId = $this->seedRestaurant();
        $tableId = $this->seedTable($restaurantId, '2');

        $orderRepo = new OrderRepository();
        $tableRepo = new TableRepository();

        $orderId = $orderRepo->create([
            'restaurant_id' => $restaurantId,
            'client_id' => null,
            'total' => 0,
            'order_type' => 'mesa',
            'observation' => null,
            'change_for' => null
        ], 'aberto');

        $tableRepo->occupy($tableId, $orderId);

        $tables = (new TableService($tableRepo, new OrderRepository()))
            ->getAllTables($restaurantId);

        $mesa = array_values(array_filter($tables, fn ($item) => (int) $item['id'] === $tableId))[0] ?? null;

        $this->assertNotNull($mesa, 'Mesa deveria estar no grid');
        $this->assertEquals('livre', $mesa['status']);
        $this->assertNull($mesa['current_order_id']);

        $stmt = self::$conn->prepare('SELECT status, current_order_id FROM tables WHERE id = :id');
        $stmt->execute(['id' => $tableId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('livre', $row['status']);
        $this->assertNull($row['current_order_id']);
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

    private function seedTable(int $restaurantId, string $number): int
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO tables (restaurant_id, number, status)
             VALUES (:rid, :num, 'livre')"
        );
        $stmt->execute(['rid' => $restaurantId, 'num' => $number]);
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
}
