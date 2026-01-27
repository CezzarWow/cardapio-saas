<?php

namespace App\Core;

/**
 * Container - Simple Dependency Injection implementation
 *
 * Rules:
 * 1. Manual binding (no reflection magic)
 * 2. Explicit factory closures
 * 3. Supports Singletons and Factory (transient) bindings
 */
class Container
{
    private array $bindings = [];
    private array $instances = [];
    private array $fallbackWarnings = [];

    /**
     * Bind a factory for a class (Transient - new instance every time)
     */
    public function bind(string $key, callable $factory): void
    {
        $this->bindings[$key] = $factory;
    }

    /**
     * Bind a singleton for a class (Instance created once)
     */
    public function singleton(string $key, callable $factory): void
    {
        $this->bindings[$key] = function ($c) use ($factory, $key) {
            if (!isset($this->instances[$key])) {
                $this->instances[$key] = $factory($c);
            }
            return $this->instances[$key];
        };
    }

    /**
     * Resolve a dependency from the container
     */
    public function get(string $key)
    {
        if (!isset($this->bindings[$key])) {
            if (class_exists($key)) {
                return $this->instantiateFallback($key);
            }
            throw new \Exception("Dependency not found: {$key}");
        }

        $factory = $this->bindings[$key];
        return $factory($this);
    }

    /**
     * Check if a binding exists
     */
    public function has(string $key): bool
    {
        return isset($this->bindings[$key]);
    }

    private function instantiateFallback(string $key)
    {
        $this->logFallback($key);
        return new $key();
    }

    private function logFallback(string $key): void
    {
        if (isset($this->fallbackWarnings[$key])) {
            return;
        }
        $this->fallbackWarnings[$key] = true;
        Logger::warning('Container fallback used; bind explicitly instead of instantiating directly', [
            'class' => $key
        ]);
    }
}
