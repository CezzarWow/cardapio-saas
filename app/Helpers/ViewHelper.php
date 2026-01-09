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
}
