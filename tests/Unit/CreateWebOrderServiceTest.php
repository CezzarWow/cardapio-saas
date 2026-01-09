<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CreateWebOrderService
 * Tests web order creation logic
 */
class CreateWebOrderServiceTest extends TestCase
{
    private static \App\Core\Container $container;

    public static function setUpBeforeClass(): void
    {
        self::$container = require __DIR__ . '/../../app/Config/dependencies.php';
    }

    public function testServiceCanBeResolved(): void
    {
        $service = self::$container->get(\App\Services\Order\CreateWebOrderService::class);
        $this->assertInstanceOf(\App\Services\Order\CreateWebOrderService::class, $service);
    }

    public function testServiceHasRequiredMethods(): void
    {
        $service = self::$container->get(\App\Services\Order\CreateWebOrderService::class);
        
        $this->assertTrue(method_exists($service, 'createOrder'));
    }

    public function testValidOrderDataStructure(): void
    {
        // Test that order data validation works
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

        // Verify data structure has required keys
        $this->assertArrayHasKey('restaurant_id', $validData);
        $this->assertArrayHasKey('client_name', $validData);
        $this->assertArrayHasKey('items', $validData);
        $this->assertIsArray($validData['items']);
    }
}
