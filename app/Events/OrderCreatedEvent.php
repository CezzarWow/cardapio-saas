<?php

namespace App\Events;

/**
 * Evento disparado quando um pedido é criado (ETAPA 4).
 * Listeners podem invalidar cache do cardápio, enviar notificações, etc.
 */
final class OrderCreatedEvent implements EventContract
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $restaurantId,
        public readonly string $orderType,
        public readonly string $status,
    ) {
    }

    public function eventName(): string
    {
        return 'order.created';
    }
}
