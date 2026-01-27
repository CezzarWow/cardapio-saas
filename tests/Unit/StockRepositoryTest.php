<?php

namespace Tests\Unit;

use App\Core\Database;
use App\Repositories\StockRepository;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

class StockRepositoryTest extends TestCase
{
    private StockRepository $repository;

    protected function setUp(): void
    {
        TestDatabase::truncateAll();
        $this->repository = new StockRepository();
    }

    public function testDecrementReducesStock(): void
    {
        $productId = $this->seedProduct(10);

        $this->repository->decrement($productId, 3);

        $stock = $this->fetchStock($productId);
        $this->assertEquals(7, $stock);
    }

    public function testIncrementIncreasesStock(): void
    {
        $productId = $this->seedProduct(5);

        $stockAfter = $this->repository->increment($productId, 4);

        $this->assertEquals(9, $stockAfter);
        $this->assertEquals(9, $this->fetchStock($productId));
    }

    public function testUpdateStockSetsExactValue(): void
    {
        $productId = $this->seedProduct(2);

        $this->repository->updateStock($productId, 12);

        $stock = $this->fetchStock($productId);
        $this->assertEquals(12, $stock);
    }

    public function testRegisterMovementCreatesRecord(): void
    {
        $productId = $this->seedProduct(10);

        $this->repository->registerMovement(1, $productId, 10, 8, 2, 'venda', 'saida');

        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT COUNT(*) as cnt FROM stock_movements WHERE product_id = :pid');
        $stmt->execute(['pid' => $productId]);

        $this->assertEquals(1, (int) $stmt->fetch()['cnt']);
    }

    private function seedProduct(int $stock): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            'INSERT INTO products (restaurant_id, name, stock, price) VALUES (1, :name, :stock, 10.0)'
        );
        $stmt->execute(['name' => 'Test Product', 'stock' => $stock]);
        return (int) $conn->lastInsertId();
    }

    private function fetchStock(int $productId): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT stock FROM products WHERE id = :id');
        $stmt->execute(['id' => $productId]);
        $row = $stmt->fetch();
        return (int) $row['stock'];
    }
}
