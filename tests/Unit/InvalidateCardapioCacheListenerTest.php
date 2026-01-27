<?php

namespace Tests\Unit;

use App\Core\Cache;
use App\Core\SimpleCache;
use App\Events\CardapioChangedEvent;
use App\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;

class InvalidateCardapioCacheListenerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Ensure the application wiring registers the listener
        require __DIR__ . '/../../app/Config/dependencies.php';
    }

    protected function setUp(): void
    {
        (new SimpleCache())->flush();
    }

    public function testListenerClearsCardapioKeys(): void
    {
        $cache = new Cache();
        $restaurantId = 12;
        $keys = [
            'cardapio_index_' . $restaurantId . '_v2',
            'categories_' . $restaurantId,
            'config_' . $restaurantId,
            'hours_' . $restaurantId,
            'products_' . $restaurantId,
            'combos_' . $restaurantId,
        ];

        foreach ($keys as $key) {
            $cache->put($key, 'stub', 60);
        }
        $cache->put('product_additional_relations', 'shared', 60);

        EventDispatcher::dispatch(new CardapioChangedEvent($restaurantId));

        foreach ($keys as $key) {
            $this->assertNull($cache->get($key));
        }

        $this->assertNull($cache->get('product_additional_relations'));
    }
}
