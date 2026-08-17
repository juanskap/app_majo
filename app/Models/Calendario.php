<?php

namespace App\Models;

use App\Core\Model;

class Calendario extends Model
{
    protected string $table = 'calendario';

    /** Eventos visibles según el rol del usuario (admin: todos; docente: sus tutorías; estudiante: sus proyectos) */
    public function visiblesPorRol(string $rol, int $usuarioId, int $limite = 0): array
    {
        $select = "SELECT c.*, p.codigo, p.nombre AS proyecto_nombre, et.nombre AS etapa_nombre
                   FROM calendario c
                   INNER JOIN proyectos p ON p.id = c.proyecto_id
                   LEFT JOIN etapas et ON et.id = c.etapa_id";

        $where = '';
        $params = [];
        if ($rol === 'docente') {
            $where = " INNER JOIN asignaciones a ON a.proyecto_id = c.proyecto_id AND a.estado = 'activa'
                       INNER JOIN docentes d ON d.id = a.docente_id AND d.usuario_id = ?";
            $params[] = $usuarioId;
        } elseif ($rol === 'estudiante') {
            $where = " INNER JOIN estudiantes e ON e.id = p.estudiante_id AND e.usuario_id = ?";
            $params[] = $usuarioId;
        }

        $sql = $select . $where . " ORDER BY c.fecha_evento ASC";
        if ($limite > 0) {
            $sql .= " LIMIT " . (int) $limite;
        }

        return $this->query($sql, $params);
    }

    /** Próximos eventos (a partir de hoy) visibles para el usuario */
    public function proximos(string $rol, int $usuarioId, int $limite = 5): array
    {
        $sql = "SELECT c.*, p.codigo, p.nombre AS proyecto_nombre, et.nombre AS etapa_nombre
                FROM calendario c
                INNER JOIN proyectos p ON p.id = c.proyecto_id
                LEFT JOIN etapas et ON et.id = c.etapa_id";
        $where = " WHERE c.fecha_evento >= NOW() AND c.estado != 'completado'";
        $params = [];

        if ($rol === 'docente') {
            $where .= " AND EXISTS (SELECT 1 FROM asignaciones a
                                    JOIN docentes d ON d.id = a.docente_id
                                    WHERE a.proyecto_id = c.proyecto_id AND a.estado = 'activa'
                                      AND d.usuario_id = ?)";
            $params[] = $usuarioId;
        } elseif ($rol === 'estudiante') {
            $where .= " AND EXISTS (SELECT 1 FROM estudiantes e
                                    WHERE e.id = p.estudiante_id AND e.usuario_id = ?)";
            $params[] = $usuarioId;
        }

        $sql .= $where . " ORDER BY c.fecha_evento ASC LIMIT " . (int) $limite;
        return $this->query($sql, $params);
    }

    /** Marca como vencidos los eventos cuya fecha ya pasó y siguen pendientes */
    public function marcarVencidos(): void
    {
        $this->execute(
            "UPDATE calendario SET estado = 'vencido'
             WHERE estado = 'pendiente' AND fecha_evento < NOW()"
        );
    }

    /** ¿El evento pertenece a un proyecto que gestiona este docente? */
    public function gestionaDocente(int $eventoId, int $usuarioId): bool
    {
        $rows = $this->query(
            "SELECT 1 FROM calendario c
             INNER JOIN asignaciones a ON a.proyecto_id = c.proyecto_id AND a.estado = 'activa'
             INNER JOIN docentes d ON d.id = a.docente_id
             WHERE c.id = ? AND d.usuario_id = ? LIMIT 1",
            [$eventoId, $usuarioId]
        );
        return !empty($rows);
    }
}
