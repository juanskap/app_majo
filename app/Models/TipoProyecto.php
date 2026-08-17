<?php

namespace App\Models;

use App\Core\Model;

class TipoProyecto extends Model
{
    protected string $table = 'tipos_proyecto';

    /** Tipos de proyecto con su cantidad de etapas */
    public function allWithCount(): array
    {
        $sql = "SELECT t.id, t.nombre, t.descripcion, t.activo, t.creado_en,
                       COUNT(e.id) AS total_etapas
                FROM tipos_proyecto t
                LEFT JOIN etapas e ON e.tipo_proyecto_id = t.id
                GROUP BY t.id
                ORDER BY t.nombre";
        return $this->query($sql);
    }

    /** Tipos activos (para selección en formularios) */
    public function allActivos(): array
    {
        return $this->where('activo', 1);
    }
}
