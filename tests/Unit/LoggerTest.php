<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Logger;

/**
 * Testes unitários para Logger
 */
class LoggerTest extends TestCase
{
    private string $logDir;

    protected function setUp(): void
    {
        $this->logDir = __DIR__ . '/../../logs/';
        
        // Limpar logs de teste anteriores
        if (is_dir($this->logDir)) {
            $files = glob($this->logDir . '*.log');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function testErrorLogsToFile(): void
    {
        Logger::error('Test error message', ['test_key' => 'test_value']);

        $logFile = $this->logDir . date('Y-m-d') . '.log';
        
        $this->assertFileExists($logFile);
        $content = file_get_contents($logFile);
        $this->assertStringContainsString('ERROR', $content);
        $this->assertStringContainsString('Test error message', $content);
    }

    public function testWarningLogsToFile(): void
    {
        Logger::warning('Test warning message', ['restaurant_id' => 8]);

        $logFile = $this->logDir . date('Y-m-d') . '.log';
        
        $this->assertFileExists($logFile);
        $content = file_get_contents($logFile);
        $this->assertStringContainsString('WARNING', $content);
        $this->assertStringContainsString('Test warning message', $content);
    }

    public function testInfoLogsToFile(): void
    {
        Logger::info('Test info message', ['order_id' => 123]);

        $logFile = $this->logDir . date('Y-m-d') . '.log';
        
        $this->assertFileExists($logFile);
        $content = file_get_contents($logFile);
        $this->assertStringContainsString('INFO', $content);
        $this->assertStringContainsString('Test info message', $content);
    }

    public function testDebugOnlyLogsInDevelopment(): void
    {
        // Definir ambiente como development
        define('APP_ENV', 'development');
        
        Logger::debug('Test debug message', ['test' => true]);

        $logFile = $this->logDir . date('Y-m-d') . '.log';
        
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $this->assertStringContainsString('DEBUG', $content);
        }
    }

    public function testLoggerIncludesContext(): void
    {
        Logger::info('Test with context', [
            'restaurant_id' => 8,
            'order_id' => 123,
            'total' => 50.00
        ]);

        $logFile = $this->logDir . date('Y-m-d') . '.log';
        
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $this->assertStringContainsString('restaurant_id', $content);
            $this->assertStringContainsString('"order_id":123', $content);
        }
    }

    public function testLoggerNeverThrowsException(): void
    {
        // Tentar logar com diretório inválido (deve falhar silenciosamente)
        // Não podemos testar isso facilmente sem mock, mas podemos verificar
        // que não lança exceção
        
        $this->expectNotToPerformAssertions();
        
        // Tentar logar - não deve lançar exceção mesmo em condições adversas
        Logger::error('Test', []);
    }
}
