<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\SimpleCache;

/**
 * Testes unitários para SimpleCache
 */
class CacheTest extends TestCase
{
    private SimpleCache $cache;
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cache = new SimpleCache();
        $this->cacheDir = __DIR__ . '/../../cache/';
        
        // Limpar cache de teste
        $this->cleanCache();
    }

    protected function tearDown(): void
    {
        $this->cleanCache();
    }

    private function cleanCache(): void
    {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testGetReturnsNullWhenNotCached(): void
    {
        $result = $this->cache->get('non_existent_key');

        $this->assertNull($result);
    }

    public function testPutStoresValue(): void
    {
        $this->cache->put('test_key', 'test_value', 60);

        $result = $this->cache->get('test_key');

        $this->assertEquals('test_value', $result);
    }

    public function testPutStoresArray(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        
        $this->cache->put('test_array', $data, 60);

        $result = $this->cache->get('test_array');

        $this->assertEquals($data, $result);
    }

    public function testForgetRemovesKey(): void
    {
        $this->cache->put('test_key', 'test_value', 60);
        $this->cache->forget('test_key');

        $result = $this->cache->get('test_key');

        $this->assertNull($result);
    }

    public function testGetReturnsNullWhenExpired(): void
    {
        $this->cache->put('test_key', 'test_value', 1); // 1 segundo

        // Esperar 2 segundos (não ideal, mas funcional)
        sleep(2);

        $result = $this->cache->get('test_key');

        $this->assertNull($result);
    }

    public function testFlushRemovesAllCache(): void
    {
        $this->cache->put('key1', 'value1', 60);
        $this->cache->put('key2', 'value2', 60);
        
        $this->cache->flush();

        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
    }
}
