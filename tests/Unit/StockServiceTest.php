<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Stock\StockService;
use App\Repositories\StockRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;

class StockServiceTest extends TestCase
{
    private $stockRepo;
    private $productRepo;
    private $categoryRepo;
    private $service;

    protected function setUp(): void
    {
        $this->stockRepo = $this->createMock(StockRepository::class);
        $this->productRepo = $this->createMock(ProductRepository::class);
        $this->categoryRepo = $this->createMock(CategoryRepository::class);

        $this->service = new StockService(
            $this->stockRepo,
            $this->productRepo,
            $this->categoryRepo
        );
    }

    public function testAdjustStockIncrements()
    {
        $rid = 1;
        $pid = 10;
        $amount = 5;

        // Mock Find Product
        $this->productRepo->method('find')
            ->with($pid, $rid)
            ->willReturn(['id' => $pid, 'stock' => 10, 'name' => 'Coca Cola']);

        // Expect Increment
        $this->stockRepo->expects($this->once())
            ->method('increment')
            ->with($pid, $amount, $rid);

        // Expect Register Movement
        $this->stockRepo->expects($this->once())
            ->method('registerMovement')
            ->with($rid, $pid, 10, 15, 5, 'AJUSTE_MANUAL', 'entrada');

        $result = $this->service->adjustStock($rid, $pid, $amount);

        $this->assertTrue($result['success']);
        $this->assertEquals(15, $result['new_stock']);
    }

    public function testAdjustStockDecrements()
    {
        $rid = 1;
        $pid = 10;
        $amount = -2;

        $this->productRepo->method('find')
            ->with($pid, $rid)
            ->willReturn(['id' => $pid, 'stock' => 10, 'name' => 'Coca Cola']);

        $this->stockRepo->expects($this->once())
            ->method('decrement')
            ->with($pid, 2, $rid);

        $this->stockRepo->expects($this->once())
            ->method('registerMovement')
            ->with($rid, $pid, 10, 8, 2, 'AJUSTE_MANUAL', 'saida');

        $result = $this->service->adjustStock($rid, $pid, $amount);

        $this->assertTrue($result['success']);
        $this->assertEquals(8, $result['new_stock']);
    }

    public function testAdjustStockThrowsIfProductNotFound()
    {
        $this->productRepo->method('find')->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Produto não encontrado');

        $this->service->adjustStock(1, 999, 5);
    }
}
