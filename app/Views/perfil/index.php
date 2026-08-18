<?php
/** @var array $perfil */
use App\Core\Request;

$nombre = $perfil['nombres'] . ' ' . $perfil['apellidos'];
$iniciales = strtoupper(mb_substr($perfil['nombres'], 0, 1) . mb_substr($perfil['apellidos'], 0, 1));
$detalle = $perfil['detalle'] ?? null;
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Mi perfil</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Tarjeta de identidad -->
    <div class="card p-6 self-start lg:sticky lg:top-24">
        <div class="flex items-center gap-4">
            <span class="avatar !w-14 !h-14 !text-xl"><?= e($iniciales) ?></span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 truncate"><?= e($nombre) ?></p>
                <p class="text-sm text-slate-500"><?= e($perfil['email']) ?></p>
            </div>
        </div>

        <dl class="mt-6 space-y-3 text-sm">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Rol</dt>
                <dd class="font-semibold"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 uppercase"><?= e($perfil['rol']) ?></span></dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Teléfono</dt>
                <dd><?= $perfil['telefono'] ? e($perfil['telefono']) : '<span class="text-slate-400">—</span>' ?></dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Registrado</dt>
                <dd><?= e(date('d/m/Y', strtotime($perfil['creado_en']))) ?></dd>
            </div>

            <?php if ($perfil['rol'] === 'estudiante' && $detalle): ?>
            <div class="border-t border-slate-100 pt-3 space-y-2">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Código</dt>
                    <dd class="font-semibold"><?= e($detalle['codigo']) ?></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Carrera</dt>
                    <dd class="text-right"><?= e($detalle['carrera']) ?></dd>
                </div>
            </div>
            <?php elseif ($perfil['rol'] === 'docente' && $detalle): ?>
            <div class="border-t border-slate-100 pt-3 space-y-2">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Título</dt>
                    <dd class="text-right font-semibold"><?= $detalle['titulo'] ? e($detalle['titulo']) : '—' ?></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Especialidad</dt>
                    <dd class="text-right"><?= $detalle['especialidad'] ? e($detalle['especialidad']) : '—' ?></dd>
                </div>
            </div>
            <?php endif; ?>
        </dl>
    </div>

    <!-- Formularios -->
    <div class="lg:col-span-2 space-y-6">
        <div class="card p-6">
            <h2 class="font-bold text-slate-900 flex items-center gap-2 mb-5">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.964 0m11.964 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Datos personales
            </h2>
            <form method="post" action="<?= url('perfil/actualizar-datos') ?>" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <input type="hidden" name="_csrf" value="<?= e(Request::csrfToken()) ?>">
                <div>
                    <label class="label">Nombres</label>
                    <input type="text" name="nombres" class="input" value="<?= e($perfil['nombres']) ?>" required>
                </div>
                <div>
                    <label class="label">Apellidos</label>
                    <input type="text" name="apellidos" class="input" value="<?= e($perfil['apellidos']) ?>" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Teléfono</label>
                    <input type="tel" name="telefono" class="input" value="<?= e($perfil['telefono'] ?? '') ?>" placeholder="Opcional">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>

        <div class="card p-6">
            <h2 class="font-bold text-slate-900 flex items-center gap-2 mb-5">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                Cambiar contraseña
            </h2>
            <form method="post" action="<?= url('perfil/cambiar-password') ?>" class="grid grid-cols-1 sm:grid-cols-2 gap-4" data-confirm="¿Deseas actualizar tu contraseña?">
                <input type="hidden" name="_csrf" value="<?= e(Request::csrfToken()) ?>">
                <div class="sm:col-span-2">
                    <label class="label">Contraseña actual</label>
                    <input type="password" name="password_actual" class="input" required autocomplete="current-password">
                </div>
                <div>
                    <label class="label">Nueva contraseña</label>
                    <input type="password" name="password" class="input" required minlength="6" autocomplete="new-password">
                </div>
                <div>
                    <label class="label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmar" class="input" required minlength="6" autocomplete="new-password">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary">Actualizar contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>