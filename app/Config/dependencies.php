<?php

use App\Config\Providers\ControllerProvider;
use App\Config\Providers\InfrastructureProvider;
use App\Config\Providers\RepositoryProvider;
use App\Config\Providers\ServiceProvider;
use App\Config\Providers\ValidatorProvider;
use App\Core\Container;

/**
 * dependencies.php
 *
 * Registers all application dependencies in the Container.
 * NOW MODULARIZED WITH PROVIDERS.
 * Returns the configured Container instance.
 */

$container = new Container();

// 1. Infrastructure (Cache, Database, Core)
(new InfrastructureProvider())->register($container);

// 2. Repositories (Data Access)
(new RepositoryProvider())->register($container);

// 3. Validators (Logic Validation)
(new ValidatorProvider())->register($container);

// 4. Services (Business Logic)
(new ServiceProvider())->register($container);

// 5. Controllers (Request Handling)
(new ControllerProvider())->register($container);

// 6. Event listeners (ETAPA 5: cache invalidation on cardápio change)
\App\Events\EventDispatcher::listen(
    'cardapio.changed',
    new \App\Events\Listeners\InvalidateCardapioCacheListener()
);

return $container;
