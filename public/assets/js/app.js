// SIGEP - Utilidades frontend

document.addEventListener('DOMContentLoaded', function () {
    // Toggle del sidebar en móvil
    const toggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    // Confirmar acciones destructivas
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});
