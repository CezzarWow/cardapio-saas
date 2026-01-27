<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Traits\OrderCreationTrait;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\StockRepository;

/**
 * Testes para OrderCreationTrait
 * 
 * Como traits não podem ser instanciados diretamente,
 * criamos uma classe de teste que usa o trait.
 */
class OrderCreationTraitTest extends TestCase
{
    private function createTraitInstance(): object
    {
        return new class {
            use OrderCreationTrait;
        };
    }

    public function testInsertItemsAndDecrementStockCallsRepositories(): void
    {
        $instance = $this->createTraitInstance();
        
        $itemRepo = $this->createMock(OrderItemRepository::class);
        $stockRepo = $this->createMock(StockRepository::class);
        
        $cart = [
            ['id' => 1, 'quantity' => 2],
            ['id' => 2, 'quantity' => 1]
        ];
        
        $orderId = 123;
        
        // Expect insert to be called once with orderId and cart
        $itemRepo
            ->expects($this->once())
            ->method('insert')
            ->with($orderId, $cart);
        
        // Expect decrement to be called for each item (PHPUnit 11 removed withConsecutive())
        $calls = [];
        $stockRepo
            ->expects($this->exactly(2))
            ->method('decrement')
            ->willReturnCallback(function ($productId, $qty) use (&$calls) {
                $calls[] = [$productId, $qty];
            });
        
        // Use reflection to call protected method
        $reflection = new \ReflectionClass($instance);
        $method = $reflection->getMethod('insertItemsAndDecrementStock');
        $method->setAccessible(true);
        
        $method->invoke($instance, $orderId, $cart, $itemRepo, $stockRepo);

        $this->assertSame([[1, 2], [2, 1]], $calls);
    }

    public function testLogOrderCreatedFormatsMessageCorrectly(): void
    {
        $instance = $this->createTraitInstance();
        
        // Use reflection to call protected method
        $reflection = new \ReflectionClass($instance);
        $method = $reflection->getMethod('logOrderCreated');
        $method->setAccessible(true);
        
        // Não podemos facilmente testar o output do Logger sem mock,
        // mas podemos verificar que não lança exceção
        $this->expectNotToPerformAssertions();
        
        $method->invoke($instance, 'MESA', 123, [
            'restaurant_id' => 8,
            'total' => 50.00
        ]);
    }

    public function testLogOrderErrorFormatsMessageCorrectly(): void
    {
        $instance = $this->createTraitInstance();
        
        $exception = new \Exception('Test error');
        
        // Use reflection to call protected method
        $reflection = new \ReflectionClass($instance);
        $method = $reflection->getMethod('logOrderError');
        $method->setAccessible(true);
        
        // Não lança exceção
        $this->expectNotToPerformAssertions();
        
        $method->invoke($instance, 'COMANDA', 'abrir', $exception, [
            'restaurant_id' => 8
        ]);
    }
}
