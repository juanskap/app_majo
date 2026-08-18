<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Calendario;

class DashboardController extends Controller
{
    /** Redirige a la sección inicial según el rol */
    public function index(): void
    {
        $role = Auth::role();

        $stats = match ($role) {
            'admin' => $this->adminStats(),
            'docente' => $this->docenteStats(),
            'estudiante' => $this->estudianteStats(),
            default => [],
        };

        $eventos = (new Calendario())->proximos($role, Auth::id(), 5);

        $this->view('dashboard/index', [
            'title' => 'Panel principal',
            'stats' => $stats,
            'eventos' => $eventos,
        ]);
    }

    private function adminStats(): array
    {
        $db = Database::getConnection();
        $q = fn (string $sql) => (int) $db->query($sql)->fetchColumn();

        $stats = [
            'total_estudiantes' => $q("SELECT COUNT(*) FROM estudiantes"),
            'total_docentes' => $q("SELECT COUNT(*) FROM docentes"),
            'total_proyectos' => $q("SELECT COUNT(*) FROM proyectos"),
            'proyectos_titulacion' => $q("SELECT COUNT(*) FROM proyectos p JOIN tipos_proyecto t ON t.id = p.tipo_proyecto_id WHERE t.nombre = 'Titulación'"),
            'proyectos_vinculacion' => $q("SELECT COUNT(*) FROM proyectos p JOIN tipos_proyecto t ON t.id = p.tipo_proyecto_id WHERE t.nombre = 'Vinculación'"),
            'proyectos_pis' => $q("SELECT COUNT(*) FROM proyectos p JOIN tipos_proyecto t ON t.id = p.tipo_proyecto_id WHERE t.nombre = 'PIS'"),
            'proyectos_en_revision' => $q("SELECT COUNT(*) FROM proyectos WHERE estado IN ('enviado','en_revision','reenviado')"),
            'proyectos_con_observaciones' => $q("SELECT COUNT(*) FROM proyectos WHERE estado IN ('con_observaciones','en_correccion')"),
            'proyectos_aprobados' => $q("SELECT COUNT(*) FROM proyectos WHERE estado = 'aprobado'"),
            'proyectos_vencidos' => $q("SELECT COUNT(*) FROM proyectos WHERE estado = 'vencido'"),
            'proyectos_finalizados' => $q("SELECT COUNT(*) FROM proyectos WHERE estado = 'finalizado'"),
        ];

        $stats['proyectos_recientes'] = $db->query(
            "SELECT p.id, p.codigo, p.nombre, p.estado, p.porcentaje_avance, p.ultima_actualizacion,
                    CONCAT(ue.nombres, ' ', ue.apellidos) AS estudiante,
                    es.nombre AS etapa_actual
             FROM proyectos p
             INNER JOIN estudiantes e ON e.id = p.estudiante_id
             INNER JOIN usuarios ue ON ue.id = e.usuario_id
             LEFT JOIN etapas es ON es.id = p.etapa_actual_id
             ORDER BY p.ultima_actualizacion DESC LIMIT 5"
        )->fetchAll();

        return $stats;
    }

    private function docenteStats(): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT a.proyecto_id)
             FROM asignaciones a
             JOIN docentes d ON d.id = a.docente_id
             WHERE d.usuario_id = ? AND a.estado = 'activa'"
        );
        $stmt->execute([Auth::id()]);

        $stats = [
            'proyectos_activos' => (int) $stmt->fetchColumn(),
        ];

        // Documentos por revisar en mis tutorías
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT d.id)
             FROM documentos d
             INNER JOIN proyectos p ON p.id = d.proyecto_id
             INNER JOIN asignaciones a ON a.proyecto_id = p.id AND a.estado = 'activa'
             INNER JOIN docentes doc ON doc.id = a.docente_id
             WHERE doc.usuario_id = ? AND d.tipo = 'trabajo'
               AND d.estado IN ('enviado', 'en_revision', 'en_correccion')"
        );
        $stmt->execute([Auth::id()]);
        $stats['docs_pendientes'] = (int) $stmt->fetchColumn();

        // Observaciones activas en mis tutorías
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT o.id)
             FROM observaciones o
             INNER JOIN proyectos p ON p.id = o.proyecto_id
             INNER JOIN asignaciones a ON a.proyecto_id = p.id AND a.estado = 'activa'
             INNER JOIN docentes doc ON doc.id = a.docente_id
             WHERE doc.usuario_id = ? AND o.estado IN ('pendiente', 'en_correccion')"
        );
        $stmt->execute([Auth::id()]);
        $stats['obs_pendientes'] = (int) $stmt->fetchColumn();

        // Lista de proyectos asignados
        $stmt = $db->prepare(
            "SELECT p.id, p.codigo, p.nombre, p.estado, p.porcentaje_avance, p.fecha_limite,
                    CONCAT(ue.nombres, ' ', ue.apellidos) AS estudiante,
                    es.nombre AS etapa_actual
             FROM proyectos p
             INNER JOIN estudiantes e ON e.id = p.estudiante_id
             INNER JOIN usuarios ue ON ue.id = e.usuario_id
             INNER JOIN asignaciones a ON a.proyecto_id = p.id AND a.estado = 'activa'
             INNER JOIN docentes doc ON doc.id = a.docente_id
             LEFT JOIN etapas es ON es.id = p.etapa_actual_id
             WHERE doc.usuario_id = ?
             ORDER BY p.ultima_actualizacion DESC"
        );
        $stmt->execute([Auth::id()]);
        $stats['mis_proyectos'] = $stmt->fetchAll();

        return $stats;
    }

    private function estudianteStats(): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM proyectos p
             JOIN estudiantes e ON e.id = p.estudiante_id
             WHERE e.usuario_id = ?"
        );
        $stmt->execute([Auth::id()]);
        $total = (int) $stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT p.*,
                    CONCAT(ud.nombres, ' ', ud.apellidos) AS tutor_nombre,
                    es.nombre AS etapa_actual_nombre, t.nombre AS tipo_proyecto
             FROM proyectos p
             INNER JOIN estudiantes e ON e.id = p.estudiante_id
             LEFT JOIN asignaciones a ON a.proyecto_id = p.id AND a.estado = 'activa'
             LEFT JOIN docentes d ON d.id = a.docente_id
             LEFT JOIN usuarios ud ON ud.id = d.usuario_id
             LEFT JOIN etapas es ON es.id = p.etapa_actual_id
             LEFT JOIN tipos_proyecto t ON t.id = p.tipo_proyecto_id
             WHERE e.usuario_id = ?
             ORDER BY p.ultima_actualizacion DESC"
        );
        $stmt->execute([Auth::id()]);
        $misProyectos = $stmt->fetchAll();

        return [
            'mis_proyectos' => $total,
            'proyectos' => $misProyectos,
        ];
    }
}