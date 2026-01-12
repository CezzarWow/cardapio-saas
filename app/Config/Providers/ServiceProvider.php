<?php

namespace App\Config\Providers;

use App\Core\Container;
use App\Services\Product\ProductService;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\AdditionalGroupRepository;
use App\Repositories\RestaurantRepository;

class ServiceProvider implements Provider
{
    public function register(Container $container): void
    {
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
                $c->get(\App\Repositories\Order\OrderPaymentRepository::class)
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

        $container->singleton(\App\Services\SalesService::class, function($c) {
            return new \App\Services\SalesService(
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\TableRepository::class),
                $c->get(\App\Repositories\StockRepository::class),
                $c->get(\App\Services\CashRegisterService::class)
            );
        });

        // --- SERVICES (Batch 4) ---
        $container->singleton(\App\Services\Pdv\PdvService::class, function($c) {
            return new \App\Services\Pdv\PdvService(
                $c->get(\App\Repositories\TableRepository::class),
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(CategoryRepository::class),
                $c->get(ProductRepository::class),
                $c->get(\App\Repositories\StockRepository::class),
                $c->get(\App\Services\CashRegisterService::class)
            );
        });

        $container->singleton(\App\Services\Cashier\CashierDashboardService::class, function($c) {
            return new \App\Services\Cashier\CashierDashboardService(
                $c->get(\App\Repositories\CashRegisterRepository::class),
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderPaymentRepository::class)
            );
        });

        $container->singleton(\App\Services\Cashier\CashierTransactionService::class, function($c) {
            return new \App\Services\Cashier\CashierTransactionService(
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\Order\OrderPaymentRepository::class),
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

        $container->singleton(\App\Presenters\CardapioPresenter::class, function($c) {
            return new \App\Presenters\CardapioPresenter();
        });

        $container->singleton(\App\Services\Cardapio\CardapioQueryService::class, function($c) {
            return new \App\Services\Cardapio\CardapioQueryService(
                $c->get(\App\Repositories\Cardapio\CardapioConfigRepository::class),
                $c->get(\App\Repositories\Cardapio\BusinessHoursRepository::class),
                $c->get(\App\Repositories\Cardapio\CategoryRepository::class),
                $c->get(\App\Repositories\Cardapio\ProductRepository::class),
                $c->get(\App\Repositories\ComboRepository::class),
                $c->get(RestaurantRepository::class),
                $c->get(\App\Presenters\CardapioPresenter::class),
                $c->get(\App\Core\SimpleCache::class)
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
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class)
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
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\TableRepository::class),
                $c->get(\App\Repositories\ClientRepository::class)
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
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\CancelOrderAction::class, function($c) {
            return new \App\Services\Order\CancelOrderAction(
                $c->get(\App\Services\Stock\StockService::class),
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\TableRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\IncludePaidItemsAction::class, function($c) {
            return new \App\Services\Order\IncludePaidItemsAction(
                $c->get(\App\Services\PaymentService::class),
                $c->get(\App\Services\CashRegisterService::class),
                $c->get(\App\Repositories\StockRepository::class),
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\DeliverOrderAction::class, function($c) {
            return new \App\Services\Order\DeliverOrderAction(
                $c->get(\App\Repositories\Order\OrderRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\CreateDeliveryLinkedAction::class, function($c) {
            return new \App\Services\Order\CreateDeliveryLinkedAction(
                $c->get(\App\Services\Order\CreateOrderAction::class),
                $c->get(\App\Repositories\TableRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\Order\OrderRepository::class)
            );
        });

        // --- ORCHESTRATOR SERVICE ---
        $container->singleton(\App\Services\OrderOrchestratorService::class, function($c) {
            return new \App\Services\OrderOrchestratorService(
                $c->get(\App\Services\Order\CreateOrderAction::class),
                $c->get(\App\Services\Order\CloseTableAction::class),
                $c->get(\App\Services\Order\CloseCommandAction::class),
                $c->get(\App\Services\Order\RemoveItemAction::class),
                $c->get(\App\Services\Order\CancelOrderAction::class),
                $c->get(\App\Services\Order\IncludePaidItemsAction::class),
                $c->get(\App\Services\Order\DeliverOrderAction::class),
                $c->get(\App\Services\Order\CreateDeliveryLinkedAction::class),
                $c->get(\App\Repositories\Order\OrderRepository::class)
            );
        });

        // ============================================================
        // FLOWS ISOLADOS (Arquitetura Nova)
        // ============================================================

        // --- BALCÃO FLOW ---
        $container->singleton(\App\Services\Order\Flows\Balcao\BalcaoValidator::class, function($c) {
            return new \App\Services\Order\Flows\Balcao\BalcaoValidator();
        });

        $container->singleton(\App\Services\Order\Flows\Balcao\CreateBalcaoSaleAction::class, function($c) {
            return new \App\Services\Order\Flows\Balcao\CreateBalcaoSaleAction(
                $c->get(\App\Services\PaymentService::class),
                $c->get(\App\Services\CashRegisterService::class),
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\StockRepository::class)
            );
        });

        $container->singleton(\App\Controllers\Api\BalcaoController::class, function($c) {
            return new \App\Controllers\Api\BalcaoController(
                $c->get(\App\Services\Order\Flows\Balcao\BalcaoValidator::class),
                $c->get(\App\Services\Order\Flows\Balcao\CreateBalcaoSaleAction::class)
            );
        });

        // --- MESA FLOW ---
        $container->singleton(\App\Services\Order\Flows\Mesa\MesaValidator::class, function($c) {
            return new \App\Services\Order\Flows\Mesa\MesaValidator();
        });

        $container->singleton(\App\Services\Order\Flows\Mesa\OpenMesaAccountAction::class, function($c) {
            return new \App\Services\Order\Flows\Mesa\OpenMesaAccountAction(
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\TableRepository::class),
                $c->get(\App\Repositories\StockRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\Flows\Mesa\AddItemsToMesaAction::class, function($c) {
            return new \App\Services\Order\Flows\Mesa\AddItemsToMesaAction(
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\StockRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\Flows\Mesa\CloseMesaAccountAction::class, function($c) {
            return new \App\Services\Order\Flows\Mesa\CloseMesaAccountAction(
                $c->get(\App\Services\PaymentService::class),
                $c->get(\App\Services\CashRegisterService::class),
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\TableRepository::class),
                $c->get(\App\Services\Order\Flows\Mesa\MesaValidator::class)
            );
        });

        $container->singleton(\App\Controllers\Api\MesaController::class, function($c) {
            return new \App\Controllers\Api\MesaController(
                $c->get(\App\Services\Order\Flows\Mesa\MesaValidator::class),
                $c->get(\App\Services\Order\Flows\Mesa\OpenMesaAccountAction::class),
                $c->get(\App\Services\Order\Flows\Mesa\AddItemsToMesaAction::class),
                $c->get(\App\Services\Order\Flows\Mesa\CloseMesaAccountAction::class)
            );
        });

        // --- COMANDA FLOW ---
        $container->singleton(\App\Services\Order\Flows\Comanda\ComandaValidator::class, function($c) {
            return new \App\Services\Order\Flows\Comanda\ComandaValidator();
        });

        $container->singleton(\App\Services\Order\Flows\Comanda\OpenComandaAction::class, function($c) {
            return new \App\Services\Order\Flows\Comanda\OpenComandaAction(
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\ClientRepository::class),
                $c->get(\App\Repositories\StockRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\Flows\Comanda\AddItemsToComandaAction::class, function($c) {
            return new \App\Services\Order\Flows\Comanda\AddItemsToComandaAction(
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\StockRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\Flows\Comanda\CloseComandaAction::class, function($c) {
            return new \App\Services\Order\Flows\Comanda\CloseComandaAction(
                $c->get(\App\Services\PaymentService::class),
                $c->get(\App\Services\CashRegisterService::class),
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Services\Order\Flows\Comanda\ComandaValidator::class)
            );
        });

        $container->singleton(\App\Controllers\Api\ComandaController::class, function($c) {
            return new \App\Controllers\Api\ComandaController(
                $c->get(\App\Services\Order\Flows\Comanda\ComandaValidator::class),
                $c->get(\App\Services\Order\Flows\Comanda\OpenComandaAction::class),
                $c->get(\App\Services\Order\Flows\Comanda\AddItemsToComandaAction::class),
                $c->get(\App\Services\Order\Flows\Comanda\CloseComandaAction::class)
            );
        });

        // --- DELIVERY FLOW ---
        $container->singleton(\App\Services\Order\Flows\Delivery\DeliveryValidator::class, function($c) {
            return new \App\Services\Order\Flows\Delivery\DeliveryValidator();
        });

        $container->singleton(\App\Services\Order\Flows\Delivery\CreateDeliveryStandaloneAction::class, function($c) {
            return new \App\Services\Order\Flows\Delivery\CreateDeliveryStandaloneAction(
                $c->get(\App\Services\PaymentService::class),
                $c->get(\App\Repositories\Order\OrderRepository::class),
                $c->get(\App\Repositories\Order\OrderItemRepository::class),
                $c->get(\App\Repositories\ClientRepository::class),
                $c->get(\App\Repositories\StockRepository::class)
            );
        });

        $container->singleton(\App\Services\Order\Flows\Delivery\UpdateDeliveryStatusAction::class, function($c) {
            return new \App\Services\Order\Flows\Delivery\UpdateDeliveryStatusAction(
                $c->get(\App\Repositories\Order\OrderRepository::class)
            );
        });

        $container->singleton(\App\Controllers\Api\DeliveryController::class, function($c) {
            return new \App\Controllers\Api\DeliveryController(
                $c->get(\App\Services\Order\Flows\Delivery\DeliveryValidator::class),
                $c->get(\App\Services\Order\Flows\Delivery\CreateDeliveryStandaloneAction::class),
                $c->get(\App\Services\Order\Flows\Delivery\UpdateDeliveryStatusAction::class)
            );
        });
    }
}
