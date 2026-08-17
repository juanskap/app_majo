<?php

namespace App\Models;

use App\Core\Model;

class Estudiante extends Model
{
    protected string $table = 'estudiantes';

    /** Registro completo del estudiante con datos del usuario */
    public function findFull(int $id): ?array
    {
        $sql = "SELECT e.id, e.codigo, e.carrera, u.id AS usuario_id,
                       u.nombres, u.apellidos, u.email, u.telefono, u.estado
                FROM estudiantes e
                INNER JOIN usuarios u ON u.id = e.usuario_id
                WHERE e.id = ?
                LIMIT 1";
        $rows = $this->query($sql, [$id]);
        return $rows[0] ?? null;
    }

    /** Lista todos los estudiantes con sus datos */
    public function allFull(): array
    {
        $sql = "SELECT e.id, e.codigo, e.carrera, u.id AS usuario_id,
                       u.nombres, u.apellidos, u.email, u.telefono, u.estado, u.creado_en
                FROM estudiantes e
                INNER JOIN usuarios u ON u.id = e.usuario_id
                ORDER BY u.apellidos, u.nombres";
        return $this->query($sql);
    }
}
