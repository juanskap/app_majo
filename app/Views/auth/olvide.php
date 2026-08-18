<div>
    <h2 class="text-2xl font-extrabold text-slate-900">Recuperar contraseña</h2>
    <p class="text-sm text-slate-500 mt-1 mb-6">Ingresa tu correo registrado y te enviaremos un enlace para restablecerla.</p>

    <form method="post" action="<?= url('auth/olvide') ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(\App\Core\Request::csrfToken()) ?>">
        <div>
            <label for="email" class="label">Correo electrónico</label>
            <input type="email" id="email" name="email" required value="<?= e(old('email')) ?>"
                   class="input" placeholder="correo@institucion.edu">
        </div>
        <button type="submit" class="btn-primary w-full !py-3 !rounded-2xl">Enviar enlace</button>
    </form>

    <p class="text-sm text-center mt-5">
        <a href="<?= url('auth/login') ?>" class="text-indigo-600 hover:underline">← Volver al inicio de sesión</a>
    </p>
</div>