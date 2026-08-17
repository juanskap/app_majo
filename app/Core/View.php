<?php

namespace App\Core;

/**
 * View: renderiza vistas dentro de un layout.
 */
class View
{
    /**
     * Renderiza una vista y la inyecta en un layout.
     *
     * @param string $view   Ruta relativa: 'dashboard/index' → app/Views/dashboard/index.php
     * @param array  $data   Variables disponibles en la vista
     * @param string $layout Layout a usar: 'layouts/main' o null para vista sin layout
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = VIEW_PATH . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "Vista no encontrada: {$view}";
            exit;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = VIEW_PATH . '/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }
}
