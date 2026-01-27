<?php

namespace Tests\Integration;

use App\Controllers\Api\OrderApiController;
use App\Core\Cache;
use App\Core\Database;
use App\Core\SimpleCache;
use App\Repositories\AdditionalItemRepository;
use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\Order\OrderPaymentRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CardapioPublico\CardapioPublicoRepository;
use App\Services\Order\CreateWebOrderService;
use App\Services\PaymentService;
use App\Repositories\ComboRepository;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

#[Group('integration')]
#[Group('database')]
class OrderApiIntegrationTest extends TestCase
{
    private static PDO $conn;
    private static ?string $apiPayload = null;

    public static function setUpBeforeClass(): void
    {
        TestDatabase::setup();
        self::$conn = Database::connect();
    }

    protected function setUp(): void
    {
        TestDatabase::truncateAll();
        (new SimpleCache())->flush();
        self::$apiPayload = null;
    }

    public static function setApiInput(string $payload): void
    {
        self::$apiPayload = $payload;
    }

    public static function readApiInput(): string
    {
        return self::$apiPayload ?? '';
    }

    public function testOrderApiControllerProcessesComboCheckout(): void
    {
        $restaurantId = $this->seedRestaurant(10);
        $productId = $this->seedProduct($restaurantId, 5, 18.0, 'Combo Burger');

        $groupId = $this->seedAdditionalGroup($restaurantId, 'Molhos', true);
        $additionalId = $this->seedAdditionalItem($restaurantId, 'Molho Especial', 2.50);
        $this->linkAdditionalGroupItem($groupId, $additionalId);
        $this->linkProductToGroup($productId, $groupId);

        $payload = [
            'restaurant_id' => $restaurantId,
            'customer_name' => 'Cliente API',
            'order_type' => 'entrega',
            'delivery_fee' => 4.00,
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 1,
                    'additionals' => [
                        [
                            'id' => $additionalId,
                            'name' => 'Molho Especial',
                            'price' => 2.50,
                        ],
                    ],
                ],
            ],
            'payments' => [
                ['method' => 'pix', 'amount' => 15.00],
                ['method' => 'dinheiro', 'amount' => 9.50],
            ],
        ];

        self::setApiInput((string) json_encode($payload));

        $service = $this->createWebOrderService();
        $controller = new OrderApiController($service);

        ob_start();
        $controller->create();
        $response = json_decode(ob_get_clean(), true);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertGreaterThan(0, (int) ($response['order_id'] ?? 0));

        $stmt = self::$conn->prepare('SELECT total, is_paid, order_type FROM orders WHERE id = :id');
        $stmt->execute(['id' => (int) $response['order_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('delivery', $order['order_type']);
        $this->assertEquals(1, (int) $order['is_paid']);
        $this->assertEqualsWithDelta(24.50, (float) $order['total'], 0.01);

        $stmt = self::$conn->prepare('SELECT COUNT(*) as cnt FROM order_payments WHERE order_id = :id');
        $stmt->execute(['id' => (int) $response['order_id']]);
        $this->assertEquals(2, (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt']);
    }

    public function testCardapioCacheInvalidatesAfterComboAndAdditionalChanges(): void
    {
        $restaurantId = $this->seedRestaurant(11);
        $productId = $this->seedProduct($restaurantId, 8, 12.0, 'Promo Pasta');

        $cache = new Cache();
        $repo = new CardapioPublicoRepository();

        $initialCombos = $repo->getCombosWithItems($restaurantId);
        $this->assertEmpty($initialCombos);
        $this->assertEquals([], $cache->get('combos_' . $restaurantId));

        $comboId = $this->createCombo($restaurantId, 'Combo Promo', 22.0, $productId);
        $freshCombos = $repo->getCombosWithItems($restaurantId);
        $this->assertCount(1, $freshCombos);
        $this->assertEquals($comboId, (int) $freshCombos[0]['id']);

        $initialAdditionals = $repo->getAdditionalsWithItems($restaurantId);
        $this->assertEmpty($initialAdditionals['groups']);
        $this->assertEquals([], $cache->get('additionals_' . $restaurantId));

        $groupId = $this->seedAdditionalGroup($restaurantId, 'Extras', true);
        $additionalId = $this->seedAdditionalItem($restaurantId, 'Extra Cheese', 3.00);
        $this->linkAdditionalGroupItem($groupId, $additionalId);
        $this->linkProductToGroup($productId, $groupId);

        $freshAdditionals = $repo->getAdditionalsWithItems($restaurantId);
        $this->assertNotEmpty($freshAdditionals['groups']);
        $this->assertArrayHasKey($groupId, $freshAdditionals['items']);
        $this->assertEquals($additionalId, (int) $freshAdditionals['items'][$groupId][0]['id']);
    }

    private function createWebOrderService(): CreateWebOrderService
    {
        return new CreateWebOrderService(
            new ClientRepository(),
            new OrderRepository(),
            new OrderItemRepository(),
            new ProductRepository(),
            new AdditionalItemRepository(),
            new PaymentService(new OrderPaymentRepository())
        );
    }

    private function seedRestaurant(int $id): int
    {
        $userId = $id;

        self::$conn->prepare(
            "INSERT INTO users (id, name, email, password, created_at)
             VALUES (:id, :name, :email, :password, NOW())"
        )->execute([
            'id' => $userId,
            'name' => 'Integration User ' . $userId,
            'email' => "user{$userId}@example.com",
            'password' => 'secret',
        ]);

        self::$conn->prepare(
            "INSERT INTO restaurants (id, user_id, name, slug, created_at)
             VALUES (:id, :uid, :name, :slug, NOW())"
        )->execute([
            'id' => $id,
            'uid' => $userId,
            'name' => "Rest {$id}",
            'slug' => "rest-{$id}",
        ]);

        return $id;
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

    private function seedAdditionalGroup(int $restaurantId, string $name, bool $required): int
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO additional_groups (restaurant_id, name, required)
             VALUES (:rid, :name, :required)"
        );
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $name,
            'required' => $required ? 1 : 0,
        ]);

        return (int) self::$conn->lastInsertId();
    }

    private function seedAdditionalItem(int $restaurantId, string $name, float $price): int
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO additional_items (restaurant_id, name, price)
             VALUES (:rid, :name, :price)"
        );
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $name,
            'price' => $price,
        ]);

        return (int) self::$conn->lastInsertId();
    }

    private function linkAdditionalGroupItem(int $groupId, int $itemId): void
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO additional_group_items (group_id, item_id)
             VALUES (:gid, :iid)"
        );
        $stmt->execute(['gid' => $groupId, 'iid' => $itemId]);
    }

    private function linkProductToGroup(int $productId, int $groupId): void
    {
        $stmt = self::$conn->prepare(
            "INSERT INTO product_additional_relations (product_id, group_id)
             VALUES (:pid, :gid)"
        );
        $stmt->execute(['pid' => $productId, 'gid' => $groupId]);
    }

    private function createCombo(int $restaurantId, string $name, float $price, int $productId): int
    {
        $comboRepo = new ComboRepository();
        $comboId = $comboRepo->create([
            'restaurant_id' => $restaurantId,
            'name' => $name,
            'description' => 'Promoção',
            'price' => $price,
            'display_order' => 1,
            'is_active' => 1,
        ]);

        $comboRepo->saveItems($comboId, [$productId], [$productId => 1]);
        return $comboId;
    }
}


namespace App\Controllers\Api;

use Tests\Integration\OrderApiIntegrationTest;

if (!function_exists(__NAMESPACE__ . '\\file_get_contents')) {
    function file_get_contents($path)
    {
        if ($path === 'php://input') {
            return OrderApiIntegrationTest::readApiInput();
        }

        return \file_get_contents($path);
    }
}
