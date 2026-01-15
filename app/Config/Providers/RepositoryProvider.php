<?php

namespace App\Config\Providers;

use App\Core\Container;
use App\Repositories\AdditionalGroupRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\RestaurantRepository;

class RepositoryProvider implements Provider
{
    public function register(Container $container): void
    {
        // --- REPOSITORIES (Singletons) ---
        $container->singleton(ProductRepository::class, fn () => new ProductRepository());
        $container->singleton(CategoryRepository::class, fn () => new CategoryRepository());
        $container->singleton(AdditionalGroupRepository::class, fn () => new AdditionalGroupRepository());
        $container->singleton(RestaurantRepository::class, fn () => new RestaurantRepository());
        $container->singleton(\App\Repositories\StockRepository::class, fn () => new \App\Repositories\StockRepository());
        $container->singleton(\App\Repositories\TableRepository::class, fn () => new \App\Repositories\TableRepository());
        $container->singleton(\App\Repositories\ClientRepository::class, fn () => new \App\Repositories\ClientRepository());
        $container->singleton(\App\Repositories\Order\OrderRepository::class, fn () => new \App\Repositories\Order\OrderRepository());
        $container->singleton(\App\Repositories\Order\OrderItemRepository::class, fn () => new \App\Repositories\Order\OrderItemRepository());
        $container->singleton(\App\Repositories\Order\OrderPaymentRepository::class, fn () => new \App\Repositories\Order\OrderPaymentRepository());
        $container->singleton(\App\Repositories\CashRegisterRepository::class, fn () => new \App\Repositories\CashRegisterRepository());
        $container->singleton(\App\Repositories\Delivery\DeliveryOrderRepository::class, function ($c) {
            return new \App\Repositories\Delivery\DeliveryOrderRepository(
                $c->get(\App\Repositories\Order\OrderItemRepository::class)
            );
        });
        $container->singleton(\App\Repositories\ComboRepository::class, fn () => new \App\Repositories\ComboRepository());

        // Additional Repos
        $container->singleton(\App\Repositories\AdditionalItemRepository::class, fn () => new \App\Repositories\AdditionalItemRepository());
        $container->singleton(\App\Repositories\AdditionalPivotRepository::class, fn () => new \App\Repositories\AdditionalPivotRepository());
        $container->singleton(\App\Repositories\AdditionalCategoryRepository::class, fn () => new \App\Repositories\AdditionalCategoryRepository());

        // Cardapio Specialized Repos
        $container->singleton(\App\Repositories\Cardapio\CardapioConfigRepository::class, fn () => new \App\Repositories\Cardapio\CardapioConfigRepository());
        $container->singleton(\App\Repositories\Cardapio\BusinessHoursRepository::class, fn () => new \App\Repositories\Cardapio\BusinessHoursRepository());
        $container->singleton(\App\Repositories\Cardapio\CategoryRepository::class, fn () => new \App\Repositories\Cardapio\CategoryRepository());
        $container->singleton(\App\Repositories\Cardapio\ProductRepository::class, fn () => new \App\Repositories\Cardapio\ProductRepository());
        $container->singleton(\App\Repositories\CardapioPublico\CardapioPublicoRepository::class, fn () => new \App\Repositories\CardapioPublico\CardapioPublicoRepository());
    }
}
