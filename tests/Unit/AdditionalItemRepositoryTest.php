<?php

namespace Tests\Unit;

use App\Core\Cache;
use App\Core\Database;
use App\Core\SimpleCache;
use App\Repositories\AdditionalItemRepository;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

class AdditionalItemRepositoryTest extends TestCase
{
    private AdditionalItemRepository $repo;
    private Cache $cache;

    public static function setUpBeforeClass(): void
    {
        require __DIR__ . '/../../app/Config/dependencies.php';
    }

    protected function setUp(): void
    {
        TestDatabase::truncateAll();
        $this->repo = new AdditionalItemRepository();
        $this->cache = new Cache();
        (new SimpleCache())->flush();
    }

    public function testSaveInvalidatesAdditionalsCache(): void
    {
        $restaurantId = 42;
        $cacheKey = 'additionals_' . $restaurantId;
        $this->cache->put($cacheKey, ['dummy'], 60);

        $this->repo->save($restaurantId, 'Molho Teste', 2.50);

        $this->assertNull($this->cache->get($cacheKey));
    }

    public function testDeleteInvalidatesAdditionalsCache(): void
    {
        $restaurantId = 43;
        $cacheKey = 'additionals_' . $restaurantId;
        $this->cache->put($cacheKey, ['dummy'], 60);

        $insertedId = $this->insertDummyAdditional($restaurantId);

        $this->repo->delete($insertedId, $restaurantId);

        $this->assertNull($this->cache->get($cacheKey));
    }

    private function insertDummyAdditional(int $restaurantId): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            INSERT INTO additional_items (restaurant_id, name, price)
            VALUES (:rid, :name, :price)
        ');
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => 'Dummy',
            'price' => 1.00
        ]);
        return (int) $conn->lastInsertId();
    }
}
