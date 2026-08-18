<div>
    <h2 class="text-2xl font-extrabold text-slate-900">Iniciar sesión</h2>
    <p class="text-sm text-slate-500 mt-1 mb-6">Accede con tu cuenta institucional.</p>

    <form method="post" action="<?= url('auth/login') ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(\App\Core\Request::csrfToken()) ?>">

        <div>
            <label for="email" class="label">Correo electrónico</label>
            <input type="email" id="email" name="email" required autofocus
                   value="<?= e(old('email')) ?>"
                   class="input" placeholder="correo@institucion.edu">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="label !mb-0">Contraseña</label>
                <a href="<?= url('auth/olvide') ?>" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:underline">¿Olvidaste tu contraseña?</a>
            </div>
            <input type="password" id="password" name="password" required
                   class="input" placeholder="••••••••">
        </div>

        <button type="submit" class="btn-primary w-full !py-3 !rounded-2xl">
            Iniciar sesión
        </button>
    </form>
</div>