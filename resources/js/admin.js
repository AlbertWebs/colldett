import './pwa';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('adminLayout', () => ({
    sidebarOpen: false,
    sidebarCollapsed: false,
    profileMenuOpen: false,
    notificationOpen: false,
    notifyCount: 4,
    activeTab: 'invoices',
    init() {
        this.sidebarCollapsed = localStorage.getItem('adminSidebarCollapsed') === 'true';
    },
    toggleSidebarCollapse() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('adminSidebarCollapsed', this.sidebarCollapsed ? 'true' : 'false');
    },
    closeOverlays() {
        this.profileMenuOpen = false;
        this.notificationOpen = false;
    },
}));

Alpine.start();
