<?php

namespace App\Events;

/**
 * Contrato para eventos nomeados (ETAPA 4).
 */
interface EventContract
{
    public function eventName(): string;
}
