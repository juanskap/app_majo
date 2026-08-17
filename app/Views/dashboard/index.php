<?php
/** @var array $stats */
/** @var array $eventos */
use App\Core\Auth;
$rol = Auth::role();
?>

<h1 class="text-2xl font-bold text-gray-900 mb-1">Panel principal</h1>
<p class="text-gray-500 mb-6">Bienvenido al sistema <?= e(APP_NAME) ?>.</p>

<?php if ($rol === 'admin'): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Estudiantes</p>
        <p class="text-3xl font-bold"><?= (int) $stats['total_estudiantes'] ?></p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Docentes</p>
        <p class="text-3xl font-bold"><?= (int) $stats['total_docentes'] ?></p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Proyectos</p>
        <p class="text-3xl font-bold"><?= (int) $stats['total_proyectos'] ?></p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Finalizados</p>
        <p class="text-3xl font-bold text-emerald-600"><?= (int) $stats['proyectos_finalizados'] ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
    <div class="bg-white rounded-xl shadow p-5">
        <h3 class="font-semibold mb-3">Proyectos por tipo</h3>
        <div class="space-y-3">
            <?php foreach ([
                'Titulación' => 'proyectos_titulacion',
                'Vinculación' => 'proyectos_vinculacion',
                'PIS' => 'proyectos_pis',
            ] as $label => $key): ?>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span><?= e($label) ?></span>
                    <span class="font-semibold"><?= (int) $stats[$key] ?></span>
                </div>
                <?php $max = max(1, (int) $stats['total_proyectos']); ?>
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full" style="width: <?= round((int) $stats[$key] / $max * 100) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <h3 class="font-semibold mb-3">Estado de proyectos</h3>
        <div class="space-y-3">
            <?php foreach ([
                'En revisión' => 'proyectos_en_revision',
                'Con observaciones' => 'proyectos_con_observaciones',
                'Aprobados' => 'proyectos_aprobados',
                'Vencidos' => 'proyectos_vencidos',
            ] as $label => $key): ?>
            <div class="flex justify-between text-sm">
                <span><?= e($label) ?></span>
                <span class="font-semibold"><?= (int) $stats[$key] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php elseif ($rol === 'docente'): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Proyectos asignados</p>
        <p class="text-3xl font-bold"><?= (int) $stats['proyectos_activos'] ?></p>
    </div>
</div>

<?php else: ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Mis proyectos</p>
        <p class="text-3xl font-bold"><?= (int) $stats['mis_proyectos'] ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Próximos eventos -->
<div class="bg-white rounded-xl shadow p-5 mt-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold">Próximos eventos</h3>
        <a href="<?= url('calendario') ?>" class="text-sm text-indigo-600 hover:underline">Ver calendario →</a>
    </div>
    <?php if (!$eventos): ?>
    <p class="text-sm text-gray-500">No hay eventos próximos.</p>
    <?php else: ?>
    <ol class="space-y-2">
        <?php foreach ($eventos as $ev): ?>
        <li class="flex items-center justify-between gap-3 p-3 rounded-lg bg-gray-50 text-sm">
            <div>
                <p class="font-medium"><?= e($ev['titulo']) ?></p>
                <p class="text-xs text-gray-400"><?= e($ev['codigo']) ?> · <?= e(date('d/m/Y H:i', strtotime($ev['fecha_evento']))) ?></p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= e(estado_badge($ev['estado'])) ?>"><?= e(ucfirst($ev['estado'])) ?></span>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
</div>
