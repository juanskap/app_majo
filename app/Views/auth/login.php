<div class="w-full max-w-md">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto bg-indigo-600 rounded-2xl flex items-center justify-center text-3xl mb-3">🎓</div>
            <h2 class="text-2xl font-bold text-gray-900"><?= e(APP_NAME) ?></h2>
            <p class="text-sm text-gray-500 mt-1"><?= e(APP_FULL_NAME) ?></p>
        </div>

        <form method="post" action="<?= url('auth/login') ?>" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= e(\App\Core\Request::csrfToken()) ?>">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                <input type="email" id="email" name="email" required autofocus
                       value="<?= e(old('email')) ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="correo@institucion.edu">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="••••••••">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition">
                Iniciar sesión
            </button>
        </form>

        <p class="text-sm text-center mt-4">
            <a href="<?= url('auth/olvide') ?>" class="text-indigo-600 hover:underline">¿Olvidaste tu contraseña?</a>
        </p>

        <p class="text-xs text-center text-gray-400 mt-6">
            Usuario por defecto: <code>admin@sigep.edu.ec</code> / <code>Admin123</code>
        </p>
    </div>
</div>
