<?php

namespace App\Core;

/**
 * Cache Abstraction
 * - Usa Redis se disponível e acessível
 * - Fallback para SimpleCache (file-based)
 */
class Cache
{
    private $adapter;

    public function __construct()
    {
        // Prefere Redis quando a extensão está disponível
        if (class_exists('Redis')) {
            try {
                $r = new \Redis();
                // Tenta conexão local padrão
                if (@$r->connect('127.0.0.1', 6379, 0.5)) {
                    $this->adapter = new RedisAdapter($r);
                    return;
                }
            } catch (\Exception $e) {
                // falha -> fallback
            }
        }

        // Fallback para file-based SimpleCache
        $this->adapter = new SimpleCacheAdapter();
    }

    public function get(string $key, $default = null)
    {
        return $this->adapter->get($key, $default);
    }

    public function put(string $key, $value, int $ttlSeconds = 300): void
    {
        $this->adapter->put($key, $value, $ttlSeconds);
    }

    public function forget(string $key): void
    {
        $this->adapter->forget($key);
    }
}

// Adapter para Redis
class RedisAdapter
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key, $default = null)
    {
        $val = $this->redis->get($key);
        if ($val === false || $val === null) {
            return $default;
        }

        $decoded = json_decode($val, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $default;
        }

        return $decoded;
    }

    public function put(string $key, $value, int $ttlSeconds = 300): void
    {
        $payload = json_encode($value);
        if ($ttlSeconds > 0) {
            $this->redis->setex($key, $ttlSeconds, $payload);
        } else {
            $this->redis->set($key, $payload);
        }
    }

    public function forget(string $key): void
    {
        $this->redis->del($key);
    }
}

// Adapter para SimpleCache (wrapper)
class SimpleCacheAdapter
{
    private SimpleCache $cache;

    public function __construct()
    {
        $this->cache = new SimpleCache();
    }

    public function get(string $key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    public function put(string $key, $value, int $ttlSeconds = 300): void
    {
        $this->cache->put($key, $value, $ttlSeconds);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }
}
