<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Usuario;

class AuthController extends Controller
{
    /** Las acciones de autenticación no exigen sesión previa */
    public function __construct()
    {
    }

    /** Muestra el formulario de inicio de sesión (GET) o lo procesa (POST) */
    public function login(): void
    {
        if (Request::isGet()) {
            if (Auth::check()) {
                redirect_to('dashboard');
            }
            $this->view('auth/login', ['title' => 'Iniciar sesión']);
            return;
        }

        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('auth/login');
        }

        $email = trim((string) Request::post('email', ''));
        $password = (string) Request::post('password', '');

        if ($email === '' || $password === '') {
            flash('error', 'Ingresa tu correo y contraseña.');
            $this->flashOld(['email' => $email]);
            redirect_to('auth/login');
        }

        $usuario = new Usuario();
        $user = $usuario->attempt($email, $password);

        if (!$user) {
            flash('error', 'Correo o contraseña incorrectos, o la cuenta está desactivada.');
            $this->flashOld(['email' => $email]);
            redirect_to('auth/login');
        }

        Auth::login((int) $user['id'], $user['rol']);
        $_SESSION['user_nombre'] = $user['nombres'] . ' ' . $user['apellidos'];

        flash('success', 'Bienvenido, ' . $user['nombres'] . '!');
        redirect_to('dashboard');
    }

    /** Cierra la sesión */
    public function logout(): void
    {
        Auth::logout();
        redirect_to('auth/login');
    }

    /** Formulario para solicitar recuperación de contraseña */
    public function olvide(): void
    {
        if (Request::isGet()) {
            $this->view('auth/olvide', ['title' => 'Recuperar contraseña']);
            return;
        }
        $this->enviar();
    }

    /** Procesa la solicitud de recuperación */
    public function enviar(): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('auth/olvide');
        }

        $email = trim((string) Request::post('email', ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Ingresa un correo electrónico válido.');
            $this->flashOld(['email' => $email]);
            redirect_to('auth/olvide');
        }

        $usuario = new Usuario();
        $user = $usuario->firstWhere('email', $email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $stmt = Database::getConnection()->prepare(
                "INSERT INTO password_resets (email, token, expira_en) VALUES (?, ?, ?)"
            );
            $stmt->execute([$email, $token, $expira]);

            $link = url('auth/restablecer/' . $token);
            $cuerpo = "<p>Hola {$user['nombres']},</p>"
                . "<p>Recibimos una solicitud para restablecer tu contraseña de SIGEP.</p>"
                . "<p><a href=\"{$link}\">Haz clic aquí para restablecerla</a></p>"
                . '<p>El enlace expira en 30 minutos. Si no fuiste tú, ignora este mensaje.</p>';

            $enviado = enviar_correo($email, 'Recuperación de contraseña - SIGEP', $cuerpo);

            if ($enviado) {
                flash('success', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');
            } else {
                flash('success', 'Solicitud registrada.');
                flash('demo', 'Modo local (sin servidor de correo): usa este enlace → ' . $link);
            }
        } else {
            flash('success', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');
        }

        redirect_to('auth/olvide');
    }

    /** Muestra el formulario para escribir la nueva contraseña */
    public function restablecer(string $token): void
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT * FROM password_resets WHERE token = ? AND usado = 0 AND expira_en > NOW() LIMIT 1"
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            flash('error', 'El enlace es inválido o ya expiró. Solicita uno nuevo.');
            redirect_to('auth/olvide');
        }

        $this->view('auth/restablecer', [
            'title' => 'Nueva contraseña',
            'token' => $token,
        ]);
    }

    /** Procesa el cambio de contraseña */
    public function cambiar(): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('auth/login');
        }

        $token = (string) Request::post('token', '');
        $password = (string) Request::post('password', '');

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT * FROM password_resets WHERE token = ? AND usado = 0 AND expira_en > NOW() LIMIT 1"
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            flash('error', 'El enlace es inválido o ya expiró.');
            redirect_to('auth/olvide');
        }

        if (strlen($password) < 6) {
            flash('error', 'La contraseña debe tener al menos 6 caracteres.');
            redirect_to('auth/restablecer/' . $token);
        }

        $usuario = new Usuario();
        $user = $usuario->firstWhere('email', $reset['email']);

        if (!$user) {
            flash('error', 'La cuenta asociada ya no existe.');
            redirect_to('auth/login');
        }

        $usuario->update((int) $user['id'], ['password' => password_hash($password, PASSWORD_BCRYPT)]);
        $db->prepare("UPDATE password_resets SET usado = 1 WHERE id = ?")->execute([$reset['id']]);

        flash('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        redirect_to('auth/login');
    }
}

