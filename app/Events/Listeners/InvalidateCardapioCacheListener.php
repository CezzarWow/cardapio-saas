<?php

namespace App\Events\Listeners;

use App\Core\Cache;
use App\Events\CardapioChangedEvent;

/**
 * Invalida todo o cache de cardápio do restaurante quando CardapioChangedEvent é disparado.
 * ETAPA 5: cache com invalidação automática via eventos.
 */
final class InvalidateCardapioCacheListener
{
    public function __invoke(CardapioChangedEvent $event): void
    {
        $rid = $event->restaurantId;
        $cache = new Cache();

        $keys = [
            'cardapio_index_' . $rid . '_v2',
            'categories_' . $rid,
            'config_' . $rid,
            'hours_' . $rid,
            'products_' . $rid,
            'additionals_' . $rid,
            'combos_' . $rid,
        ];

        foreach ($keys as $key) {
            try {
                $cache->forget($key);
            } catch (\Throwable $e) {
                // Não quebrar o fluxo por falha de cache
            }
        }

        // Chave global usada por vários restaurantes
        try {
            $cache->forget('product_additional_relations');
        } catch (\Throwable $e) {
        }
    }
}
