<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Container;

/**
 * Testes unitários para Container (Dependency Injection)
 */
class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testBindRegistersFactory(): void
    {
        $this->container->bind('test_key', function() {
            return new \stdClass();
        });

        $this->assertTrue($this->container->has('test_key'));
    }

    public function testGetReturnsInstanceFromFactory(): void
    {
        $this->container->bind('test_key', function() {
            return new \stdClass();
        });

        $instance = $this->container->get('test_key');

        $this->assertInstanceOf(\stdClass::class, $instance);
    }

    public function testSingletonReturnsSameInstance(): void
    {
        $this->container->singleton('test_singleton', function() {
            return new \stdClass();
        });

        $instance1 = $this->container->get('test_singleton');
        $instance2 = $this->container->get('test_singleton');

        $this->assertSame($instance1, $instance2);
    }

    public function testBindCreatesNewInstanceEachTime(): void
    {
        $this->container->bind('test_transient', function() {
            return new \stdClass();
        });

        $instance1 = $this->container->get('test_transient');
        $instance2 = $this->container->get('test_transient');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testGetThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Dependency not found');

        $this->container->get('non_existent_key');
    }

    public function testHasReturnsFalseWhenNotBound(): void
    {
        $this->assertFalse($this->container->has('non_existent_key'));
    }

    public function testHasReturnsTrueWhenBound(): void
    {
        $this->container->bind('test_key', function() {
            return 'value';
        });

        $this->assertTrue($this->container->has('test_key'));
    }
}
