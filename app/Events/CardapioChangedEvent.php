<?php

namespace App\Events;

/**
 * Disparado quando dados do cardápio de um restaurante mudam.
 * Listeners podem invalidar cache (ex.: InvalidateCardapioCacheListener).
 *
 * Dispare a partir de: ProductRepository, CategoryRepository, ComboRepository,
 * CardapioConfigRepository, etc., após create/update/delete que afetem o cardápio.
 */
final class CardapioChangedEvent implements EventContract
{
    public function __construct(
        public readonly int $restaurantId,
    ) {
    }

    public function eventName(): string
    {
        return 'cardapio.changed';
    }
}
