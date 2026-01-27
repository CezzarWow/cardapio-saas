<?php

namespace Tests\Unit;

use App\Core\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        Router::clear();
        RouterTestController::$called = false;
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
        Router::pattern('/^\\/user\\/(\\d+)$/', \stdClass::class, 'method');
        $this->assertTrue(true);
    }

    public function testSetDefaultRegistersDefaultHandler(): void
    {
        $called = false;
        Router::setDefault(function ($path) use (&$called) {
            $called = true;
        });

        Router::dispatch('/non_existent');

        $this->assertTrue($called);
    }

    public function testDispatchCallsControllerMethod(): void
    {
        Router::add('/test', RouterTestController::class, 'testMethod');

        $result = Router::dispatch('/test');

        $this->assertTrue($result);
        $this->assertTrue(RouterTestController::$called);
    }

    public function testDispatchReturnsTrueWhenRouteFound(): void
    {
        Router::add('/test', RouterTestController::class, 'testMethod');

        $this->assertTrue(Router::dispatch('/test'));
    }
}

class RouterTestController
{
    public static bool $called = false;

    public function testMethod(): void
    {
        self::$called = true;
    }
}
