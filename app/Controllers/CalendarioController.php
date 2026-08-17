<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Calendario;
use App\Models\Etapa;
use App\Models\Proyecto;

/**
 * Calendario de eventos (entregas, revisiones, correcciones, límites).
 * Gestión (crear/estado/eliminar): admin o docente tutor del proyecto.
 * Lectura: admin, tutor y estudiante dueño del proyecto.
 */
class CalendarioController extends Controller
{
    /** Agenda de eventos visibles para el rol */
    public function index(): void
    {
        $rol = Auth::role();
        $usuarioId = Auth::id();

        (new Calendario())->marcarVencidos();
        $eventos = (new Calendario())->visiblesPorRol($rol, $usuarioId);

        $proyectosGestionables = $this->proyectosGestionables();
        $etapasPorTipo = $this->etapasPorTipo();

        $this->view('calendario/index', [
            'title' => 'Calendario',
            'eventos' => $eventos,
            'proyectosGestionables' => $proyectosGestionables,
            'etapasPorTipo' => $etapasPorTipo,
            'puedeGestionar' => $rol === 'admin' || $rol === 'docente',
        ]);
    }

    /** Crea un evento (admin o tutor) */
    public function guardar(): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('calendario');
        }

        [$proyectoId, $etapaId] = $this->parseEtapa((string) Request::post('proyecto_etapa', ''));
        if ($proyectoId <= 0) {
            flash('error', 'Selecciona el proyecto del evento.');
            redirect_to('calendario');
        }
        if (!$this->puedeGestionarProyecto($proyectoId)) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }

        $titulo = trim((string) Request::post('titulo', ''));
        $descripcion = trim((string) Request::post('descripcion', ''));
        $fechaEvento = trim((string) Request::post('fecha_evento', ''));
        $tipo = (string) Request::post('tipo', 'otro');

        if ($titulo === '' || $fechaEvento === '') {
            flash('error', 'El título y la fecha del evento son obligatorios.');
            redirect_to('calendario');
        }
        if (!in_array($tipo, ['entrega', 'revision', 'correccion', 'limite', 'otro'], true)) {
            $tipo = 'otro';
        }
        $fecha = strtotime($fechaEvento);
        if (!$fecha) {
            flash('error', 'Fecha del evento inválida.');
            redirect_to('calendario');
        }

        (new Calendario())->create([
            'proyecto_id' => $proyectoId,
            'etapa_id' => $etapaId > 0 ? $etapaId : null,
            'usuario_id' => Auth::id(),
            'titulo' => $titulo,
            'descripcion' => $descripcion ?: null,
            'fecha_evento' => date('Y-m-d H:i:s', $fecha),
            'tipo' => $tipo,
            'estado' => 'pendiente',
        ]);

        $this->registrarHistorial($proyectoId, 'Nuevo evento', "Evento '{$titulo}' registrado en el calendario");
        $inv = (new Proyecto())->involucrados($proyectoId);
        notificar([$inv['estudiante_usuario_id'], $inv['tutor_usuario_id']], 'Nuevo evento', "Evento \"{$titulo}\" agregado al calendario.", $proyectoId, 'calendario', Auth::id());
        flash('success', 'Evento registrado en el calendario.');
        redirect_to('calendario');
    }

    /** Cambia el estado de un evento (admin o tutor) */
    public function estado(int $id): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('calendario');
        }
        $evento = (new Calendario())->find($id);
        if (!$evento) {
            flash('error', 'Evento no encontrado.');
            redirect_to('calendario');
        }
        if (!$this->puedeGestionarProyecto((int) $evento['proyecto_id'])) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }

        $estado = (string) Request::post('estado', '');
        $permitidos = ['pendiente', 'completado', 'vencido'];
        if (!in_array($estado, $permitidos, true)) {
            flash('error', 'Estado inválido.');
            redirect_to('calendario');
        }

        (new Calendario())->update($id, ['estado' => $estado]);
        flash('success', 'Evento actualizado.');
        redirect_to('calendario');
    }

    /** Elimina un evento (admin o tutor) */
    public function eliminar(int $id): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('calendario');
        }
        $evento = (new Calendario())->find($id);
        if (!$evento) {
            flash('error', 'Evento no encontrado.');
            redirect_to('calendario');
        }
        if (!$this->puedeGestionarProyecto((int) $evento['proyecto_id'])) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }

        (new Calendario())->delete($id);
        flash('success', 'Evento eliminado.');
        redirect_to('calendario');
    }

    /** ¿Puede gestionar eventos de este proyecto? */
    private function puedeGestionarProyecto(int $proyectoId): bool
    {
        if ($proyectoId <= 0) {
            return false;
        }
        $rol = Auth::role();
        if ($rol === 'admin') {
            return true;
        }
        if ($rol === 'docente') {
            return (new Proyecto())->puedeVer($proyectoId, $rol, Auth::id());
        }
        return false;
    }

    /** Proyectos sobre los que el usuario puede crear eventos */
    private function proyectosGestionables(): array
    {
        $db = Database::getConnection();
        if (Auth::role() === 'admin') {
            return $db->query(
                "SELECT id, codigo, nombre, tipo_proyecto_id FROM proyectos ORDER BY codigo"
            )->fetchAll();
        }
        $stmt = $db->prepare(
            "SELECT p.id, p.codigo, p.nombre, p.tipo_proyecto_id
             FROM proyectos p
             INNER JOIN asignaciones a ON a.proyecto_id = p.id AND a.estado = 'activa'
             INNER JOIN docentes d ON d.id = a.docente_id
             WHERE d.usuario_id = ?
             ORDER BY p.codigo"
        );
        $stmt->execute([Auth::id()]);
        return $stmt->fetchAll();
    }

    /** Etapas agrupadas por tipo de proyecto */
    private function etapasPorTipo(): array
    {
        $rows = (new Etapa())->query(
            "SELECT id, tipo_proyecto_id, nombre FROM etapas ORDER BY tipo_proyecto_id, orden"
        );
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['tipo_proyecto_id']][] = $r;
        }
        return $map;
    }

    /** Parsea el valor compuesto "proyecto:etapa" */
    private function parseEtapa(string $value): array
    {
        $parts = explode(':', $value);
        $proyectoId = (int) ($parts[0] ?? 0);
        $etapaId = (int) ($parts[1] ?? 0);
        return [$proyectoId, $etapaId];
    }

    private function registrarHistorial(int $proyectoId, string $accion, string $descripcion): void
    {
        $stmt = Database::getConnection()->prepare(
            "INSERT INTO historial_acciones (usuario_id, accion, descripcion, proyecto_id) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([Auth::id(), $accion, $descripcion, $proyectoId]);
    }
}
