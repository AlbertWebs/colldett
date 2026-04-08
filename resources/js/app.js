import './bootstrap';
import './pwa';

const menuButton = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');
const navBackdrop = document.querySelector('[data-nav-backdrop]');
const topbar = document.querySelector('[data-topbar]');
const headerShell = document.querySelector('[data-site-header-fixed]');
const root = document.documentElement;
const scrollTopBtn = document.querySelector('[data-scroll-top]');
const navDropdowns = document.querySelectorAll('.nav-dropdown');
const mobileNavMq = window.matchMedia('(max-width: 960px)');

const setMobileMenuOpen = (open) => {
    if (!menu || !menuButton) {
        return;
    }
    menu.classList.toggle('open', open);
    menuButton.classList.toggle('is-open', open);
    document.body.classList.toggle('nav-open', open);
    document.documentElement.classList.toggle('nav-open', open);
    menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
    menuButton.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    navBackdrop?.setAttribute('aria-hidden', open ? 'false' : 'true');
};

if (menuButton && menu) {
    menuButton.addEventListener('click', () => {
        setMobileMenuOpen(!menu.classList.contains('open'));
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMobileMenuOpen(false));
    });

    navBackdrop?.addEventListener('click', () => setMobileMenuOpen(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('open')) {
            setMobileMenuOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        if (!mobileNavMq.matches) {
            setMobileMenuOpen(false);
        }
    });
}

if (navDropdowns.length) {
    navDropdowns.forEach((dropdown) => {
        let closeTimer = null;

        const openDropdown = () => {
            if (closeTimer) {
                window.clearTimeout(closeTimer);
                closeTimer = null;
            }
            dropdown.setAttribute('open', '');
        };

        const scheduleClose = () => {
            if (closeTimer) {
                window.clearTimeout(closeTimer);
            }
            closeTimer = window.setTimeout(() => {
                dropdown.removeAttribute('open');
            }, 180);
        };

        dropdown.addEventListener('mouseenter', () => {
            if (window.innerWidth > 960) {
                openDropdown();
            }
        });

        dropdown.addEventListener('mouseleave', () => {
            if (window.innerWidth > 960) {
                scheduleClose();
            }
        });

        const menuLinks = dropdown.querySelectorAll('.nav-sub-link');
        menuLinks.forEach((link) => {
            link.addEventListener('click', () => {
                dropdown.removeAttribute('open');
            });
        });
    });
}

if (topbar) {
    const syncHeaderHeight = () => {
        const h = headerShell ? headerShell.offsetHeight : topbar.offsetHeight;
        root.style.setProperty('--header-height', `${h}px`);
    };

    const toggleTopbar = () => {
        const compact = window.scrollY > 24;
        topbar.classList.toggle('compact', compact);
        headerShell?.classList.toggle('is-compact', compact);
        syncHeaderHeight();
    };

    syncHeaderHeight();
    toggleTopbar();
    window.addEventListener('scroll', toggleTopbar, { passive: true });
    window.addEventListener('resize', syncHeaderHeight);
}

if (scrollTopBtn) {
    const toggleScrollTop = () => {
        scrollTopBtn.classList.toggle('visible', window.scrollY > 380);
    };

    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    toggleScrollTop();
    window.addEventListener('scroll', toggleScrollTop, { passive: true });
}

const normalizedPath = window.location.pathname.replace(/\/+$/, '') || '/';
const isAboutPage = normalizedPath === '/about';

if (isAboutPage) {
    const aboutRevealTargets = document.querySelectorAll(
        '.about-section .container h2, .about-section .container .about-intro, .about-team-card, .about-service-card, .about-timeline article, .about-compliance-list li, .about-client-list li, .about-choose-card, .about-reach-panel, .about-relations, .about-reach-highlights .reach-stat, .about-do-media, .about-compliance-media, .about-choose-side, .about-experience-clients, .about-experience-summary'
    );

    aboutRevealTargets.forEach((el, index) => {
        if (!el.classList.contains('reveal')) {
            el.classList.add('reveal', 'reveal-item');
        } else {
            el.classList.add('reveal-item');
        }

        el.style.setProperty('--reveal-delay', `${Math.min(index * 45, 420)}ms`);

        if (index % 3 === 1) {
            el.classList.add('reveal-left');
        } else if (index % 3 === 2) {
            el.classList.add('reveal-right');
        }
    });
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
