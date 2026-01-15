<?php

namespace App\Config\Providers;

use App\Core\Container;

class InfrastructureProvider implements Provider
{
    public function register(Container $container): void
    {
        // --- CACHE ---
        $container->singleton(\App\Core\SimpleCache::class, fn () => new \App\Core\SimpleCache());
    }
}
