<?php

namespace App\Models;

use App\Core\Model;

class Docente extends Model
{
    protected string $table = 'docentes';

    /** Registro completo del docente con datos del usuario */
    public function findFull(int $id): ?array
    {
        $sql = "SELECT d.id, d.titulo, d.especialidad, u.id AS usuario_id,
                       u.nombres, u.apellidos, u.email, u.telefono, u.estado
                FROM docentes d
                INNER JOIN usuarios u ON u.id = d.usuario_id
                WHERE d.id = ?
                LIMIT 1";
        $rows = $this->query($sql, [$id]);
        return $rows[0] ?? null;
    }

    /** Lista todos los docentes con sus datos */
    public function allFull(): array
    {
        $sql = "SELECT d.id, d.titulo, d.especialidad, u.id AS usuario_id,
                       u.nombres, u.apellidos, u.email, u.telefono, u.estado, u.creado_en
                FROM docentes d
                INNER JOIN usuarios u ON u.id = d.usuario_id
                ORDER BY u.apellidos, u.nombres";
        return $this->query($sql);
    }
}
