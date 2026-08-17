<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Actividad;
use App\Models\Etapa;
use App\Models\Plazo;
use App\Models\Proyecto;

/**
 * Plan de actividades y plazos de un proyecto.
 * Gestión (crear/estado/eliminar): admin o docente tutor asignado.
 * Lectura: admin, tutor y estudiante dueño del proyecto.
 */
class PlanController extends Controller
{
    /** Muestra el plan de actividades y plazos de un proyecto */
    public function index(int $proyectoId): void
    {
        $proyecto = (new Proyecto())->detail($proyectoId);
        if (!$proyecto) {
            flash('error', 'Proyecto no encontrado.');
            redirect_to('proyectos');
        }
        if (!(new Proyecto())->puedeVer($proyectoId, Auth::role(), Auth::id())) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }

        $actividadModel = new Actividad();
        $plazoModel = new Plazo();
        $actividadModel->marcarVencidas($proyectoId);
        $plazoModel->marcarVencidos($proyectoId);

        $this->view('proyectos/plan', [
            'title' => 'Plan de actividades — ' . $proyecto['codigo'],
            'proyecto' => $proyecto,
            'actividades' => $actividadModel->porProyecto($proyectoId),
            'plazos' => $plazoModel->porProyecto($proyectoId),
            'puedeGestionar' => $this->puedeGestionar($proyectoId),
            'etapas' => (new Etapa())->query(
                "SELECT id, nombre FROM etapas WHERE tipo_proyecto_id = ? ORDER BY orden",
                [(int) $proyecto['tipo_proyecto_id']]
            ),
            'responsables' => $this->responsablesDeProyecto($proyecto),
        ]);
    }

    /** Crea una actividad (admin o tutor) */
    public function actividadGuardar(int $proyectoId): void
    {
        if (!$this->puedeGestionar($proyectoId)) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('plan/' . $proyectoId);
        }

        $descripcion = trim((string) Request::post('descripcion', ''));
        $tipo = (string) Request::post('tipo', 'entrega');
        $etapaId = (int) Request::post('etapa_id', 0);
        $responsableId = (int) Request::post('responsable_id', 0);
        $fechaInicio = trim((string) Request::post('fecha_inicio', ''));
        $fechaLimite = trim((string) Request::post('fecha_limite', ''));

        if ($descripcion === '' || $fechaInicio === '' || $fechaLimite === '' || $responsableId <= 0) {
            flash('error', 'Completa descripción, fechas y responsable.');
            redirect_to('plan/' . $proyectoId);
        }
        if (!in_array($tipo, ['entrega', 'revision', 'correccion', 'aprobacion'], true)) {
            $tipo = 'entrega';
        }
        if ($fechaLimite < $fechaInicio) {
            flash('error', 'La fecha límite no puede ser anterior a la fecha de inicio.');
            redirect_to('plan/' . $proyectoId);
        }

        (new Actividad())->create([
            'proyecto_id' => $proyectoId,
            'etapa_id' => $etapaId > 0 ? $etapaId : null,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'responsable_id' => $responsableId,
            'fecha_inicio' => $fechaInicio,
            'fecha_limite' => $fechaLimite,
            'estado' => 'pendiente',
        ]);

        $this->registrarHistorial($proyectoId, 'Nueva actividad', "Actividad '{$descripcion}' registrada");
        $inv = (new Proyecto())->involucrados($proyectoId);
        notificar([$inv['estudiante_usuario_id'], $inv['tutor_usuario_id']], 'Nueva actividad', "Actividad \"{$descripcion}\" registrada en el plan.", $proyectoId, 'actividad', Auth::id());
        flash('success', 'Actividad registrada.');
        redirect_to('plan/' . $proyectoId);
    }

    /** Crea un plazo (admin o tutor) */
    public function plazoGuardar(int $proyectoId): void
    {
        if (!$this->puedeGestionar($proyectoId)) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('plan/' . $proyectoId);
        }

        $descripcion = trim((string) Request::post('descripcion', ''));
        $actividadId = (int) Request::post('actividad_id', 0);
        $fechaInicio = trim((string) Request::post('fecha_inicio', ''));
        $fechaLimite = trim((string) Request::post('fecha_limite', ''));

        if ($descripcion === '' || $fechaInicio === '' || $fechaLimite === '') {
            flash('error', 'Completa descripción y fechas.');
            redirect_to('plan/' . $proyectoId);
        }
        if ($fechaLimite < $fechaInicio) {
            flash('error', 'La fecha límite no puede ser anterior a la fecha de inicio.');
            redirect_to('plan/' . $proyectoId);
        }

        (new Plazo())->create([
            'proyecto_id' => $proyectoId,
            'actividad_id' => $actividadId > 0 ? $actividadId : null,
            'descripcion' => $descripcion,
            'fecha_inicio' => $fechaInicio,
            'fecha_limite' => $fechaLimite,
            'estado' => 'activo',
        ]);

        $this->registrarHistorial($proyectoId, 'Nuevo plazo', "Plazo '{$descripcion}' registrado");
        $inv = (new Proyecto())->involucrados($proyectoId);
        notificar([$inv['estudiante_usuario_id'], $inv['tutor_usuario_id']], 'Nuevo plazo', "Plazo \"{$descripcion}\" registrado en el plan.", $proyectoId, 'actividad', Auth::id());
        flash('success', 'Plazo registrado.');
        redirect_to('plan/' . $proyectoId);
    }

    /** Cambia el estado de una actividad (admin o tutor) */
    public function actividadEstado(int $id): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('proyectos');
        }
        $actividad = (new Actividad())->find($id);
        if (!$actividad) {
            flash('error', 'Actividad no encontrada.');
            redirect_to('proyectos');
        }
        if (!$this->puedeGestionar((int) $actividad['proyecto_id'])) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }

        $estado = (string) Request::post('estado', '');
        $permitidos = ['pendiente', 'en_curso', 'completada', 'vencida'];
        if (!in_array($estado, $permitidos, true)) {
            flash('error', 'Estado inválido.');
            redirect_to('plan/' . $actividad['proyecto_id']);
        }

        (new Actividad())->update($id, ['estado' => $estado]);
        flash('success', 'Actividad actualizada.');
        redirect_to('plan/' . $actividad['proyecto_id']);
    }

    /** Cambia el estado de un plazo (admin o tutor) */
    public function plazoEstado(int $id): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('proyectos');
        }
        $plazo = (new Plazo())->find($id);
        if (!$plazo) {
            flash('error', 'Plazo no encontrado.');
            redirect_to('proyectos');
        }
        if (!$this->puedeGestionar((int) $plazo['proyecto_id'])) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }

        $estado = (string) Request::post('estado', '');
        $permitidos = ['activo', 'completado', 'vencido'];
        if (!in_array($estado, $permitidos, true)) {
            flash('error', 'Estado inválido.');
            redirect_to('plan/' . $plazo['proyecto_id']);
        }

        (new Plazo())->update($id, ['estado' => $estado]);
        flash('success', 'Plazo actualizado.');
        redirect_to('plan/' . $plazo['proyecto_id']);
    }

    /** Elimina una actividad (admin o tutor) */
    public function actividadEliminar(int $id): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('proyectos');
        }
        $actividad = (new Actividad())->find($id);
        if (!$actividad) {
            flash('error', 'Actividad no encontrada.');
            redirect_to('proyectos');
        }
        if (!$this->puedeGestionar((int) $actividad['proyecto_id'])) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }

        (new Actividad())->delete($id);
        flash('success', 'Actividad eliminada.');
        redirect_to('plan/' . $actividad['proyecto_id']);
    }

    /** Elimina un plazo (admin o tutor) */
    public function plazoEliminar(int $id): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('proyectos');
        }
        $plazo = (new Plazo())->find($id);
        if (!$plazo) {
            flash('error', 'Plazo no encontrado.');
            redirect_to('proyectos');
        }
        if (!$this->puedeGestionar((int) $plazo['proyecto_id'])) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }

        (new Plazo())->delete($id);
        flash('success', 'Plazo eliminado.');
        redirect_to('plan/' . $plazo['proyecto_id']);
    }

    /** ¿Puede este usuario gestionar (crear/editar) el plan? */
    private function puedeGestionar(int $proyectoId): bool
    {
        if (Auth::role() === 'admin') {
            return true;
        }
        return Auth::role() === 'docente' &&
            (new Proyecto())->puedeVer($proyectoId, Auth::role(), Auth::id());
    }

    /** Opciones de responsable: el estudiante del proyecto y su tutor */
    private function responsablesDeProyecto(array $proyecto): array
    {
        $db = Database::getConnection();
        $opciones = [];

        $stmt = $db->prepare(
            "SELECT u.id, u.nombres, u.apellidos FROM estudiantes e
             INNER JOIN usuarios u ON u.id = e.usuario_id WHERE e.id = ?"
        );
        $stmt->execute([(int) $proyecto['estudiante_record_id']]);
        if ($row = $stmt->fetch()) {
            $opciones[$row['id']] = $row['nombres'] . ' ' . $row['apellidos'] . ' (estudiante)';
        }

        if ($proyecto['docente_record_id'] !== null) {
            $stmt = $db->prepare(
                "SELECT u.id, u.nombres, u.apellidos FROM docentes d
                 INNER JOIN usuarios u ON u.id = d.usuario_id WHERE d.id = ?"
            );
            $stmt->execute([(int) $proyecto['docente_record_id']]);
            if ($row = $stmt->fetch()) {
                $opciones[$row['id']] = $row['nombres'] . ' ' . $row['apellidos'] . ' (tutor)';
            }
        }

        return $opciones;
    }

    private function registrarHistorial(int $proyectoId, string $accion, string $descripcion): void
    {
        $stmt = Database::getConnection()->prepare(
            "INSERT INTO historial_acciones (usuario_id, accion, descripcion, proyecto_id) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([Auth::id(), $accion, $descripcion, $proyectoId]);
    }
}
