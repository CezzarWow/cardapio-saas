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

        if (!is_array($data) || !array_key_exists('expires_at', $data)) {
            return $default;
        }

        if ($data['expires_at'] !== 0 && time() > $data['expires_at']) {
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
        $expiresAt = $ttlSeconds > 0 ? time() + $ttlSeconds : 0;
        $data = [
            'expires_at' => $expiresAt,
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
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getFilePath(string $key): string
    {
        // Sanitize key
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        if ($safeKey === '') {
            $safeKey = 'key';
        }
        $hash = substr(hash('sha256', $key), 0, 16);
        return $this->cacheDir . '/' . $safeKey . '.' . $hash . '.cache';
    }
}
