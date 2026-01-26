<?php

namespace App\Middleware;

/**
 * CSRF Middleware
 * Protects the application against Cross-Site Request Forgery attacks
 */
class CsrfMiddleware
{
    private const TOKEN_KEY = 'csrf_token';

    /**
     * Handle the incoming request
     *
     * @return bool Returns true if request is valid, false otherwise
     */
    public static function handle(): bool
    {
        // 1. Ensure session is started (should be by index.php)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Generate token if not exists
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        // 3. Check Request Method and Exceptions
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Exceções: Rotas que não exigem verificação CSRF
        // NOTA: Apenas rotas que realmente não podem usar CSRF devem estar aqui.
        // Ver documentação em docs/CSRF_EXCEPTIONS.md para justificativas.
        $exceptions = [
            // '/admin/loja/venda/fechar-comanda', // REMOVIDO: Frontend envia CSRF token no payload
            '/admin/loja/reposicao/ajustar', // Ajuste de estoque via SPA (verificar se pode receber CSRF)
            'reposicao/ajustar', // Variação sem prefixo completo
            '/api/order/create' // API legada (considerar migrar para /api/v1/ com autenticação adequada)
        ];

        // Verifica se a URI atual corresponde a alguma exceção
        foreach ($exceptions as $ex) {
            if (strpos($uri, $ex) !== false) {
                return true;
            }
        }

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true; // Safe methods don't need CSRF check
        }

        // 4. Validate Token for Unsafe Methods (POST, PUT, DELETE, etc)
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        // [FIX] Read from JSON Input if not found in headers, AND stash it for Controllers
        if (!$token && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $token = $input['csrf_token'] ?? null;
            
            // Stash for controllers since php://input is now consumed
            if ($input) {
                $_REQUEST['JSON_BODY'] = $input;
            }
        }

        if (!$token || !hash_equals($_SESSION[self::TOKEN_KEY], $token)) {
            // CSRF inválido check
            http_response_code(403);

            // Detecta se é requisição AJAX/JSON
            http_response_code(403);

            // Detecta se é requisição AJAX/JSON
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $wantsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
            $isJsonRequest = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;

            if ($isAjax || $wantsJson || $isJsonRequest) {
                header('Content-Type: application/json');
                die(json_encode([
                    'success' => false,
                    'message' => 'Token CSRF inválido ou expirado. Por favor, atualize a página (F5) e tente novamente.'
                ]));
            }

            die('Ação não autorizada (Token CSRF Inválido). Atualize a página e tente novamente.');
        }

        return true;
    }

    /**
     * Get the current CSRF Token
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }
}
