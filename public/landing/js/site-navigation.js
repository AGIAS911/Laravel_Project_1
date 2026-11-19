const menuToggle = document.querySelector('.nav-toggle');

menuToggle?.addEventListener('click', () => {
    const expanded = document.body.classList.toggle('nav-open');
    menuToggle.setAttribute('aria-expanded', String(expanded));
});
