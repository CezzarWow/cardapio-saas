<?php

namespace Tests\Unit;

use App\Core\Cache;
use App\Core\Database;
use App\Core\SimpleCache;
use App\Repositories\CardapioPublico\CardapioPublicoRepository;
use App\Repositories\ComboRepository;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

class ComboRepositoryTest extends TestCase
{
    private \PDO $conn;
    private ComboRepository $comboRepo;
    private CardapioPublicoRepository $publicRepo;

    public static function setUpBeforeClass(): void
    {
        require __DIR__ . '/../../app/Config/dependencies.php';
    }

    protected function setUp(): void
    {
        TestDatabase::truncateAll();
        $this->conn = Database::connect();
        $this->comboRepo = new ComboRepository();
        $this->publicRepo = new CardapioPublicoRepository();
        (new SimpleCache())->flush();
    }

    public function testComboItemsRespectAllowAdditionalsMetadata(): void
    {
        $restaurantId = 1;
        $userId = 1;
        $productId = $this->seedProduct($restaurantId);

        $this->seedUser($userId);
        $this->seedRestaurant($restaurantId, $userId);

        $comboId = $this->comboRepo->create([
            'restaurant_id' => $restaurantId,
            'name' => 'Combo Especial',
            'description' => 'Combo teste',
            'price' => 30.00,
            'display_order' => 2,
            'is_active' => 1
        ]);

        $this->comboRepo->saveItems($comboId, [$productId], [$productId => 1]);

        $combos = $this->publicRepo->getCombosWithItems($restaurantId);

        $this->assertCount(1, $combos);
        $combo = $combos[0];
        $this->assertArrayHasKey('items', $combo);
        $this->assertCount(1, $combo['items']);

        $item = $combo['items'][0];
        $this->assertSame($productId, (int) $item['product_id']);
        $this->assertEquals(1, (int) $item['allow_additionals']);
        $this->assertStringContainsString('Combo Especial', $combo['name']);
        $this->assertNotEmpty($combo['products_list']);
    }

    public function testCreatingComboClearsCombosCache(): void
    {
        $restaurantId = 2;
        $userId = 2;
        $productId = $this->seedProduct($restaurantId);

        $this->seedUser($userId);
        $this->seedRestaurant($restaurantId, $userId);

        $initialComboId = $this->comboRepo->create([
            'restaurant_id' => $restaurantId,
            'name' => 'Combo Inicial',
            'description' => 'Primeiro combo',
            'price' => 25.00,
            'display_order' => 1,
            'is_active' => 1
        ]);
        $this->comboRepo->saveItems($initialComboId, [$productId], []);

        $comboKey = 'combos_' . $restaurantId;
        $cache = new Cache();

        $this->publicRepo->getCombosWithItems($restaurantId);
        $this->assertNotNull($cache->get($comboKey));

        $this->comboRepo->create([
            'restaurant_id' => $restaurantId,
            'name' => 'Combo Novo',
            'description' => 'Segundo combo',
            'price' => 40.00,
            'display_order' => 2,
            'is_active' => 1
        ]);

        $this->assertNull($cache->get($comboKey), 'A criação de um combo deve disparar o listener e invalidar o cache');
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
            'password' => 'secret',
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
            'slug' => "rest-{$restaurantId}",
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function seedProduct(int $restaurantId): int
    {
        $stmt = $this->conn->prepare('
            INSERT INTO products (restaurant_id, name, stock, price, is_active, item_number)
            VALUES (:rid, :name, :stock, :price, :active, :item_number)
        ');
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => 'Produto Combo',
            'stock' => 20,
            'price' => 12.50,
            'active' => 1,
            'item_number' => 100
        ]);

        return (int) $this->conn->lastInsertId();
    }
}
