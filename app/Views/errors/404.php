<?php
$logo = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='24' fill='%234f46e5'/%3E%3Ctext x='50' y='68' font-size='52' font-family='Arial' font-weight='bold' text-anchor='middle' fill='white'%3ES%3C/text%3E%3C/svg%3E";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= $logo ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-800 min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    <div class="blob w-[420px] h-[420px] bg-violet-600/30 -top-32 -right-32"></div>
    <div class="blob w-[360px] h-[360px] bg-indigo-600/30 -bottom-32 -left-32"></div>
    <div class="card w-full max-w-lg p-10 text-center relative z-10 bg-white rounded-3xl shadow-2xl">
        <div class="w-16 h-16 mx-auto bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mb-5">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
        </div>
        <p class="text-sm font-bold text-indigo-500 uppercase tracking-widest">Error 404</p>
        <h1 class="text-3xl font-extrabold text-slate-900 mt-2">Página no encontrada</h1>
        <p class="text-slate-500 mt-2">La página que buscas no existe o fue movida.</p>
        <a href="<?= url('dashboard') ?>" class="btn-primary w-full mt-8">Volver al inicio</a>
    </div>
</body>
</html>