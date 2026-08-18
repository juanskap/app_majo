<?php
/** @var string $token */
?>
<div>
    <h2 class="text-2xl font-extrabold text-slate-900">Nueva contraseña</h2>
    <p class="text-sm text-slate-500 mt-1 mb-6">Escribe tu nueva contraseña (mínimo 6 caracteres).</p>

    <form method="post" action="<?= url('auth/cambiar') ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(\App\Core\Request::csrfToken()) ?>">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div>
            <label for="password" class="label">Nueva contraseña</label>
            <input type="password" id="password" name="password" required minlength="6"
                   class="input" placeholder="••••••••" autocomplete="new-password">
        </div>
        <button type="submit" class="btn-primary w-full !py-3 !rounded-2xl">Guardar contraseña</button>
    </form>

    <p class="text-sm text-center mt-5">
        <a href="<?= url('auth/login') ?>" class="text-indigo-600 hover:underline">← Volver al inicio de sesión</a>
    </p>
</div>