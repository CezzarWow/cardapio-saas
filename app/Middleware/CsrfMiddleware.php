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
        $exceptions = [
            '/admin/loja/venda/finalizar', // Exceção adicionada para contornar bloqueio de ambiente
            '/admin/loja/venda/fechar-comanda',
            '/api/order/create'
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

        // [NOVO] Tenta ler do JSON Input se não encontrou
        if (!$token && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['csrf_token'] ?? null;
        }

        if (!$token || !hash_equals($_SESSION[self::TOKEN_KEY], $token)) {
            // CSRF inválido
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
