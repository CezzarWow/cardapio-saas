<?php

namespace App\Helpers;

use App\Middleware\CsrfMiddleware;

class ViewHelper
{
    public static function csrfField(): string
    {
        $token = CsrfMiddleware::getToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function csrfToken(): string
    {
        return CsrfMiddleware::getToken();
    }

    /**
     * Verifica se a rota atual contém o nome desejado
     * Compatível com a implementação antiga em App\Core\ViewHelper
     */
    public static function isRouteActive(string $rotaDesejada): bool
    {
        $rotaAtual = $_SERVER['REQUEST_URI'] ?? '';
        return preg_match('/\/' . preg_quote($rotaDesejada, '/') . '(\/|$|\?)/', $rotaAtual) === 1;
    }
}
