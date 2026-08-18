<?php
$logo = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='24' fill='%234f46e5'/%3E%3Ctext x='50' y='68' font-size='52' font-family='Arial' font-weight='bold' text-anchor='middle' fill='white'%3ES%3C/text%3E%3C/svg%3E";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= $logo ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-800 min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    <div class="blob w-[420px] h-[420px] bg-red-600/30 -top-32 -right-32"></div>
    <div class="blob w-[360px] h-[360px] bg-indigo-600/30 -bottom-32 -left-32"></div>
    <div class="card w-full max-w-lg p-10 text-center relative z-10 bg-white rounded-3xl shadow-2xl">
        <div class="w-16 h-16 mx-auto bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-3xl mb-5">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
        <p class="text-sm font-bold text-red-500 uppercase tracking-widest">Error 403</p>
        <h1 class="text-3xl font-extrabold text-slate-900 mt-2">Acceso denegado</h1>
        <p class="text-slate-500 mt-2">No tienes permisos para ver esta página.</p>
        <a href="<?= url('dashboard') ?>" class="btn-primary w-full mt-8">Volver al inicio</a>
    </div>
</body>
</html>