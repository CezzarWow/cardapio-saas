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

        // 3. Check Request Method
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true; // Safe methods don't need CSRF check
        }

        // 4. Validate Token for Unsafe Methods (POST, PUT, DELETE, etc)
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !hash_equals($_SESSION[self::TOKEN_KEY], $token)) {
            // Invalid CSRF Token
            http_response_code(403);
            die('Ação não autorizada (Token CSRF Inválido). Atualize a página e tente novamente.');
        }

        return true;
    }

    /**
     * Get the current CSRF Token
     */
    public static function getToken(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }
}
