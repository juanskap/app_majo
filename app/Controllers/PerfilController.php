<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Usuario;

/**
 * Perfil del usuario autenticado: consulta de datos,
 * edición de información básica y cambio de contraseña.
 */
class PerfilController extends Controller
{
    /** Muestra el perfil del usuario actual */
    public function index(): void
    {
        $perfil = (new Usuario())->profileFull(Auth::id());

        $this->view('perfil/index', [
            'title' => 'Mi perfil',
            'perfil' => $perfil,
        ]);
    }

    /** Actualiza nombres, apellidos y teléfono del usuario actual */
    public function actualizarDatos(): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('perfil');
        }

        $nombres = trim((string) Request::post('nombres', ''));
        $apellidos = trim((string) Request::post('apellidos', ''));
        $telefono = trim((string) Request::post('telefono', ''));

        if ($nombres === '' || $apellidos === '') {
            flash('error', 'Nombres y apellidos son obligatorios.');
            redirect_to('perfil');
        }

        (new Usuario())->update(Auth::id(), [
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono ?: null,
        ]);

        $_SESSION['user_nombre'] = $nombres . ' ' . $apellidos;
        flash('success', 'Datos del perfil actualizados.');
        redirect_to('perfil');
    }

    /** Cambia la contraseña del usuario actual */
    public function cambiarPassword(): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('perfil');
        }

        $actual = (string) Request::post('password_actual', '');
        $nueva = (string) Request::post('password', '');
        $confirmar = (string) Request::post('password_confirmar', '');

        $usuario = new Usuario();
        $user = $usuario->find(Auth::id());

        if (!$user) {
            flash('error', 'Cuenta no encontrada.');
            redirect_to('auth/login');
        }

        if (!password_verify($actual, $user['password'])) {
            flash('error', 'La contraseña actual no es correcta.');
            redirect_to('perfil');
        }
        if (strlen($nueva) < 6) {
            flash('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
            redirect_to('perfil');
        }
        if ($nueva !== $confirmar) {
            flash('error', 'La confirmación no coincide con la nueva contraseña.');
            redirect_to('perfil');
        }

        $usuario->update((int) $user['id'], ['password' => password_hash($nueva, PASSWORD_BCRYPT)]);

        Database::getConnection()->prepare(
            "UPDATE password_resets SET usado = 1 WHERE email = ?"
        )->execute([$user['email']]);

        flash('success', 'Contraseña actualizada correctamente.');
        redirect_to('perfil');
    }
}