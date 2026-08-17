<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Middlewares\AuthMiddleware;
use App\Models\Etapa;
use App\Models\TipoProyecto;

/**
 * Gestión de tipos de proyecto y sus etapas.
 * Solo acceso del rol administrador.
 */
class TipoProyectoController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        AuthMiddleware::requireRole('admin');
    }

    /** Lista los tipos de proyecto */
    public function index(): void
    {
        $tipos = (new TipoProyecto())->allWithCount();

        $this->view('tipos-proyecto/index', [
            'title' => 'Tipos de proyecto',
            'tipos' => $tipos,
        ]);
    }

    /** Muestra el formulario de creación */
    public function nuevo(): void
    {
        $this->view('tipos-proyecto/nuevo', ['title' => 'Nuevo tipo de proyecto']);
    }

    /** Procesa la creación de un tipo de proyecto */
    public function crear(): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('tipos-proyecto');
        }

        $nombre = trim((string) Request::post('nombre', ''));
        $descripcion = trim((string) Request::post('descripcion', ''));

        if ($nombre === '') {
            flash('error', 'El nombre del tipo de proyecto es obligatorio.');
            redirect_to('tipos-proyecto/nuevo');
        }

        $model = new TipoProyecto();
        if ($model->firstWhere('nombre', $nombre)) {
            flash('error', 'Ya existe un tipo de proyecto con ese nombre.');
            redirect_to('tipos-proyecto/nuevo');
        }

        $id = $model->create([
            'nombre' => $nombre,
            'descripcion' => $descripcion ?: null,
            'activo' => 1,
        ]);

        $this->registrarHistorial('Creación de tipo de proyecto', "Tipo '{$nombre}' creado");
        flash('success', 'Tipo de proyecto creado. Ahora agrega sus etapas.');
        redirect_to('tipos-proyecto/etapas/' . $id);
    }

    /** Muestra el formulario de edición */
    public function editar(int $id): void
    {
        $tipo = (new TipoProyecto())->find($id);
        if (!$tipo) {
            flash('error', 'Tipo de proyecto no encontrado.');
            redirect_to('tipos-proyecto');
        }

        $this->view('tipos-proyecto/editar', [
            'title' => 'Editar tipo de proyecto',
            'tipo' => $tipo,
        ]);
    }

    /** Procesa la actualización */
    public function actualizar(): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('tipos-proyecto');
        }

        $id = (int) Request::post('id', 0);
        $nombre = trim((string) Request::post('nombre', ''));
        $descripcion = trim((string) Request::post('descripcion', ''));

        if ($nombre === '') {
            flash('error', 'El nombre es obligatorio.');
            redirect_to('tipos-proyecto/editar/' . $id);
        }

        (new TipoProyecto())->update($id, [
            'nombre' => $nombre,
            'descripcion' => $descripcion ?: null,
        ]);

        $this->registrarHistorial('Actualización de tipo de proyecto', "Tipo '{$nombre}' actualizado");
        flash('success', 'Tipo de proyecto actualizado.');
        redirect_to('tipos-proyecto');
    }

    /** Activa o desactiva un tipo de proyecto */
    public function estado(int $id): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('tipos-proyecto');
        }

        $model = new TipoProyecto();
        $tipo = $model->find($id);
        if (!$tipo) {
            flash('error', 'Tipo de proyecto no encontrado.');
            redirect_to('tipos-proyecto');
        }

        $nuevo = $tipo['activo'] ? 0 : 1;
        $model->update($id, ['activo' => $nuevo]);

        $this->registrarHistorial('Cambio de estado de tipo de proyecto', "Tipo '{$tipo['nombre']}' → " . ($nuevo ? 'activo' : 'inactivo'));
        flash('success', 'Tipo de proyecto ' . ($nuevo ? 'activado' : 'desactivado') . '.');
        redirect_to('tipos-proyecto');
    }

    /** Muestra la gestión de etapas de un tipo de proyecto */
    public function etapas(int $tipoId): void
    {
        $tipo = (new TipoProyecto())->find($tipoId);
        if (!$tipo) {
            flash('error', 'Tipo de proyecto no encontrado.');
            redirect_to('tipos-proyecto');
        }

        $etapas = (new Etapa())->byTipo($tipoId);

        $this->view('tipos-proyecto/etapas', [
            'title' => 'Etapas — ' . $tipo['nombre'],
            'tipo' => $tipo,
            'etapas' => $etapas,
        ]);
    }

    /** Procesa la creación de una etapa */
    public function crearEtapa(int $tipoId): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('tipos-proyecto/etapas/' . $tipoId);
        }

        $nombre = trim((string) Request::post('nombre', ''));
        if ($nombre === '') {
            flash('error', 'El nombre de la etapa es obligatorio.');
            redirect_to('tipos-proyecto/etapas/' . $tipoId);
        }

        $etapa = new Etapa();
        $orden = $etapa->maxOrden($tipoId) + 1;

        $etapa->create([
            'tipo_proyecto_id' => $tipoId,
            'nombre' => $nombre,
            'orden' => $orden,
        ]);

        $this->registrarHistorial('Creación de etapa', "Etapa '{$nombre}' agregada");
        flash('success', 'Etapa agregada correctamente.');
        redirect_to('tipos-proyecto/etapas/' . $tipoId);
    }

    /** Actualiza una etapa */
    public function actualizarEtapa(int $tipoId): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('tipos-proyecto/etapas/' . $tipoId);
        }

        $etapaId = (int) Request::post('etapa_id', 0);
        $nombre = trim((string) Request::post('nombre', ''));

        if ($nombre === '') {
            flash('error', 'El nombre de la etapa es obligatorio.');
            redirect_to('tipos-proyecto/etapas/' . $tipoId);
        }

        (new Etapa())->update($etapaId, ['nombre' => $nombre]);

        $this->registrarHistorial('Actualización de etapa', "Etapa '{$nombre}' actualizada");
        flash('success', 'Etapa actualizada.');
        redirect_to('tipos-proyecto/etapas/' . $tipoId);
    }

    /** Elimina una etapa */
    public function eliminarEtapa(int $tipoId): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('tipos-proyecto/etapas/' . $tipoId);
        }

        $etapaId = (int) Request::post('etapa_id', 0);
        $etapa = (new Etapa())->find($etapaId);

        if (!$etapa) {
            flash('error', 'Etapa no encontrada.');
            redirect_to('tipos-proyecto/etapas/' . $tipoId);
        }

        (new Etapa())->delete($etapaId);
        $this->reordenar($tipoId);

        $this->registrarHistorial('Eliminación de etapa', "Etapa '{$etapa['nombre']}' eliminada");
        flash('success', 'Etapa eliminada.');
        redirect_to('tipos-proyecto/etapas/' . $tipoId);
    }

    /** Mueve una etapa hacia arriba o abajo */
    public function moverEtapa(int $tipoId): void
    {
        if (!Request::csrfValidate()) {
            flash('error', 'La sesión expiró, inténtalo de nuevo.');
            redirect_to('tipos-proyecto/etapas/' . $tipoId);
        }

        $etapaId = (int) Request::post('etapa_id', 0);
        $direccion = Request::post('direccion', 'abajo');

        $etapa = (new Etapa())->find($etapaId);
        if (!$etapa) {
            flash('error', 'Etapa no encontrada.');
            redirect_to('tipos-proyecto/etapas/' . $tipoId);
        }

        $db = Database::getConnection();

        if ($direccion === 'arriba') {
            $stmt = $db->prepare("SELECT * FROM etapas WHERE tipo_proyecto_id = ? AND orden < ? ORDER BY orden DESC LIMIT 1");
        } else {
            $stmt = $db->prepare("SELECT * FROM etapas WHERE tipo_proyecto_id = ? AND orden > ? ORDER BY orden ASC LIMIT 1");
        }
        $stmt->execute([$tipoId, $etapa['orden']]);
        $vecino = $stmt->fetch();

        if ($vecino) {
            $db->prepare("UPDATE etapas SET orden = ? WHERE id = ?")->execute([$vecino['orden'], $etapa['id']]);
            $db->prepare("UPDATE etapas SET orden = ? WHERE id = ?")->execute([$etapa['orden'], $vecino['id']]);
        }

        redirect_to('tipos-proyecto/etapas/' . $tipoId);
    }

    private function reordenar(int $tipoId): void
    {
        $etapas = (new Etapa())->byTipo($tipoId);
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE etapas SET orden = ? WHERE id = ?");
        foreach ($etapas as $i => $etapa) {
            $stmt->execute([$i + 1, $etapa['id']]);
        }
    }

    private function registrarHistorial(string $accion, string $descripcion): void
    {
        $stmt = Database::getConnection()->prepare(
            "INSERT INTO historial_acciones (usuario_id, accion, descripcion) VALUES (?, ?, ?)"
        );
        $stmt->execute([\App\Core\Auth::id(), $accion, $descripcion]);
    }
}
