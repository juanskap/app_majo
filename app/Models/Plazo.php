<?php

namespace App\Models;

use App\Core\Model;

class Plazo extends Model
{
    protected string $table = 'plazos';

    /** Plazos de un proyecto con la descripción de su actividad asociada */
    public function porProyecto(int $proyectoId): array
    {
        return $this->query(
            "SELECT p.*, a.descripcion AS actividad_descripcion
             FROM plazos p
             LEFT JOIN actividades a ON a.id = p.actividad_id
             WHERE p.proyecto_id = ?
             ORDER BY p.fecha_limite ASC, p.id ASC",
            [$proyectoId]
        );
    }

    /** Marca como vencidos los plazos cuya fecha límite ya pasó */
    public function marcarVencidos(int $proyectoId): void
    {
        $this->execute(
            "UPDATE plazos
             SET estado = 'vencido'
             WHERE proyecto_id = ? AND estado = 'activo'
               AND fecha_limite < CURDATE()",
            [$proyectoId]
        );
    }
}
