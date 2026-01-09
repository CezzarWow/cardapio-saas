<?php

use App\Core\Container;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\AdditionalGroupRepository;
use App\Services\Product\ProductService;
use App\Controllers\Admin\ProductController;
use App\Validators\StockValidator;

/**
 * dependencies.php
 * 
 * Registers all application dependencies in the Container.
 * Returns the configured Container instance.
 */

$container = new Container();

// --- REPOSITORIES (Singletons) ---
// Note: Database is static singleton, so Repositories can be just new().
// In future, if Database becomes instance, we inject it here.
$container->singleton(ProductRepository::class, fn() => new ProductRepository());
$container->singleton(CategoryRepository::class, fn() => new CategoryRepository());
$container->singleton(AdditionalGroupRepository::class, fn() => new AdditionalGroupRepository());
$container->singleton(RestaurantRepository::class, fn() => new RestaurantRepository());
$container->singleton(\App\Repositories\StockRepository::class, fn() => new \App\Repositories\StockRepository());
$container->singleton(\App\Repositories\TableRepository::class, fn() => new \App\Repositories\TableRepository());
$container->singleton(\App\Repositories\ClientRepository::class, fn() => new \App\Repositories\ClientRepository());
$container->singleton(\App\Repositories\Order\OrderRepository::class, fn() => new \App\Repositories\Order\OrderRepository());
$container->singleton(\App\Repositories\CashRegisterRepository::class, fn() => new \App\Repositories\CashRegisterRepository());
$container->singleton(\App\Repositories\Delivery\DeliveryOrderRepository::class, fn() => new \App\Repositories\Delivery\DeliveryOrderRepository());
$container->singleton(\App\Repositories\ComboRepository::class, fn() => new \App\Repositories\ComboRepository());

// Additional Repos
$container->singleton(\App\Repositories\AdditionalItemRepository::class, fn() => new \App\Repositories\AdditionalItemRepository());
$container->singleton(\App\Repositories\AdditionalPivotRepository::class, fn() => new \App\Repositories\AdditionalPivotRepository());
$container->singleton(\App\Repositories\AdditionalCategoryRepository::class, fn() => new \App\Repositories\AdditionalCategoryRepository());

// Cardapio Specialized Repos
$container->singleton(\App\Repositories\Cardapio\CardapioConfigRepository::class, fn() => new \App\Repositories\Cardapio\CardapioConfigRepository());
$container->singleton(\App\Repositories\Cardapio\BusinessHoursRepository::class, fn() => new \App\Repositories\Cardapio\BusinessHoursRepository());
$container->singleton(\App\Repositories\Cardapio\CategoryRepository::class, fn() => new \App\Repositories\Cardapio\CategoryRepository());
$container->singleton(\App\Repositories\Cardapio\CategoryRepository::class, fn() => new \App\Repositories\Cardapio\CategoryRepository());
$container->singleton(\App\Repositories\Cardapio\ProductRepository::class, fn() => new \App\Repositories\Cardapio\ProductRepository());
$container->singleton(\App\Repositories\CardapioPublico\CardapioPublicoRepository::class, fn() => new \App\Repositories\CardapioPublico\CardapioPublicoRepository());

// --- VALIDATORS ---
$container->bind(StockValidator::class, fn() => new StockValidator());
$container->bind(\App\Validators\RestaurantValidator::class, fn() => new \App\Validators\RestaurantValidator());
$container->bind(\App\Validators\ConfigValidator::class, fn() => new \App\Validators\ConfigValidator());
$container->bind(\App\Validators\CategoryValidator::class, fn() => new \App\Validators\CategoryValidator());
$container->bind(\App\Validators\TableValidator::class, fn() => new \App\Validators\TableValidator());
$container->bind(\App\Validators\ClientValidator::class, fn() => new \App\Validators\ClientValidator());
$container->bind(\App\Validators\CashierValidator::class, fn() => new \App\Validators\CashierValidator());
$container->bind(\App\Validators\CardapioValidator::class, fn() => new \App\Validators\CardapioValidator());
$container->bind(\App\Validators\AdditionalValidator::class, fn() => new \App\Validators\AdditionalValidator());

// --- SERVICES ---
// ProductService needs 3 Repositories injected
$container->singleton(ProductService::class, function($c) {
    return new ProductService(
        $c->get(ProductRepository::class),
        $c->get(CategoryRepository::class),
        $c->get(AdditionalGroupRepository::class)
    );
});

$container->singleton(\App\Services\RestaurantService::class, function($c) {
    return new \App\Services\RestaurantService(
        $c->get(RestaurantRepository::class),
        $c->get(CategoryRepository::class)
    );
});

$container->singleton(\App\Services\ConfigService::class, function($c) {
    return new \App\Services\ConfigService(
        $c->get(RestaurantRepository::class)
    );
});

$container->singleton(\App\Services\CategoryService::class, function($c) {
    return new \App\Services\CategoryService(
        $c->get(CategoryRepository::class)
    );
});

$container->singleton(\App\Services\Stock\StockService::class, function($c) {
    return new \App\Services\Stock\StockService(
        $c->get(\App\Repositories\StockRepository::class),
        $c->get(ProductRepository::class),
        $c->get(CategoryRepository::class)
    );
});

$container->singleton(\App\Services\TableService::class, function($c) {
    return new \App\Services\TableService(
        $c->get(\App\Repositories\TableRepository::class),
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

$container->singleton(\App\Services\Client\ClientService::class, function($c) {
    return new \App\Services\Client\ClientService(
        $c->get(\App\Repositories\ClientRepository::class),
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

$container->singleton(\App\Services\PaymentService::class, function($c) {
    return new \App\Services\PaymentService(
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

$container->singleton(\App\Services\CashRegisterService::class, function($c) {
    return new \App\Services\CashRegisterService(
        $c->get(\App\Repositories\CashRegisterRepository::class)
    );
});

$container->singleton(\App\Services\Delivery\DeliveryService::class, function($c) {
    return new \App\Services\Delivery\DeliveryService(
        $c->get(\App\Repositories\Delivery\DeliveryOrderRepository::class)
    );
});

// --- SERVICES (Batch 4) ---
$container->singleton(\App\Services\Pdv\PdvService::class, function($c) {
    return new \App\Services\Pdv\PdvService(
        $c->get(\App\Repositories\TableRepository::class),
        $c->get(\App\Repositories\Order\OrderRepository::class),
        $c->get(CategoryRepository::class),
        $c->get(ProductRepository::class),
        $c->get(\App\Repositories\StockRepository::class),
        $c->get(\App\Services\CashRegisterService::class)
    );
});

$container->singleton(\App\Services\Cashier\CashierDashboardService::class, function($c) {
    return new \App\Services\Cashier\CashierDashboardService(
        $c->get(\App\Repositories\CashRegisterRepository::class),
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

$container->singleton(\App\Services\Cashier\CashierTransactionService::class, function($c) {
    return new \App\Services\Cashier\CashierTransactionService(
        $c->get(\App\Repositories\Order\OrderRepository::class),
        $c->get(\App\Repositories\TableRepository::class),
        $c->get(\App\Repositories\StockRepository::class),
        $c->get(\App\Repositories\CashRegisterRepository::class)
    );
});

$container->singleton(\App\Services\Admin\ComboService::class, function($c) {
    return new \App\Services\Admin\ComboService(
        $c->get(\App\Repositories\ComboRepository::class)
    );
});

$container->singleton(\App\Services\Cardapio\CardapioQueryService::class, function($c) {
    return new \App\Services\Cardapio\CardapioQueryService(
        $c->get(\App\Repositories\Cardapio\CardapioConfigRepository::class),
        $c->get(\App\Repositories\Cardapio\BusinessHoursRepository::class),
        $c->get(\App\Repositories\Cardapio\CategoryRepository::class),
        $c->get(\App\Repositories\Cardapio\ProductRepository::class),
        $c->get(\App\Repositories\ComboRepository::class),
        $c->get(RestaurantRepository::class)
    );
});

$container->singleton(\App\Services\Cardapio\UpdateCardapioConfigService::class, function($c) {
    return new \App\Services\Cardapio\UpdateCardapioConfigService(
        $c->get(\App\Repositories\Cardapio\CardapioConfigRepository::class),
        $c->get(\App\Repositories\Cardapio\BusinessHoursRepository::class),
        $c->get(\App\Repositories\Cardapio\CategoryRepository::class),
        $c->get(\App\Repositories\Cardapio\ProductRepository::class)
    );
});

$container->singleton(\App\Services\Additional\AdditionalService::class, function($c) {
    return new \App\Services\Additional\AdditionalService(
        $c->get(\App\Repositories\AdditionalItemRepository::class),
        $c->get(AdditionalGroupRepository::class),
        $c->get(\App\Repositories\AdditionalPivotRepository::class),
        $c->get(\App\Repositories\AdditionalCategoryRepository::class)
    );
});

$container->singleton(\App\Services\Order\CreateWebOrderService::class, function($c) {
    return new \App\Services\Order\CreateWebOrderService(
        $c->get(\App\Repositories\ClientRepository::class),
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

$container->singleton(\App\Services\CardapioPublico\CardapioPublicoQueryService::class, function($c) {
    return new \App\Services\CardapioPublico\CardapioPublicoQueryService(
        $c->get(\App\Repositories\CardapioPublico\CardapioPublicoRepository::class)
    );
});

// --- ACTIONS (Order) ---
$container->singleton(\App\Services\Order\CreateOrderAction::class, function($c) {
    return new \App\Services\Order\CreateOrderAction(
        $c->get(\App\Services\PaymentService::class),
        $c->get(\App\Services\CashRegisterService::class),
        $c->get(\App\Repositories\StockRepository::class),
        $c->get(\App\Repositories\Order\OrderRepository::class),
        $c->get(\App\Repositories\TableRepository::class)
    );
});

$container->singleton(\App\Services\Order\CloseTableAction::class, function($c) {
    return new \App\Services\Order\CloseTableAction(
        $c->get(\App\Services\PaymentService::class),
        $c->get(\App\Services\CashRegisterService::class),
        $c->get(\App\Repositories\Order\OrderRepository::class),
        $c->get(\App\Repositories\TableRepository::class)
    );
});

$container->singleton(\App\Services\Order\CloseCommandAction::class, function($c) {
    return new \App\Services\Order\CloseCommandAction(
        $c->get(\App\Services\PaymentService::class),
        $c->get(\App\Services\CashRegisterService::class),
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

$container->singleton(\App\Services\Order\RemoveItemAction::class, function($c) {
    return new \App\Services\Order\RemoveItemAction(
        $c->get(\App\Repositories\StockRepository::class),
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

$container->singleton(\App\Services\Order\CancelOrderAction::class, function($c) {
    return new \App\Services\Order\CancelOrderAction(
        $c->get(\App\Services\Stock\StockService::class),
        $c->get(\App\Repositories\Order\OrderRepository::class),
        $c->get(\App\Repositories\TableRepository::class)
    );
});

$container->singleton(\App\Services\Order\IncludePaidItemsAction::class, function($c) {
    return new \App\Services\Order\IncludePaidItemsAction(
        $c->get(\App\Services\PaymentService::class),
        $c->get(\App\Services\CashRegisterService::class),
        $c->get(\App\Repositories\StockRepository::class),
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

$container->singleton(\App\Services\Order\DeliverOrderAction::class, function($c) {
    return new \App\Services\Order\DeliverOrderAction(
        $c->get(\App\Repositories\Order\OrderRepository::class)
    );
});

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
$container->bind(\App\Validators\DeliveryValidator::class, fn() => new \App\Validators\DeliveryValidator());
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

return $container;

