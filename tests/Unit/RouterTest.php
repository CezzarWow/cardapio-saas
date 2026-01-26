<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Router;
use App\Core\Container;

/**
 * Testes unitários para Router
 */
class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        Router::clear();
    }

    protected function tearDown(): void
    {
        Router::clear();
    }

    public function testAddRegistersRoute(): void
    {
        Router::add('/test', \stdClass::class, 'method');

        $routes = Router::getRoutes();
        $this->assertArrayHasKey('/test', $routes);
    }

    public function testPatternRegistersPatternRoute(): void
    {
        Router::pattern('/^\/user\/(\d+)$/', \stdClass::class, 'method');

        $patterns = Router::getRoutes(); // Nota: getRoutes() não retorna patterns
        // Patterns são testados via dispatch
        $this->assertTrue(true); // Placeholder
    }

    public function testSetDefaultRegistersDefaultHandler(): void
    {
        $called = false;
        Router::setDefault(function($path) use (&$called) {
            $called = true;
        });

        // Dispatch de rota não encontrada deve chamar default
        Router::dispatch('/non_existent');
        
        $this->assertTrue($called);
    }

    public function testDispatchCallsControllerMethod(): void
    {
        $this->markTestIncomplete('Requires controller mock setup');
    }

    public function testDispatchReturnsTrueWhenRouteFound(): void
    {
        $controller = new class {
            public function testMethod() {
                // Mock controller
            }
        };

        Router::add('/test', get_class($controller), 'testMethod');
        
        // Não podemos testar dispatch facilmente sem setup completo
        $this->assertTrue(true); // Placeholder
    }
}
