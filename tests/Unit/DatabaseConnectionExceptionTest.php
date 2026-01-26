<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Exceptions\DatabaseConnectionException;

/**
 * Testes unitários para DatabaseConnectionException
 */
class DatabaseConnectionExceptionTest extends TestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $exception = new DatabaseConnectionException('Test message');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testGetUserMessageReturnsSafeMessage(): void
    {
        $exception = new DatabaseConnectionException('Database connection failed: Access denied');

        $userMessage = $exception->getUserMessage();

        $this->assertStringNotContainsString('Access denied', $userMessage);
        $this->assertStringNotContainsString('Database connection failed', $userMessage);
        $this->assertStringContainsString('banco de dados', $userMessage);
    }

    public function testExceptionCanHavePreviousException(): void
    {
        $previous = new \PDOException('Original error');
        $exception = new DatabaseConnectionException('Wrapper message', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
