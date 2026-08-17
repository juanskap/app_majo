<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Notificacion;

/**
 * Notificaciones del usuario actual.
 */
class NotificacionController extends Controller
{
    /** Lista de notificaciones del usuario */
    public function index(): void
    {
        $modelo = new Notificacion();
        $usuarioId = Auth::id();

        $this->view('notificaciones/index', [
            'title' => 'Notificaciones',
            'notificaciones' => $modelo->porUsuario($usuarioId, 100),
            'noLeidas' => $modelo->noLeidas($usuarioId),
        ]);
    }

    /** Marca una notificación como leída */
    public function marcarLeida(int $id): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('notificaciones');
        }
        (new Notificacion())->marcarLeida($id, Auth::id());
        redirect_to('notificaciones');
    }

    /** Marca todas como leídas */
    public function marcarTodas(): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('notificaciones');
        }
        (new Notificacion())->marcarTodasLeidas(Auth::id());
        flash('success', 'Todas las notificaciones marcadas como leídas.');
        redirect_to('notificaciones');
    }
}
