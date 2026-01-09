<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\Container;
use App\Controllers\Admin\ProductController;

class ProductControllerTest extends TestCase
{
    private $container;

    protected function setUp(): void
    {
        // Require the dependencies file which returns the configured container
        $this->container = require __DIR__ . '/../../app/Config/dependencies.php';
    }

    public function testContainerResolvesProductController()
    {
        // Verify if the container can substantiate the controller with all dependencies
        $controller = $this->container->get(ProductController::class);
        
        $this->assertInstanceOf(ProductController::class, $controller);
    }
    
    // We can add more tests here to verify if dependencies are shared or new instances
    // based on our configuration (Singleton vs Transient).
    // ProductController is transient (bind), Service is Singleton.
}
