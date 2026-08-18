<?php
/** @var string $title */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='24' fill='%234f46e5'/%3E%3Ctext x='50' y='68' font-size='52' font-family='Arial' font-weight='bold' text-anchor='middle' fill='white'%3ES%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="min-h-screen bg-slate-950 text-slate-800 flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Fondo decorativo -->
    <div class="blob w-[520px] h-[520px] bg-indigo-600/40 -top-40 -left-40"></div>
    <div class="blob w-[420px] h-[420px] bg-violet-600/30 top-1/3 -right-32"></div>
    <div class="blob w-[380px] h-[380px] bg-cyan-500/20 -bottom-32 left-1/4"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 opacity-60"></div>

    <?php if ($msgDemo = flash('demo')): ?>
    <div class="absolute top-4 left-1/2 -translate-x-1/2 w-full max-w-xl px-4">
        <div class="bg-blue-950 border border-blue-500/40 text-blue-100 px-4 py-3 text-sm rounded-xl break-all"><?= e($msgDemo) ?></div>
    </div>
    <?php endif; ?>

    <div class="relative z-10 w-full max-w-6xl flex">
        <!-- Panel de marca -->
        <div class="hidden lg:flex flex-1 flex-col justify-center px-10">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-2xl font-black text-white shadow-lg shadow-indigo-900/40">S</div>
                <div>
                    <p class="text-2xl font-extrabold text-white tracking-tight"><?= e(APP_NAME) ?></p>
                    <p class="text-sm text-slate-400"><?= e(APP_FULL_NAME) ?></p>
                </div>
            </div>
            <h1 class="text-4xl font-extrabold text-white leading-tight">
                Gestiona proyectos académicos<br>
                <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">de forma simple y ordenada.</span>
            </h1>
            <p class="text-slate-400 mt-4 max-w-md text-sm leading-relaxed">
                Seguimiento de etapas, documentos, tutorías, calendario y notificaciones en un solo lugar, con roles para administradores, docentes y estudiantes.
            </p>
            <div class="flex gap-2 mt-8">
                <span class="text-xs font-medium text-slate-300 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">📁 Proyectos por etapas</span>
                <span class="text-xs font-medium text-slate-300 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">📅 Calendario</span>
                <span class="text-xs font-medium text-slate-300 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">🔔 Avisos</span>
            </div>
        </div>

        <!-- Tarjeta del formulario -->
        <div class="w-full lg:w-[420px] lg:ml-10">
            <div class="bg-white rounded-3xl shadow-2xl shadow-slate-900/50 p-8 animate-in">
                <div class="lg:hidden flex items-center gap-3 mb-6">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-xl font-black text-white">S</div>
                    <div>
                        <p class="text-xl font-extrabold text-slate-900 leading-none"><?= e(APP_NAME) ?></p>
                        <p class="text-xs text-slate-500 mt-0.5"><?= e(APP_FULL_NAME) ?></p>
                    </div>
                </div>

                <?= $content ?>

                <p class="text-[11px] text-center text-slate-400 mt-6">© <?= date('Y') ?> <?= e(APP_NAME) ?> v<?= e(APP_VERSION) ?></p>
            </div>
        </div>
    </div>
</body>
</html>