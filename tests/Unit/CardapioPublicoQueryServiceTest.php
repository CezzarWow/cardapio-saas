<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\CardapioPublico\CardapioPublicoQueryService;
use App\Repositories\CardapioPublico\CardapioPublicoRepository;

class CardapioPublicoQueryServiceTest extends TestCase
{
    private $repo;
    private $service;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(CardapioPublicoRepository::class);
        $this->service = new CardapioPublicoQueryService($this->repo);
    }

    public function testGetCardapioDataAggregatesCorrectly()
    {
        $rid = 1;

        // Mock method returns
        $this->repo->method('findRestaurantById')->willReturn(['id' => 1, 'name' => 'Burger King']);
        $this->repo->method('getCategories')->willReturn([['id' => 1, 'name' => 'Burgers']]);
        $this->repo->method('getProducts')->willReturn([
            ['id' => 10, 'name' => 'Whopper', 'category_name' => 'Burgers', 'is_featured' => 1]
        ]);
        $this->repo->method('getCombosWithItems')->willReturn([]);
        $this->repo->method('getAdditionalsWithItems')->willReturn(['groups' => [], 'items' => []]);
        $this->repo->method('getProductAdditionalRelations')->willReturn([]);
        $this->repo->method('getConfig')->willReturn(['is_open' => 1]);
        $this->repo->method('getBusinessHours')->willReturn([
            'today' => ['is_open' => true, 'open_time' => '00:00', 'close_time' => '23:59'],
            'yesterday' => ['is_open' => false]
        ]);

        $result = $this->service->getCardapioData($rid);

        $this->assertIsArray($result);
        $this->assertEquals('Burger King', $result['restaurant']['name']);
        $this->assertCount(1, $result['categories']);
        $this->assertCount(1, $result['allProducts']);
        $this->assertCount(1, $result['featuredProducts']);
        $this->assertTrue($result['cardapioConfig']['is_open_now']);
    }

    public function testGetCardapioDataReturnsNullIfRestaurantNotFound()
    {
        $this->repo->method('findRestaurantById')->willReturn(null);
        
        $result = $this->service->getCardapioData(999);

        $this->assertNull($result);
    }
}
