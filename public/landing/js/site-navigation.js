const menuToggle = document.querySelector('.nav-toggle');

if (menuToggle) {
    const closeMenu = () => {
        document.body.classList.remove('nav-open');
        menuToggle.setAttribute('aria-expanded', 'false');
    };

    menuToggle.addEventListener('click', () => {
        const expanded = document.body.classList.toggle('nav-open');
        menuToggle.setAttribute('aria-expanded', String(expanded));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('nav-open')) {
            closeMenu();
            menuToggle.focus();
        }
    });
}
