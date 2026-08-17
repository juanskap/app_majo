<?php

namespace App\Models;

use App\Core\Model;

class Actividad extends Model
{
    protected string $table = 'actividades';

    /** Actividades de un proyecto con nombre del responsable y de la etapa */
    public function porProyecto(int $proyectoId): array
    {
        return $this->query(
            "SELECT a.*,
                    CONCAT(u.nombres, ' ', u.apellidos) AS responsable_nombre,
                    et.nombre AS etapa_nombre
             FROM actividades a
             INNER JOIN usuarios u ON u.id = a.responsable_id
             LEFT JOIN etapas et ON et.id = a.etapa_id
             WHERE a.proyecto_id = ?
             ORDER BY a.fecha_limite ASC, a.id ASC",
            [$proyectoId]
        );
    }

    /** Marca como vencidas las actividades cuya fecha límite ya pasó */
    public function marcarVencidas(int $proyectoId): void
    {
        $this->execute(
            "UPDATE actividades
             SET estado = 'vencida'
             WHERE proyecto_id = ? AND estado IN ('pendiente', 'en_curso')
               AND fecha_limite < CURDATE()",
            [$proyectoId]
        );
    }
}
