<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Middlewares\AuthMiddleware;

/**
 * Historial de acciones del sistema. Solo acceso del rol administrador.
 */
class HistorialController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        AuthMiddleware::requireRole('admin');
    }

    /** Lista el historial de acciones */
    public function index(): void
    {
        $filtro = trim((string) Request::get('q', ''));
        $db = Database::getConnection();

        $sql = "SELECT h.id, h.accion, h.descripcion, h.creado_en,
                       CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                       u.email, p.codigo AS proyecto
                FROM historial_acciones h
                INNER JOIN usuarios u ON u.id = h.usuario_id
                LEFT JOIN proyectos p ON p.id = h.proyecto_id";
        $params = [];

        if ($filtro !== '') {
            $sql .= " WHERE h.accion LIKE ? OR h.descripcion LIKE ? OR u.email LIKE ? OR CONCAT(u.nombres, ' ', u.apellidos) LIKE ?";
            $like = '%' . $filtro . '%';
            $params = [$like, $like, $like, $like];
        }

        $sql .= " ORDER BY h.id DESC LIMIT 500";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();

        $this->view('historial/index', [
            'title' => 'Historial de acciones',
            'registros' => $registros,
            'filtro' => $filtro,
        ]);
    }
}