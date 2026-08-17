<?php

namespace App\Models;

use App\Core\Model;

class Notificacion extends Model
{
    protected string $table = 'notificaciones';

    /** Últimas notificaciones de un usuario (con código de proyecto) */
    public function porUsuario(int $usuarioId, int $limite = 15): array
    {
        return $this->query(
            "SELECT n.*, p.codigo
             FROM notificaciones n
             LEFT JOIN proyectos p ON p.id = n.proyecto_id
             WHERE n.usuario_id = ?
             ORDER BY n.creado_en DESC, n.id DESC
             LIMIT " . (int) $limite,
            [$usuarioId]
        );
    }

    /** Cantidad de notificaciones no leídas */
    public function noLeidas(int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = 0"
        );
        $stmt->execute([$usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    /** Crea una notificación */
    public function crear(int $usuarioId, string $titulo, string $mensaje, ?int $proyectoId = null, string $tipo = 'info'): int
    {
        return $this->create([
            'usuario_id' => $usuarioId,
            'proyecto_id' => $proyectoId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'leida' => 0,
        ]);
    }

    /** Marca una notificación como leída (solo si es del usuario) */
    public function marcarLeida(int $id, int $usuarioId): bool
    {
        return $this->execute(
            "UPDATE notificaciones SET leida = 1 WHERE id = ? AND usuario_id = ?",
            [$id, $usuarioId]
        );
    }

    /** ids de los usuarios con rol admin */
    public function adminIds(): array
    {
        $rows = $this->query(
            "SELECT u.id FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id WHERE r.nombre = 'admin'"
        );
        return array_map('intval', array_column($rows, 'id'));
    }

    /** Marca todas las notificaciones del usuario como leídas */
    public function marcarTodasLeidas(int $usuarioId): bool
    {
        return $this->execute(
            "UPDATE notificaciones SET leida = 1 WHERE usuario_id = ? AND leida = 0",
            [$usuarioId]
        );
    }
}
