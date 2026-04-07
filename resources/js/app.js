import './bootstrap';

const menuButton = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');
const topbar = document.querySelector('[data-topbar]');
const root = document.documentElement;

if (menuButton && menu) {
    menuButton.addEventListener('click', () => {
        menu.classList.toggle('open');
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => menu.classList.remove('open'));
    });
}

if (topbar) {
    const syncHeaderHeight = () => {
        root.style.setProperty('--header-height', `${topbar.offsetHeight}px`);
    };

    const toggleTopbar = () => {
        topbar.classList.toggle('compact', window.scrollY > 24);
        syncHeaderHeight();
    };

    syncHeaderHeight();
    toggleTopbar();
    window.addEventListener('scroll', toggleTopbar, { passive: true });
    window.addEventListener('resize', syncHeaderHeight);
}

const reveals = document.querySelectorAll('.reveal');

if ('IntersectionObserver' in window && reveals.length) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    reveals.forEach((el) => observer.observe(el));
} else {
    reveals.forEach((el) => el.classList.add('visible'));
}
