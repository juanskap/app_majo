<?php

namespace App\Models;

use App\Core\Model;

class Documento extends Model
{
    protected string $table = 'documentos';

    /** Documento actual de una etapa (tipo trabajo o final) */
    public function actualDeEtapa(int $proyectoId, int $etapaId, string $tipo = 'trabajo'): ?array
    {
        $rows = $this->query(
            "SELECT d.*, u.nombres, u.apellidos
             FROM documentos d
             INNER JOIN usuarios u ON u.id = d.subido_por
             WHERE d.proyecto_id = ? AND d.etapa_id = ? AND d.tipo = ?
             ORDER BY d.version DESC LIMIT 1",
            [$proyectoId, $etapaId, $tipo]
        );
        return $rows[0] ?? null;
    }

    /** Siguiente número de versión para una etapa */
    public function siguienteVersion(int $proyectoId, int $etapaId, string $tipo = 'trabajo'): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(version), 0) + 1 FROM documentos
             WHERE proyecto_id = ? AND etapa_id = ? AND tipo = ?"
        );
        $stmt->execute([$proyectoId, $etapaId, $tipo]);
        return (int) $stmt->fetchColumn();
    }

    /** Documento con sus observaciones (hilo completo) */
    public function conObservaciones(int $id): ?array
    {
        $rows = $this->query(
            "SELECT d.*, u.nombres, u.apellidos
             FROM documentos d
             INNER JOIN usuarios u ON u.id = d.subido_por
             WHERE d.id = ? LIMIT 1",
            [$id]
        );
        $doc = $rows[0] ?? null;
        if (!$doc) return null;

        $doc['observaciones'] = $this->query(
            "SELECT o.*, u.nombres, u.apellidos, r.nombre AS rol
             FROM observaciones o
             INNER JOIN usuarios u ON u.id = o.usuario_id
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE o.documento_id = ?
             ORDER BY o.id",
            [$id]
        );

        foreach ($doc['observaciones'] as &$obs) {
            $obs['respuestas'] = $this->query(
                "SELECT r.*, u.nombres, u.apellidos, rl.nombre AS rol
                 FROM respuestas_observaciones r
                 INNER JOIN usuarios u ON u.id = r.usuario_id
                 INNER JOIN roles rl ON rl.id = u.rol_id
                 WHERE r.observacion_id = ?
                 ORDER BY r.id",
                [$obs['id']]
            );
        }

        return $doc;
    }

    /** Observaciones pendientes de un proyecto (para el tutor) */
    public function pendientesDeProyecto(int $proyectoId): array
    {
        return $this->query(
            "SELECT o.*, d.nombre_original, d.version, et.nombre AS etapa
             FROM observaciones o
             INNER JOIN documentos d ON d.id = o.documento_id
             INNER JOIN etapas et ON et.id = d.etapa_id
             WHERE o.proyecto_id = ? AND o.estado IN ('pendiente', 'en_correccion')
             ORDER BY o.id",
            [$proyectoId]
        );
    }
}
