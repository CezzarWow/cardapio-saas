<?php

namespace Tests\Unit;

use App\Core\Container;
use App\Core\Database;
use App\Services\Order\CreateWebOrderService;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

class CreateWebOrderServiceTest extends TestCase
{
    private static Container $container;
    private CreateWebOrderService $service;
    private \PDO $conn;

    public static function setUpBeforeClass(): void
    {
        self::$container = require __DIR__ . '/../../app/Config/dependencies.php';
    }

    protected function setUp(): void
    {
        TestDatabase::truncateAll();
        $this->service = self::$container->get(CreateWebOrderService::class);
        $this->conn = Database::connect();
    }

    public function testServiceCanBeResolved(): void
    {
        $this->assertInstanceOf(CreateWebOrderService::class, $this->service);
    }

    public function testServiceHasRequiredMethods(): void
    {
        $this->assertTrue(method_exists($this->service, 'execute'));
    }

    public function testValidOrderDataStructure(): void
    {
        $validData = [
            'restaurant_id' => 8,
            'client_name' => 'Test Client',
            'client_phone' => '11999999999',
            'order_type' => 'delivery',
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'price' => 25.00]
            ],
            'total' => 50.00
        ];

        $this->assertArrayHasKey('restaurant_id', $validData);
        $this->assertArrayHasKey('client_name', $validData);
        $this->assertArrayHasKey('items', $validData);
        $this->assertIsArray($validData['items']);
    }

    public function testExecuteFailsWhenRequiredAdditionalMissing(): void
    {
        $restaurantId = 1;
        $userId = 1;
        $this->seedUser($userId);
        $this->seedRestaurant($restaurantId, $userId);

        $productId = $this->seedProduct($restaurantId, [
            'price' => 30.00
        ]);

        $groupId = $this->seedAdditionalGroup($restaurantId, 'Molhos', true);
        $additionalId = $this->seedAdditionalItem($restaurantId, 'Molho Especial', 3.00);
        $this->linkAdditionalGroupItem($groupId, $additionalId);
        $this->linkProductToGroup($productId, $groupId);

        $result = $this->service->execute([
            'restaurant_id' => $restaurantId,
            'customer_name' => 'Cliente Demo',
            'order_type' => 'local',
            'payment_method' => 'dinheiro',
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 1
                ]
            ]
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsStringIgnoringCase('Selecione', $result['message']);
        $this->assertSame(0, $this->orderCount());
    }

    public function testExecuteAppliesPromotionDeliveryAndChange(): void
    {
        $restaurantId = 2;
        $userId = 2;
        $this->seedUser($userId);
        $this->seedRestaurant($restaurantId, $userId);

        $promoPrice = 15.00;
        $productId = $this->seedProduct($restaurantId, [
            'price' => 25.00,
            'is_on_promotion' => 1,
            'promotional_price' => $promoPrice,
            'promo_expires_at' => date('Y-m-d', strtotime('+2 days'))
        ]);

        $groupId = $this->seedAdditionalGroup($restaurantId, 'Tamanho', true);
        $additionalId = $this->seedAdditionalItem($restaurantId, 'Extra Grande', 3.50);
        $this->linkAdditionalGroupItem($groupId, $additionalId);
        $this->linkProductToGroup($productId, $groupId);

        $deliveryFee = 4.75;
        $result = $this->service->execute([
            'restaurant_id' => $restaurantId,
            'customer_name' => 'Cliente Promo',
            'customer_phone' => '21999999999',
            'order_type' => 'entrega',
            'payment_method' => 'pix',
            'delivery_fee' => $deliveryFee,
            'change_amount' => 'R$ 100,00',
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                    'additionals' => [
                        [
                            'id' => $additionalId,
                            'name' => 'Extra Grande',
                            'price' => 3.50
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue($result['success']);
        $order = $this->fetchOrder((int) $result['order_id']);
        $expectedItemPrice = $promoPrice + 3.50;
        $expectedTotal = $expectedItemPrice * 2 + $deliveryFee;

        $this->assertEqualsWithDelta($expectedTotal, (float) $order['total'], 0.01);
        $this->assertEquals(100.0, (float) $order['change_for']);

        $item = $this->fetchOrderItem((int) $result['order_id']);
        $this->assertNotFalse($item);
        $this->assertEqualsWithDelta($expectedItemPrice, (float) $item['price'], 0.001);
    }

    private function seedUser(int $id): void
    {
        $stmt = $this->conn->prepare('
            INSERT INTO users (id, name, email, password, created_at)
            VALUES (:id, :name, :email, :password, :created_at)
        ');
        $stmt->execute([
            'id' => $id,
            'name' => 'User ' . $id,
            'email' => "user{$id}@example.com",
            'password' => 'secret'
            ,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function seedRestaurant(int $restaurantId, int $userId): void
    {
        $stmt = $this->conn->prepare('
            INSERT INTO restaurants (id, user_id, name, slug, created_at)
            VALUES (:id, :user_id, :name, :slug, :created_at)
        ');
        $stmt->execute([
            'id' => $restaurantId,
            'user_id' => $userId,
            'name' => "Rest {$restaurantId}",
            'slug' => "rest-{$restaurantId}"
            ,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function seedProduct(int $restaurantId, array $data): int
    {
        $stmt = $this->conn->prepare('
            INSERT INTO products (restaurant_id, name, stock, price, is_active, item_number, is_on_promotion, promotional_price, promo_expires_at)
            VALUES (:rid, :name, :stock, :price, :active, :item_number, :promo_flag, :promo_price, :promo_expires)
        ');
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $data['name'] ?? 'Produto Teste',
            'stock' => $data['stock'] ?? 100,
            'price' => $data['price'] ?? 10.00,
            'active' => $data['is_active'] ?? 1,
            'item_number' => $data['item_number'] ?? 1,
            'promo_flag' => $data['is_on_promotion'] ?? 0,
            'promo_price' => $data['promotional_price'] ?? null,
            'promo_expires' => $data['promo_expires_at'] ?? null
        ]);

        return (int) $this->conn->lastInsertId();
    }

    private function seedAdditionalGroup(int $restaurantId, string $name, bool $required): int
    {
        $stmt = $this->conn->prepare('
            INSERT INTO additional_groups (restaurant_id, name, required)
            VALUES (:rid, :name, :required)
        ');
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $name,
            'required' => $required ? 1 : 0
        ]);

        return (int) $this->conn->lastInsertId();
    }

    private function seedAdditionalItem(int $restaurantId, string $name, float $price): int
    {
        $stmt = $this->conn->prepare('
            INSERT INTO additional_items (restaurant_id, name, price)
            VALUES (:rid, :name, :price)
        ');
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $name,
            'price' => $price
        ]);

        return (int) $this->conn->lastInsertId();
    }

    private function linkAdditionalGroupItem(int $groupId, int $itemId): void
    {
        $stmt = $this->conn->prepare('
            INSERT INTO additional_group_items (group_id, item_id)
            VALUES (:gid, :iid)
        ');
        $stmt->execute([
            'gid' => $groupId,
            'iid' => $itemId
        ]);
    }

    private function linkProductToGroup(int $productId, int $groupId): void
    {
        $stmt = $this->conn->prepare('
            INSERT INTO product_additional_relations (product_id, group_id)
            VALUES (:pid, :gid)
        ');
        $stmt->execute([
            'pid' => $productId,
            'gid' => $groupId
        ]);
    }

    private function fetchOrder(int $orderId): array
    {
        $stmt = $this->conn->prepare('SELECT total, change_for FROM orders WHERE id = :id');
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($order);
        return $order;
    }

    private function fetchOrderItem(int $orderId): array
    {
        $stmt = $this->conn->prepare('SELECT price FROM order_items WHERE order_id = :oid LIMIT 1');
        $stmt->execute(['oid' => $orderId]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($item);
        return $item;
    }

    private function orderCount(): int
    {
        $stmt = $this->conn->query('SELECT COUNT(*) as cnt FROM orders');
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) ($row['cnt'] ?? 0);
    }
}
