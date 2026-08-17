<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';

    /** Busca un usuario por email incluyendo su rol */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT u.*, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE u.email = ?
                LIMIT 1";
        $rows = $this->query($sql, [$email]);
        return $rows[0] ?? null;
    }

    /** Devuelve todos los usuarios con su rol */
    public function allWithRole(): array
    {
        $sql = "SELECT u.id, u.nombres, u.apellidos, u.email, u.telefono, u.estado,
                       u.creado_en, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                ORDER BY u.creado_en DESC";
        return $this->query($sql);
    }

    /** Datos del perfil con rol */
    public function profile(int $id): ?array
    {
        $sql = "SELECT u.id, u.nombres, u.apellidos, u.email, u.telefono, u.estado,
                       r.nombre AS rol, r.descripcion AS rol_descripcion
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE u.id = ?
                LIMIT 1";
        $rows = $this->query($sql, [$id]);
        return $rows[0] ?? null;
    }

    /** Datos del perfil extendido según el rol */
    public function profileFull(int $id): ?array
    {
        $profile = $this->profile($id);
        if (!$profile) {
            return null;
        }

        if ($profile['rol'] === 'estudiante') {
            $sql = "SELECT e.id, e.codigo, e.carrera
                    FROM estudiantes e WHERE e.usuario_id = ? LIMIT 1";
            $rows = $this->query($sql, [$id]);
            $profile['detalle'] = $rows[0] ?? null;
        } elseif ($profile['rol'] === 'docente') {
            $sql = "SELECT d.id, d.titulo, d.especialidad
                    FROM docentes d WHERE d.usuario_id = ? LIMIT 1";
            $rows = $this->query($sql, [$id]);
            $profile['detalle'] = $rows[0] ?? null;
        }

        return $profile;
    }

    /** Valida credenciales: devuelve el usuario si la contraseña es correcta */
    public function attempt(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }
        if ($user['estado'] !== 'activo') {
            return null;
        }
        if (!password_verify($password, $user['password'])) {
            return null;
        }
        return $user;
    }
}
