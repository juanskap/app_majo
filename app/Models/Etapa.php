<?php

namespace App\Models;

use App\Core\Model;

class Etapa extends Model
{
    protected string $table = 'etapas';

    /** Etapas de un tipo de proyecto ordenadas */
    public function byTipo(int $tipoProyectoId): array
    {
        return $this->query(
            "SELECT * FROM etapas WHERE tipo_proyecto_id = ? ORDER BY orden",
            [$tipoProyectoId]
        );
    }

    /** Mayor orden actual de un tipo de proyecto */
    public function maxOrden(int $tipoProyectoId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(orden), 0) FROM etapas WHERE tipo_proyecto_id = ?"
        );
        $stmt->execute([$tipoProyectoId]);
        return (int) $stmt->fetchColumn();
    }
}
