<?php

namespace App\Middleware;

/**
 * CSRF Middleware
 * Protects the application against Cross-Site Request Forgery attacks.
 *
 * Important: return false when blocking, so Router global middleware can stop execution
 * without hard-exiting the PHP process (this also keeps PHPUnit running).
 */
class CsrfMiddleware
{
    private const TOKEN_KEY = 'csrf_token';

    /**
     * Handle the incoming request.
     *
     * @return bool Returns true if request is valid, false otherwise
     */
    public static function handle(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        // Routes that do not require CSRF validation (should be kept minimal).
        $exceptions = [
            '/admin/loja/reposicao/ajustar',
            'reposicao/ajustar',
            '/api/v1/order/create',
            '/api/order/create',
        ];

        foreach ($exceptions as $ex) {
            if (self::matchesException($path, $ex)) {
                return true;
            }
        }

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        // Token from form or header
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        // Token from JSON body (also stash body for controllers since php://input can be consumed once)
        if (!$token && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            if (is_array($input)) {
                $_REQUEST['JSON_BODY'] = $input;
                $token = $input['csrf_token'] ?? null;
            }
        }

        if (!$token || !hash_equals($_SESSION[self::TOKEN_KEY], $token)) {
            http_response_code(403);

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $wantsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
            $isJsonRequest = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;

            if ($isAjax || $wantsJson || $isJsonRequest) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Token CSRF invalido ou expirado. Atualize a pagina e tente novamente.',
                ]);
                return false;
            }

            echo 'Acao nao autorizada (token CSRF invalido). Atualize a pagina e tente novamente.';
            return false;
        }

        return true;
    }

    private static function matchesException(string $path, string $exception): bool
    {
        if ($exception === '') {
            return false;
        }

        if ($path === $exception) {
            return true;
        }

        return str_ends_with($path, $exception);
    }

    /**
     * Get the current CSRF token.
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
