<?php

namespace App\Config\Providers;

use App\Core\Container;
use App\Validators\StockValidator;

class ValidatorProvider implements Provider
{
    public function register(Container $container): void
    {
        // --- VALIDATORS ---
        $container->bind(StockValidator::class, fn () => new StockValidator());
        $container->bind(\App\Validators\RestaurantValidator::class, fn () => new \App\Validators\RestaurantValidator());
        $container->bind(\App\Validators\ConfigValidator::class, fn () => new \App\Validators\ConfigValidator());
        $container->bind(\App\Validators\CategoryValidator::class, fn () => new \App\Validators\CategoryValidator());
        $container->bind(\App\Validators\TableValidator::class, fn () => new \App\Validators\TableValidator());
        $container->bind(\App\Validators\ClientValidator::class, fn () => new \App\Validators\ClientValidator());
        $container->bind(\App\Validators\CashierValidator::class, fn () => new \App\Validators\CashierValidator());
        $container->bind(\App\Validators\CardapioValidator::class, fn () => new \App\Validators\CardapioValidator());
        $container->bind(\App\Validators\AdditionalValidator::class, fn () => new \App\Validators\AdditionalValidator());
        $container->bind(\App\Validators\DeliveryValidator::class, fn () => new \App\Validators\DeliveryValidator());
        $container->bind(\App\Validators\OrderValidator::class, fn () => new \App\Validators\OrderValidator());
    }
}
