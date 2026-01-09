<?php

namespace App\Config\Providers;

use App\Core\Container;
use App\Services\Product\ProductService;
use App\Validators\StockValidator;
use App\Controllers\Admin\ProductController;

class ControllerProvider implements Provider
{
    public function register(Container $container): void
    {
        // --- CONTROLLERS ---
        // ProductController
        $container->bind(ProductController::class, function($c) {
            return new ProductController(
                $c->get(ProductService::class),
                $c->get(StockValidator::class)
            );
        });

        // Admin Controllers (Batch 1)
        $container->bind(\App\Controllers\Admin\DashboardController::class, function($c) {
            return new \App\Controllers\Admin\DashboardController(
                $c->get(\App\Services\RestaurantService::class)
            );
        });

        $container->bind(\App\Controllers\Admin\RestaurantController::class, function($c) {
            return new \App\Controllers\Admin\RestaurantController(
                $c->get(\App\Services\RestaurantService::class),
                $c->get(\App\Validators\RestaurantValidator::class)
            );
        });

        $container->bind(\App\Controllers\Admin\AutologinController::class, function($c) {
            return new \App\Controllers\Admin\AutologinController(
                $c->get(\App\Services\RestaurantService::class)
            );
        });

        $container->bind(\App\Controllers\Admin\ConfigController::class, function($c) {
            return new \App\Controllers\Admin\ConfigController(
                $c->get(\App\Services\ConfigService::class),
                $c->get(\App\Validators\ConfigValidator::class)
            );
        });

        $container->bind(\App\Controllers\Admin\CategoryController::class, function($c) {
            return new \App\Controllers\Admin\CategoryController(
                $c->get(\App\Services\CategoryService::class),
                $c->get(\App\Validators\CategoryValidator::class)
            );
        });

        // Admin Controllers (Batch 2)
        $container->bind(\App\Controllers\Admin\StockController::class, function($c) {
            return new \App\Controllers\Admin\StockController(
                $c->get(\App\Services\Stock\StockService::class)
            );
        });

        $container->bind(\App\Controllers\Admin\StockRepositionController::class, function($c) {
            return new \App\Controllers\Admin\StockRepositionController(
                $c->get(\App\Services\Stock\StockService::class),
                $c->get(StockValidator::class)
            );
        });

        $container->bind(\App\Controllers\Admin\StockMovementController::class, function($c) {
            return new \App\Controllers\Admin\StockMovementController(
                $c->get(\App\Services\Stock\StockService::class)
            );
        });

        $container->bind(\App\Controllers\Admin\TableController::class, function($c) {
            return new \App\Controllers\Admin\TableController(
                $c->get(\App\Services\TableService::class),
                $c->get(\App\Validators\TableValidator::class)
            );
        });

        $container->bind(\App\Controllers\Admin\ClientController::class, function($c) {
            return new \App\Controllers\Admin\ClientController(
                $c->get(\App\Services\Client\ClientService::class),
                $c->get(\App\Validators\ClientValidator::class)
            );
        });

        $container->bind(\App\Controllers\Admin\PdvController::class, function($c) {
            return new \App\Controllers\Admin\PdvController(
                $c->get(\App\Services\Pdv\PdvService::class)
            );
        });

        $container->bind(\App\Controllers\Admin\AdditionalController::class, function($c) {
            return new \App\Controllers\Admin\AdditionalController(
                $c->get(\App\Services\Additional\AdditionalService::class),
                $c->get(\App\Validators\AdditionalValidator::class),
                $c->get(\App\Repositories\AdditionalCategoryRepository::class)
            );
        });

        // Cardapio Publico Controller
        $container->bind(\App\Controllers\CardapioPublicoController::class, function($c) {
            return new \App\Controllers\CardapioPublicoController(
                $c->get(\App\Services\CardapioPublico\CardapioPublicoQueryService::class)
            );
        });

        // Delivery Controller
        $container->bind(\App\Controllers\Admin\DeliveryController::class, function($c) {
            return new \App\Controllers\Admin\DeliveryController(
                $c->get(\App\Services\Delivery\DeliveryService::class),
                $c->get(\App\Validators\DeliveryValidator::class)
            );
        });

        // Cardapio Admin Controller
        $container->bind(\App\Controllers\Admin\CardapioController::class, function($c) {
            return new \App\Controllers\Admin\CardapioController(
                $c->get(\App\Services\Cardapio\CardapioQueryService::class),
                $c->get(\App\Services\Cardapio\UpdateCardapioConfigService::class),
                $c->get(\App\Services\Admin\ComboService::class),
                $c->get(\App\Validators\CardapioValidator::class)
            );
        });

        // Cashier Controller
        $container->bind(\App\Controllers\Admin\CashierController::class, function($c) {
            return new \App\Controllers\Admin\CashierController(
                $c->get(\App\Services\Cashier\CashierDashboardService::class),
                $c->get(\App\Services\Cashier\CashierTransactionService::class),
                $c->get(\App\Validators\CashierValidator::class)
            );
        });

        // Order API Controller
        $container->bind(\App\Controllers\Api\OrderApiController::class, function($c) {
            return new \App\Controllers\Api\OrderApiController(
                $c->get(\App\Services\Order\CreateWebOrderService::class)
            );
        });
    }
}
