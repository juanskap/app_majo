<?php

namespace App\Core;

use App\Middlewares\AuthMiddleware;

/**
 * Controller: clase base para todos los controladores.
 * Por defecto exige inicio de sesión en todas sus acciones.
 */
abstract class Controller
{
    public function __construct()
    {
        AuthMiddleware::requireLogin();
    }

    /** Renderiza una vista con datos */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    /** Redirige a una ruta interna */
    protected function redirect(string $path): void
    {
        redirect_to($path);
    }

    /** Guarda valores de formulario en sesión para rellenar tras un error */
    protected function flashOld(array $data): void
    {
        $_SESSION['old'] = $data;
    }
}
