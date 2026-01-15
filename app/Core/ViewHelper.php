<?php

namespace App\Core;

class ViewHelper
{
    /**
     * Proxy para manter compatibilidade com código que usa App\Core\ViewHelper
     */
    public static function isRouteActive($rotaDesejada)
    {
        return \App\Helpers\ViewHelper::isRouteActive((string) $rotaDesejada);
    }
}
