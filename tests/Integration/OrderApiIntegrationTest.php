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
use App\Events\CardapioChangedEvent;
use App\Events\EventDispatcher;
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
        $this->assertNull($cache->get('combos_' . $restaurantId));

        $comboId = $this->createCombo($restaurantId, 'Combo Promo', 22.0, $productId);
        $freshCombos = $repo->getCombosWithItems($restaurantId);
        $this->assertCount(1, $freshCombos);
        $this->assertEquals($comboId, (int) $freshCombos[0]['id']);

        $initialAdditionals = $repo->getAdditionalsWithItems($restaurantId);
        $this->assertEmpty($initialAdditionals['groups']);
        $this->assertNull($cache->get('additionals_' . $restaurantId));

        $groupId = $this->seedAdditionalGroup($restaurantId, 'Extras', true);
        $additionalId = $this->seedAdditionalItem($restaurantId, 'Extra Cheese', 3.00);
        $this->linkAdditionalGroupItem($groupId, $additionalId);
        $this->linkProductToGroup($productId, $groupId);

        $freshAdditionals = $repo->getAdditionalsWithItems($restaurantId);
        $this->assertNotEmpty($freshAdditionals['groups']);
        $this->assertArrayHasKey($groupId, $freshAdditionals['items']);
        $this->assertEquals($additionalId, (int) $freshAdditionals['items'][$groupId][0]['id']);
    }

    /**
     * Testa que checkout com entrega + múltiplas formas de pagamento funciona corretamente
     */
    public function testMultiplePaymentMethodsWithDeliveryFeeCalculation(): void
    {
        $restaurantId = $this->seedRestaurant(12);
        $productId = $this->seedProduct($restaurantId, 10, 25.0, 'Pizza Grande');

        $payload = [
            'restaurant_id' => $restaurantId,
            'customer_name' => 'Cliente Entrega',
            'order_type' => 'entrega',
            'delivery_fee' => 8.00,
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                    'additionals' => [],
                ],
            ],
            'payments' => [
                ['method' => 'pix', 'amount' => 30.00],
                ['method' => 'cartao_credito', 'amount' => 20.00],
                ['method' => 'dinheiro', 'amount' => 8.00],
            ],
        ];

        self::setApiInput((string) json_encode($payload));

        $service = $this->createWebOrderService();
        $controller = new OrderApiController($service);

        ob_start();
        $controller->create();
        $response = json_decode(ob_get_clean(), true);

        $this->assertTrue($response['success']);
        $orderId = (int) $response['order_id'];

        // Valida total = (25 * 2) + 8 = 58
        $stmt = self::$conn->prepare('SELECT total, is_paid, order_type FROM orders WHERE id = :id');
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('delivery', $order['order_type']);
        $this->assertEquals(1, (int) $order['is_paid']);
        $this->assertEqualsWithDelta(58.00, (float) $order['total'], 0.01);

        // Valida 3 linhas em order_payments
        $stmt = self::$conn->prepare('SELECT method, amount FROM order_payments WHERE order_id = :id ORDER BY method');
        $stmt->execute(['id' => $orderId]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(3, $payments);
    }

    /**
     * Testa fluxo completo: checkout → alteração de produto → invalidação de cache → cardápio atualizado
     */
    public function testCheckoutAndCacheInvalidationFlow(): void
    {
        $restaurantId = $this->seedRestaurant(13);
        $productId = $this->seedProduct($restaurantId, 5, 15.0, 'Burger Original');

        // Garante que listener está registrado
        require_once __DIR__ . '/../../app/Config/dependencies.php';

        $cache = new Cache();
        $repo = new CardapioPublicoRepository();

        // Popula cache inicial
        $initialProducts = $repo->getProducts($restaurantId);
        $this->assertCount(1, $initialProducts);
        $this->assertEqualsWithDelta(15.0, (float) $initialProducts[0]['price'], 0.01);

        // Faz checkout via API
        $payload = [
            'restaurant_id' => $restaurantId,
            'customer_name' => 'Cliente Cache Test',
            'order_type' => 'retirada',
            'items' => [
                ['product_id' => $productId, 'quantity' => 1, 'additionals' => []],
            ],
            'payments' => [['method' => 'pix', 'amount' => 15.00]],
        ];

        self::setApiInput((string) json_encode($payload));
        $service = $this->createWebOrderService();
        $controller = new OrderApiController($service);

        ob_start();
        $controller->create();
        $response = json_decode(ob_get_clean(), true);
        $this->assertTrue($response['success']);

        // Altera preço do produto no banco
        self::$conn->prepare('UPDATE products SET price = :price WHERE id = :id')
            ->execute(['price' => 20.0, 'id' => $productId]);

        // Dispara evento de invalidação
        EventDispatcher::dispatch(new CardapioChangedEvent($restaurantId));

        // Busca cardápio novamente - deve refletir novo preço
        $updatedProducts = $repo->getProducts($restaurantId);
        $this->assertCount(1, $updatedProducts);
        $this->assertEqualsWithDelta(20.0, (float) $updatedProducts[0]['price'], 0.01);
    }

    /**
     * Testa que cardápio público reflete atualizações após invalidação de cache
     */
    public function testPublicCardapioReflectsCacheUpdatesAfterChanges(): void
    {
        $restaurantId = $this->seedRestaurant(14);
        $productId = $this->seedProduct($restaurantId, 10, 30.0, 'Combo Familia');

        // Garante listener registrado
        require_once __DIR__ . '/../../app/Config/dependencies.php';

        $cache = new Cache();
        $repo = new CardapioPublicoRepository();

        // Estado inicial: sem combos
        $this->assertEmpty($repo->getCombosWithItems($restaurantId));

        // Cria combo
        $comboId = $this->createCombo($restaurantId, 'Combo Teste Cache', 45.0, $productId);

        // Dispara invalidação
        EventDispatcher::dispatch(new CardapioChangedEvent($restaurantId));

        // Verifica que combo aparece
        $combos = $repo->getCombosWithItems($restaurantId);
        $this->assertCount(1, $combos);
        $this->assertEquals($comboId, (int) $combos[0]['id']);
        $this->assertEquals('Combo Teste Cache', $combos[0]['name']);

        // Cria adicional e vincula
        $groupId = $this->seedAdditionalGroup($restaurantId, 'Bebidas', false);
        $itemId = $this->seedAdditionalItem($restaurantId, 'Coca-Cola', 5.0);
        $this->linkAdditionalGroupItem($groupId, $itemId);
        $this->linkProductToGroup($productId, $groupId);

        // Dispara invalidação
        EventDispatcher::dispatch(new CardapioChangedEvent($restaurantId));

        // Verifica que adicional aparece
        $additionals = $repo->getAdditionalsWithItems($restaurantId);
        $this->assertNotEmpty($additionals['groups']);
        $this->assertArrayHasKey($groupId, $additionals['items']);
        $this->assertEquals('Coca-Cola', $additionals['items'][$groupId][0]['name']);
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
