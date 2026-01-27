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

    /**
     * Encode a value as a JS literal that is safe to embed inside a <script> tag.
     */
    public static function js(mixed $value): string
    {
        return (string) json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Best-effort allowlist for CSS color values used in inline styles.
     * Returns $fallback if $value does not look like a safe color.
     */
    public static function cssColor(mixed $value, string $fallback = ''): string
    {
        $s = trim((string) $value);
        if ($s === '') return $fallback;

        // #RGB, #RRGGBB, #RRGGBBAA
        if (preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?([0-9a-fA-F]{2})?$/', $s) === 1) {
            return $s;
        }

        // rgb(0,0,0) / rgba(0,0,0,0.5)
        if (preg_match('/^rgba?\\(\\s*\\d{1,3}\\s*,\\s*\\d{1,3}\\s*,\\s*\\d{1,3}(\\s*,\\s*(0|1|0?\\.\\d+)\\s*)?\\)$/', $s) === 1) {
            return $s;
        }

        // CSS variable: var(--token)
        if (preg_match('/^var\\(--[a-zA-Z0-9_-]+\\)$/', $s) === 1) {
            return $s;
        }

        return $fallback;
    }

    public static function csrfField(): string
    {
        $token = CsrfMiddleware::getToken();
        return '<input type="hidden" name="csrf_token" value="' . self::e($token) . '">';
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
