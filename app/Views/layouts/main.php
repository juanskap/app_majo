<?php
/** @var string $title */
use App\Core\Auth;
use App\Core\Request;

$rol = Auth::role();
$nombre = $_SESSION['user_nombre'] ?? 'Usuario';
$iniciales = implode('', array_map(fn ($p) => mb_substr($p, 0, 1), preg_split('/\s+/', trim($nombre)) ?: []));
$iniciales = strtoupper(mb_substr($iniciales, 0, 2));

$current = strtolower($_SESSION['__current'] ?? 'dashboard');
$navProyectos = in_array($current, ['proyectos', 'mis-proyectos', 'documentos', 'plan'], true);
$navCalendario = in_array($current, ['calendario'], true);
$navNotificaciones = $current === 'notificaciones';

function nav_item(string $href, string $label, bool $active, string $icon): string {
    $activeCls = $active ? 'bg-indigo-600 text-white shadow-md shadow-indigo-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white';
    return '<a href="' . $href . '" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition ' . $activeCls . '">'
        . $icon . '<span class="truncate">' . $label . '</span></a>';
}
function nav_label(string $text): string {
    return '<p class="px-3.5 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-widest text-slate-500">' . $text . '</p>';
}

$icDash   = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>';
$icProy   = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>';
$icCalendar = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>';
$icBell   = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>';
$icUsers  = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>';
$icTipo   = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>';
$icHistory = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
$icProfile = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.964 0m11.964 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';

$nav = [];
$nav[] = nav_item(url('dashboard'), 'Panel principal', $current === 'dashboard', $icDash);

$nav[] = nav_label('Proyectos');
if ($rol === 'estudiante' || $rol === 'docente') {
    $nav[] = nav_item(url('mis-proyectos'), 'Mis proyectos', $navProyectos, $icProy);
} else {
    $nav[] = nav_item(url('proyectos'), 'Proyectos', $navProyectos, $icProy);
}

$nav[] = nav_label('Organización');
$nav[] = nav_item(url('calendario'), 'Calendario', $navCalendario, $icCalendar);
$nav[] = nav_item(url('notificaciones'), 'Notificaciones', $navNotificaciones, $icBell);

if ($rol === 'admin') {
    $nav[] = nav_label('Administración');
    $nav[] = nav_item(url('usuarios'), 'Usuarios', $current === 'usuarios', $icUsers);
    $nav[] = nav_item(url('tipos-proyecto'), 'Tipos de proyecto', $current === 'tipos-proyecto', $icTipo);
    $nav[] = nav_item(url('historial'), 'Historial', $current === 'historial', $icHistory);
}
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
<body class="bg-slate-100 text-slate-800 min-h-screen">
<div class="flex min-h-screen">

    <!-- ===== Sidebar ===== -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-slate-900 text-slate-100 flex flex-col transform -translate-x-full lg:translate-x-0 lg:static transition-transform duration-200">
        <div class="px-6 py-6 border-b border-slate-800 flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-xl font-black text-white shadow-lg shadow-indigo-900/50">S</div>
            <div class="min-w-0">
                <h1 class="text-lg font-extrabold text-white leading-none"><?= e(APP_NAME) ?></h1>
                <p class="text-[11px] text-slate-400 mt-1 truncate"><?= e(APP_FULL_NAME) ?></p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-5 overflow-y-auto">
            <?= implode('', $nav) ?>
        </nav>

        <div class="p-4 border-t border-slate-800 space-y-1">
            <a href="<?= url('perfil') ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <?= $icProfile ?><span>Mi perfil</span>
            </a>
            <form method="post" action="<?= url('auth/logout') ?>">
                <input type="hidden" name="_csrf" value="<?= e(Request::csrfToken()) ?>">
                <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-red-300 hover:bg-red-950/40 hover:text-red-200 transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ===== Contenido ===== -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Topbar -->
        <header class="bg-white/95 backdrop-blur border-b border-slate-200 px-5 py-3 flex items-center justify-between gap-4 sticky top-0 z-30">
            <button id="menu-toggle" class="lg:hidden text-2xl text-slate-600" aria-label="Abrir menú">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>

            <div class="text-sm hidden sm:block">
                <span class="text-slate-400"><?= e($title ?? APP_NAME) ?></span>
            </div>

            <div class="flex items-center gap-3">
                <?php
                $notifModel = new \App\Models\Notificacion();
                $notifNoLeidas = $notifModel->noLeidas(Auth::id());
                $notifUltimas = $notifModel->porUsuario(Auth::id(), 5);
                ?>
                <details class="relative" id="notif-menu">
                    <summary class="relative cursor-pointer list-none flex items-center justify-center w-10 h-10 rounded-xl hover:bg-slate-100 transition text-slate-500" aria-label="Notificaciones">
                        <?= str_replace('w-5 h-5', 'w-5 h-5', $icBell) ?>
                        <?php if ($notifNoLeidas > 0): ?>
                        <span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-[11px] font-bold"><?= (int) $notifNoLeidas ?></span>
                        <?php endif; ?>
                    </summary>
                    <div class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden">
                        <div class="px-5 py-4 border-b flex items-center justify-between">
                            <span class="font-semibold text-sm">Notificaciones</span>
                            <a href="<?= url('notificaciones') ?>" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
                        </div>
                        <?php if (!$notifUltimas): ?>
                        <p class="px-4 py-8 text-sm text-slate-500 text-center">Sin notificaciones.</p>
                        <?php else: ?>
                        <ol class="max-h-72 overflow-y-auto">
                            <?php foreach ($notifUltimas as $n): ?>
                            <li class="px-5 py-3 border-b last:border-0 <?= $n['leida'] ? 'bg-white' : 'bg-indigo-50/60' ?>">
                                <p class="text-sm font-medium"><?= e($n['titulo']) ?></p>
                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-2"><?= e($n['mensaje']) ?></p>
                                <p class="text-[11px] text-slate-400 mt-1"><?= e(format_date($n['creado_en'])) ?></p>
                            </li>
                            <?php endforeach; ?>
                        </ol>
                        <?php endif; ?>
                    </div>
                </details>

                <details class="relative" id="user-menu">
                    <summary class="cursor-pointer list-none flex items-center gap-2.5 pl-1 pr-1 py-1 rounded-xl hover:bg-slate-100 transition">
                        <span class="avatar"><?= e($iniciales) ?></span>
                        <span class="hidden md:block text-left leading-tight">
                            <span class="block text-sm font-semibold text-slate-800 max-w-[160px] truncate"><?= e($nombre) ?></span>
                            <span class="block text-[11px] text-slate-400 uppercase tracking-wide font-medium"><?= e($rol) ?></span>
                        </span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </summary>
                    <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden py-1.5">
                        <a href="<?= url('perfil') ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <?= str_replace('w-5 h-5', 'w-4 h-4', $icProfile) ?> Mi perfil
                        </a>
                        <div class="my-1 border-t border-slate-100"></div>
                        <form method="post" action="<?= url('auth/logout') ?>">
                            <input type="hidden" name="_csrf" value="<?= e(Request::csrfToken()) ?>">
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </header>

        <main class="p-5 lg:p-8 flex-1">
            <?php if ($msgSuccess = flash('success')): ?>
            <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm animate-in">
                <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= e($msgSuccess) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($msgError = flash('error')): ?>
            <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm animate-in">
                <svg class="w-5 h-5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span><?= e($msgError) ?></span>
            </div>
            <?php endif; ?>

            <div class="animate-in">
                <?= $content ?>
            </div>
        </main>
    </div>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>