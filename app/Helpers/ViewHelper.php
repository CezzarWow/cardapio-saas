<?php

namespace App\Helpers;

use App\Middleware\CsrfMiddleware;
use Exception;

class ViewHelper
{
    /**
     * HTML-escape helper for views.
     * Use for any untrusted content rendered into HTML text/attributes.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

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

    /**
     * Retorna a URL do asset a partir do manifest gerado pelo build.
     * Uso: ViewHelper::asset('cardapio.js')
     */
    public static function asset(string $key): string
    {
        $manifestPath = __DIR__ . '/../../public/dist/assets-manifest.json';
        if (!file_exists($manifestPath)) return '/';

        $json = @file_get_contents($manifestPath);
        if (!$json) return '/';

        $manifest = json_decode($json, true);
        if (!is_array($manifest)) return '/';

        return $manifest[$key] ?? '/';
    }
}
