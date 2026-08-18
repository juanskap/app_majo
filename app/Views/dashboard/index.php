<?php
/** @var array $stats */
/** @var array $eventos */
use App\Core\Auth;
$rol = Auth::role();
?>

<h1 class="text-2xl font-bold text-slate-900">Panel principal</h1>
<p class="text-slate-500 mb-6">Bienvenido al sistema <?= e(APP_NAME) ?>.</p>

<?php if ($rol === 'admin'): ?>
<!-- ===== Estadísticas ===== -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 animate-in">
    <div class="card p-5">
        <p class="text-sm text-slate-500">Estudiantes</p>
        <p class="text-3xl font-extrabold text-slate-900 mt-1"><?= (int) $stats['total_estudiantes'] ?></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Docentes</p>
        <p class="text-3xl font-extrabold text-slate-900 mt-1"><?= (int) $stats['total_docentes'] ?></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Proyectos</p>
        <p class="text-3xl font-extrabold text-slate-900 mt-1"><?= (int) $stats['total_proyectos'] ?></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Finalizados</p>
        <p class="text-3xl font-extrabold text-emerald-600 mt-1"><?= (int) $stats['proyectos_finalizados'] ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="card p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-900">Proyectos recientes</h3>
            <a href="<?= url('proyectos') ?>" class="text-sm font-medium text-indigo-600 hover:underline">Ver todos →</a>
        </div>
        <?php if (!$stats['proyectos_recientes']): ?>
        <p class="text-sm text-slate-500 py-6 text-center">Aún no hay proyectos registrados.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr><th>Código</th><th>Proyecto</th><th>Estudiante</th><th>Avance</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['proyectos_recientes'] as $p): ?>
                    <tr>
                        <td class="font-mono text-xs font-semibold text-indigo-600"><?= e($p['codigo']) ?></td>
                        <td><a href="<?= url('proyectos/ver/' . $p['id']) ?>" class="font-medium text-slate-900 hover:text-indigo-600 hover:underline"><?= e($p['nombre']) ?></a>
                            <span class="block text-xs text-slate-400"><?= e($p['etapa_actual'] ?? 'Sin etapa') ?></span></td>
                        <td class="text-slate-600"><?= e($p['estudiante']) ?></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full" style="width: <?= (float) $p['porcentaje_avance'] ?>%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-600"><?= (int) $p['porcentaje_avance'] ?>%</span>
                            </div>
                        </td>
                        <td><span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= e(estado_badge($p['estado'])) ?>"><?= e(ucfirst($p['estado'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="space-y-6">
        <div class="card p-6">
            <h3 class="font-bold mb-4">Proyectos por tipo</h3>
            <div class="space-y-4">
                <?php foreach ([
                    'Titulación' => 'proyectos_titulacion',
                    'Vinculación' => 'proyectos_vinculacion',
                    'PIS' => 'proyectos_pis',
                ] as $label => $key): ?>
                <?php $max = max(1, (int) $stats['total_proyectos']); ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-600"><?= e($label) ?></span>
                        <span class="font-semibold text-slate-900"><?= (int) $stats[$key] ?></span>
                    </div>
                    <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: <?= round((int) $stats[$key] / $max * 100) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="font-bold mb-4">Estado de proyectos</h3>
            <div class="space-y-2">
                <?php foreach ([
                    'En revisión' => 'proyectos_en_revision',
                    'Con observaciones' => 'proyectos_con_observaciones',
                    'Aprobados' => 'proyectos_aprobados',
                    'Vencidos' => 'proyectos_vencidos',
                ] as $label => $key): ?>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600"><?= e($label) ?></span>
                    <span class="font-semibold text-slate-900"><?= (int) $stats[$key] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php elseif ($rol === 'docente'): ?>
<!-- ===== Docente ===== -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 animate-in">
    <div class="card p-5">
        <p class="text-sm text-slate-500">Proyectos asignados</p>
        <p class="text-3xl font-extrabold text-slate-900 mt-1"><?= (int) $stats['proyectos_activos'] ?></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Documentos por revisar</p>
        <p class="text-3xl font-extrabold text-amber-600 mt-1"><?= (int) $stats['docs_pendientes'] ?></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Observaciones activas</p>
        <p class="text-3xl font-extrabold text-indigo-600 mt-1"><?= (int) $stats['obs_pendientes'] ?></p>
    </div>
</div>

<div class="card p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-900">Mis tutorías</h3>
        <a href="<?= url('mis-proyectos') ?>" class="text-sm font-medium text-indigo-600 hover:underline">Ver todos →</a>
    </div>
    <?php if (!$stats['mis_proyectos']): ?>
    <p class="text-sm text-slate-500 py-6 text-center">No tienes proyectos asignados todavía.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>Código</th><th>Proyecto</th><th>Estudiante</th><th>Avance</th><th>Estado</th></tr></thead>
            <tbody>
                <?php foreach ($stats['mis_proyectos'] as $p): ?>
                <tr>
                    <td class="font-mono text-xs font-semibold text-indigo-600"><?= e($p['codigo']) ?></td>
                    <td><a href="<?= url('proyectos/ver/' . $p['id']) ?>" class="font-medium text-slate-900 hover:text-indigo-600 hover:underline"><?= e($p['nombre']) ?></a>
                        <span class="block text-xs text-slate-400"><?= e($p['etapa_actual'] ?? 'Sin etapa') ?></span></td>
                    <td class="text-slate-600"><?= e($p['estudiante']) ?></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-20 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: <?= (float) $p['porcentaje_avance'] ?>%"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-600"><?= (int) $p['porcentaje_avance'] ?>%</span>
                        </div>
                    </td>
                    <td><span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= e(estado_badge($p['estado'])) ?>"><?= e(ucfirst($p['estado'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- ===== Estudiante ===== -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-in">
    <div class="card p-5">
        <p class="text-sm text-slate-500">Mis proyectos</p>
        <p class="text-3xl font-extrabold text-slate-900 mt-1"><?= (int) $stats['mis_proyectos'] ?></p>
    </div>
    <div class="card p-5 col-span-1 sm:col-span-2">
        <p class="text-sm text-slate-500">Siguiente paso</p>
        <p class="text-slate-700 mt-1 text-sm font-medium">
            <?php if ($stats['mis_proyectos'] > 0): ?>
            Revisa el estado de tus proyectos y carga los documentos de cada etapa.
            <?php else: ?>
            Registra tu primer proyecto para comenzar el seguimiento.
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if ($stats['proyectos']): ?>
<div class="card p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-900">Mis proyectos</h3>
        <a href="<?= url('proyectos') ?>" class="text-sm font-medium text-indigo-600 hover:underline">Ver todos →</a>
    </div>
    <div class="space-y-3">
        <?php foreach ($stats['proyectos'] as $p): ?>
        <a href="<?= url('proyectos/ver/' . $p['id']) ?>" class="block p-4 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40 transition group">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-semibold text-indigo-600"><?= e($p['codigo']) ?></span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= e(estado_badge($p['estado'])) ?>"><?= e(ucfirst($p['estado'])) ?></span>
                    </div>
                    <p class="font-semibold text-slate-900 group-hover:text-indigo-700"><?= e($p['nombre']) ?></p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <?= e($p['tipo_proyecto'] ?? '') ?> ·
                        <?php if ($p['tutor_nombre']): ?>Tutor: <?= e($p['tutor_nombre']) ?><?php else: ?><span class="text-amber-600 font-medium">Sin tutor asignado</span><?php endif; ?>
                        <?php if ($p['fecha_limite']): ?> · Vence: <?= e(date('d/m/Y', strtotime($p['fecha_limite']))) ?><?php endif; ?>
                    </p>
                </div>
                <div class="w-40">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-500"><?= e($p['etapa_actual_nombre'] ?? 'Inicio') ?></span>
                        <span class="font-semibold"><?= (int) $p['porcentaje_avance'] ?>%</span>
                    </div>
                    <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-500 rounded-full" style="width: <?= (float) $p['porcentaje_avance'] ?>%"></div>
                    </div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== Próximos eventos ===== -->
<div class="card p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-900">Próximos eventos</h3>
        <a href="<?= url('calendario') ?>" class="text-sm font-medium text-indigo-600 hover:underline">Ver calendario →</a>
    </div>
    <?php if (!$eventos): ?>
    <p class="text-sm text-slate-500 py-4 text-center">No hay eventos próximos.</p>
    <?php else: ?>
    <ol class="space-y-2">
        <?php foreach ($eventos as $ev): ?>
        <li class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 text-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex flex-col items-center justify-center shrink-0">
                    <span class="text-xs font-bold text-slate-900 leading-none"><?= e(date('d', strtotime($ev['fecha_evento']))) ?></span>
                    <span class="text-[9px] uppercase text-slate-400 font-semibold"><?= e(date('M', strtotime($ev['fecha_evento']))) ?></span>
                </div>
                <div>
                    <p class="font-medium text-slate-900"><?= e($ev['titulo']) ?></p>
                    <p class="text-xs text-slate-400"><?= e($ev['codigo']) ?> · <?= e(date('H:i', strtotime($ev['fecha_evento']))) ?> h</p>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= e(estado_badge($ev['estado'])) ?>"><?= e(ucfirst($ev['estado'])) ?></span>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
</div>