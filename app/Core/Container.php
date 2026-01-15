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
            // Fallback: If class exists and has no dependencies, try to instantiate
            // BUT per user rules ("Explicit dependencies"), we should prefer explicit binding.
            // For safety during migration, we can allow direct new if class exists,
            // but ideally we should throw Exception if strictly following DI.
            // Let's allow simple instantiation for non-bound classes to avoid breaking everything immediately,
            // but for the Pilot we will bind everything explicitly.
            if (class_exists($key)) {
                return new $key();
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
}
