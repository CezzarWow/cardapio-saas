<?php

namespace App\Config\Providers;

use App\Core\Container;

interface Provider
{
    public function register(Container $container): void;
}
