<?php

namespace App\Middleware;

/**
 * Request Sanitizer Middleware
 * Automatically cleans incoming request data (GET, POST, COOKIE, REQUEST)
 */
class RequestSanitizerMiddleware
{
    /**
     * Handle the incoming request
     * 
     * @return bool Returns true to continue execution
     */
    public static function handle(): bool
    {
        self::clean($_GET);
        self::clean($_POST);
        self::clean($_COOKIE);
        self::clean($_REQUEST);

        return true;
    }

    /**
     * Recursively clean the array
     * 
     * @param array $data Passed by reference
     */
    private static function clean(array &$data): void
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                self::clean($value);
            } else {
                // 1. Trim whitespace
                // 2. Remove internal null bytes
                // 3. Strip HTML tags (basic XSS prevention for this app type)
                // Note: We avoid strip_tags on specific keys if needed, 
                // but for this POS app, no HTML input is expected.
                $value = trim($value);
                $value = str_replace(chr(0), '', $value); // Null byte injection
                
                // Only strip tags for strings
                if (is_string($value)) {
                    // Normalize newlines
                    $value = str_replace(["\r\n", "\r"], "\n", $value);
                    // Strip tags
                    $value = strip_tags($value);
                }
            }
        }
    }

    /**
     * Public method to sanitize any array (useful for JSON APIs)
     */
    public static function sanitize(array $data): array
    {
        self::clean($data);
        return $data;
    }
}
