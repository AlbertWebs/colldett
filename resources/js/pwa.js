/**
 * PWA: register service worker + optional install banner (Chrome/Edge) or iOS hint.
 * Expects document.body.dataset.pwaContext to be "site" or "admin".
 */

const STORAGE = {
    site: 'colldett_pwa_install_dismissed_site',
    admin: 'colldett_pwa_install_dismissed_admin',
};

function isStandalone() {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true
    );
}

function isIosSafari() {
    const ua = navigator.userAgent || '';
    return /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
}

function faviconHref() {
    const link = document.querySelector('link[rel~="icon"]');
    return link?.href || '/uploads/favicon.png';
}

async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }
    try {
        await navigator.serviceWorker.register('/sw.js', { scope: '/' });
    } catch (e) {
        console.warn('[PWA] Service worker registration failed', e);
    }
}

function buildBanner(context, { deferredPrompt, iosHint }) {
    const iconUrl = faviconHref();
    const isAdmin = context === 'admin';
    const title = isAdmin ? 'Install admin console' : 'Install Colldett';
    const body = isAdmin
        ? 'Add this panel to your home screen for quick access to billing, cases, and reports.'
        : 'Add Colldett to your home screen for faster access and a focused experience.';

    const wrap = document.createElement('div');
    wrap.className = `pwa-install-banner ${isAdmin ? 'pwa-install-banner--admin' : ''}`;
    wrap.setAttribute('role', 'dialog');
    wrap.setAttribute('aria-labelledby', 'pwa-install-title');

    wrap.innerHTML = `
        <div class="pwa-install-banner__inner">
            <img class="pwa-install-banner__icon" src="${iconUrl}" width="40" height="40" alt="" />
            <div class="pwa-install-banner__text">
                <p id="pwa-install-title" class="pwa-install-banner__title">${title}</p>
                <p class="pwa-install-banner__body">${iosHint ? iosHint : body}</p>
            </div>
            <div class="pwa-install-banner__actions">
                ${iosHint ? '' : '<button type="button" class="pwa-install-banner__btn pwa-install-banner__btn--primary" data-pwa-install>Install</button>'}
                <button type="button" class="pwa-install-banner__btn pwa-install-banner__btn--ghost" data-pwa-dismiss>Not now</button>
            </div>
        </div>
    `;

    const dismiss = () => {
        localStorage.setItem(STORAGE[context], '1');
        wrap.remove();
    };

    wrap.querySelector('[data-pwa-dismiss]')?.addEventListener('click', dismiss);

    const installBtn = wrap.querySelector('[data-pwa-install]');
    if (installBtn && deferredPrompt) {
        installBtn.addEventListener('click', async () => {
            installBtn.disabled = true;
            try {
                await deferredPrompt.prompt();
                await deferredPrompt.userChoice;
            } catch {
                /* ignore */
            }
            deferredPrompt = null;
            dismiss();
        });
    }

    document.body.appendChild(wrap);
}

/**
 * @param {'site'|'admin'} context
 */
export function initPwa(context) {
    if (context !== 'site' && context !== 'admin') {
        return;
    }

    registerServiceWorker();

    if (isStandalone()) {
        return;
    }

    if (localStorage.getItem(STORAGE[context])) {
        return;
    }

    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (document.querySelector('.pwa-install-banner')) {
            return;
        }
        buildBanner(context, { deferredPrompt, iosHint: null });
    });

    /* iOS Safari: no beforeinstallprompt — show a one-time hint */
    if (isIosSafari()) {
        window.setTimeout(() => {
            if (isStandalone() || localStorage.getItem(STORAGE[context]) || document.querySelector('.pwa-install-banner')) {
                return;
            }
            const hint =
                'On Safari: tap Share → <strong>Add to Home Screen</strong> to install this app.';
            buildBanner(context, { deferredPrompt: null, iosHint: hint });
        }, 5000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.body?.dataset?.pwaContext;
    if (ctx === 'site' || ctx === 'admin') {
        initPwa(ctx);
    }
});
