document.addEventListener('DOMContentLoaded', function () {
    const submenus = document.querySelectorAll('.collapse');
    const activeSubmenu = localStorage.getItem('activeSubmenu');

    if (activeSubmenu) {
        const submenu = document.getElementById(activeSubmenu);

        if (submenu) {
            submenu.classList.add('show');

            const trigger = document.querySelector('[href="#' + activeSubmenu + '"]');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'true');
            }
        }
    }

    submenus.forEach(function (submenu) {
        submenu.addEventListener('shown.bs.collapse', function () {
            localStorage.setItem('activeSubmenu', submenu.id);
        });

        submenu.addEventListener('hidden.bs.collapse', function () {
            if (localStorage.getItem('activeSubmenu') === submenu.id) {
                localStorage.removeItem('activeSubmenu');
            }
        });
    });
});