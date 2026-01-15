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
                if (is_string($value)) {
                    // 1. Trim whitespace
                    $value = trim($value);

                    // 2. Remove internal null bytes
                    $value = str_replace(chr(0), '', $value); // Null byte injection

                    // 3. Normalize newlines
                    $value = str_replace(["\r\n", "\r"], "\n", $value);

                    // 4. Strip tags
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
