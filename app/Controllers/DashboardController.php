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

        return [
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

        return [
            'proyectos_activos' => (int) $stmt->fetchColumn(),
        ];
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

        return [
            'mis_proyectos' => (int) $stmt->fetchColumn(),
        ];
    }
}
