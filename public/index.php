<?php
/**
 * SIGEP - Front Controller
 * Todas las peticiones pasan por aquí (ver .htaccess)
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once APP_PATH . '/Core/bootstrap.php';

use App\Core\Router;

$router = new Router();
$router->dispatch($_GET['url'] ?? '');
