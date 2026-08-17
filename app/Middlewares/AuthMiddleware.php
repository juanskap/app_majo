<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\View;

/**
 * AuthMiddleware: controla el acceso por autenticación y roles.
 */
class AuthMiddleware
{
    public static function requireLogin(): void
    {
        if (!Auth::check()) {
            redirect_to('auth/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(Auth::role(), $roles, true)) {
            http_response_code(403);
            View::render('errors/403', ['roles' => $roles], null);
            exit;
        }
    }
}
