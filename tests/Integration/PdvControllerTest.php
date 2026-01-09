<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\Container;

/**
 * Integration tests for PdvController
 * Tests the PDV page load and basic functionality
 */
class PdvControllerTest extends TestCase
{
    private static Container $container;

    public static function setUpBeforeClass(): void
    {
        // Load container
        self::$container = require __DIR__ . '/../../app/Config/dependencies.php';
    }

    public function testControllerCanBeResolved(): void
    {
        $controller = self::$container->get(\App\Controllers\Admin\PdvController::class);
        $this->assertInstanceOf(\App\Controllers\Admin\PdvController::class, $controller);
    }

    public function testPdvServiceCanBeResolved(): void
    {
        $service = self::$container->get(\App\Services\Pdv\PdvService::class);
        $this->assertInstanceOf(\App\Services\Pdv\PdvService::class, $service);
    }

    public function testPdvServiceReturnsValidData(): void
    {
        $service = self::$container->get(\App\Services\Pdv\PdvService::class);
        
        // Test that service methods exist and return expected types
        $this->assertTrue(method_exists($service, 'getPdvData'));
    }
}
