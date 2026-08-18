<?php
/** @var array $registros */
/** @var string $filtro */
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Historial de acciones</h1>
        <p class="text-slate-500 mt-0.5">Registro de las actividades realizadas en el sistema.</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="p-4 border-b border-slate-200">
        <form method="get" action="<?= url('historial') ?>" class="flex flex-wrap gap-3">
            <input type="text" name="q" value="<?= e($filtro) ?>" placeholder="Buscar por acción, descripción, usuario o correo…"
                   class="input !max-w-md">
            <button type="submit" class="btn-secondary">Buscar</button>
            <?php if ($filtro !== ''): ?>
            <a href="<?= url('historial') ?>" class="btn-secondary">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$registros): ?>
    <p class="text-center text-slate-500 py-12">No hay registros<?= $filtro !== '' ? ' que coincidan con la búsqueda' : '' ?>.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Descripción</th>
                    <th>Proyecto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $h): ?>
                <tr>
                    <td class="whitespace-nowrap text-slate-500"><?= e(format_date($h['creado_en'])) ?></td>
                    <td class="font-medium"><?= e($h['usuario']) ?><span class="block text-xs text-slate-400 font-normal"><?= e($h['email']) ?></span></td>
                    <td><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?= e($h['accion']) ?></span></td>
                    <td class="text-slate-600"><?= e($h['descripcion'] ?? '—') ?></td>
                    <td><?= $h['proyecto'] ? '<span class="font-mono text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">' . e($h['proyecto']) . '</span>' : '<span class="text-slate-300">—</span>' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>