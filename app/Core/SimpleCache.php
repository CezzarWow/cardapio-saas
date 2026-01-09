<?php

namespace App\Core;

/**
 * Simple File-Based Cache
 * Useful for environments without Redis/Memcached types.
 */
class SimpleCache
{
    private string $cacheDir;

    public function __construct()
    {
        // Save in specific cache directory
        $this->cacheDir = __DIR__ . '/../../cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get item from cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (!$data || !isset($data['expires_at'])) {
            return $default;
        }

        if (time() > $data['expires_at']) {
            unlink($file); // Expired
            return $default;
        }

        return $data['payload'];
    }

    /**
     * Put item in cache
     * @param int $ttlSeconds Time to live in seconds
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 300): void
    {
        $file = $this->getFilePath($key);
        $data = [
            'expires_at' => time() + $ttlSeconds,
            'payload' => $value
        ];
        
        file_put_contents($file, json_encode($data));
    }

    /**
     * Remove item from cache
     */
    public function forget(string $key): void
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cache
     */
    public function flush(): void
    {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    private function getFilePath(string $key): string
    {
        // Sanitize key
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }
}
