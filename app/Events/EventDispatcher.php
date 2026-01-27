<?php

namespace App\Events;

use App\Core\Logger;

/**
 * Despachante de eventos simples (ETAPA 4).
 * Permite desacoplar lógica: ex. OrderCreated -> invalidar cache, notificar, auditoria.
 *
 * Uso:
 *   EventDispatcher::listen('order.created', function (OrderCreatedEvent $e) { ... });
 *   EventDispatcher::dispatch(new OrderCreatedEvent($orderId, $restaurantId));
 */
final class EventDispatcher
{
    /** @var array<string, list<callable(object): void>> */
    private static array $listeners = [];

    public static function listen(string $eventName, callable $listener): void
    {
        if (!isset(self::$listeners[$eventName])) {
            self::$listeners[$eventName] = [];
        }
        self::$listeners[$eventName][] = $listener;
    }

    public static function dispatch(object $event): void
    {
        $eventName = self::eventName($event);
        $listeners = self::$listeners[$eventName] ?? [];

        foreach ($listeners as $listener) {
            try {
                $listener($event);
            } catch (\Throwable $e) {
                Logger::error('Event listener failed', [
                    'event' => $eventName,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        }
    }

    private static function eventName(object $event): string
    {
        if ($event instanceof EventContract) {
            return $event->eventName();
        }
        $class = $event::class;
        if (str_ends_with($class, 'Event')) {
            return strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1.$2', substr(basename(str_replace('\\', '/', $class)), 0, -5)));
        }
        return $class;
    }

    /** Apenas para testes: limpa os listeners */
    public static function reset(): void
    {
        self::$listeners = [];
    }
}
