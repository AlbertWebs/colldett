<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Panel' }} | {{ config('colldett.company.name', 'Colldett') }}</title>
    <meta name="theme-color" content="{{ config('colldett.pwa.admin.theme_color', '#0f4c81') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('colldett.pwa.admin.short_name', 'Admin') }}">
    <link rel="icon" type="image/png" href="{{ asset(config('colldett.pwa.icon', 'uploads/favicon.png')) }}">
    <link rel="apple-touch-icon" href="{{ asset(config('colldett.pwa.icon', 'uploads/favicon.png')) }}">
    <link rel="manifest" href="{{ asset('manifest-admin.webmanifest') }}">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body class="bg-admin-bg text-admin-ink" data-pwa-context="admin">
@php
    $adminUiSettings = \App\Support\AdminStoredSettings::all();
    $showReportsNav = filter_var($adminUiSettings['show_reports_nav'] ?? false, FILTER_VALIDATE_BOOL);
    $navGroups = [
        [
            'title' => 'Overview',
            'items' => [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12h18M3 6h18M3 18h18', 'active' => ['admin.dashboard']],
                ...($showReportsNav ? [['route' => 'admin.reports', 'label' => 'Reports', 'icon' => 'M4 18h16 M7 14v-4 M12 14V8 M17 14V6', 'active' => ['admin.reports']]] : []),
            ],
        ],
        [
            'title' => 'Content',
            'items' => [
                ['route' => 'admin.about-content.edit', 'label' => 'About Content', 'icon' => 'M4 7h16M4 12h16M4 17h12', 'active' => ['admin.about-content.*']],
                ['route' => 'admin.services.index', 'label' => 'Services', 'icon' => 'M4 7h16M4 12h16M4 17h10', 'active' => ['admin.services.*']],
                ['route' => 'admin.industries.index', 'label' => 'Industries', 'icon' => 'M3 6h18M6 12h12M9 18h6', 'active' => ['admin.industries.*']],
                ['route' => 'admin.insights.index', 'label' => 'Insights', 'icon' => 'M4 6h16M4 12h10M4 18h7', 'active' => ['admin.insights.*']],
                ['route' => 'admin.team', 'label' => 'Team', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0.01 0', 'active' => ['admin.team', 'admin.team.*']],
            ],
        ],
        [
            'title' => 'Operations',
            'items' => [
                ['route' => 'admin.clients', 'label' => 'Clients', 'icon' => 'M17 21v-2a4 4 0 0 0-3-3.87 M9 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8', 'active' => ['admin.clients', 'admin.clients.*']],
                ['route' => 'admin.cases', 'label' => 'Cases', 'icon' => 'M4 6h16v12H4z M9 10h6 M9 14h4', 'active' => ['admin.cases', 'admin.cases.*']],
                ['route' => 'admin.billing', 'label' => 'Management', 'icon' => 'M3 8h18M3 16h18 M6 4h12v16H6z', 'active' => ['admin.billing', 'admin.billing.*']],
            ],
        ],
        [
            'title' => 'Administration',
            'items' => [
                ['route' => 'admin.users', 'label' => 'Users', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0.01 0', 'active' => ['admin.users', 'admin.users.*']],
                ['route' => 'admin.settings', 'label' => 'Settings', 'icon' => 'M12 8a4 4 0 1 0 0.01 0 M4.93 4.93l1.41 1.41 M17.66 17.66l1.41 1.41 M1 12h2 M21 12h2', 'active' => ['admin.settings', 'admin.settings.*']],
            ],
        ],
    ];
@endphp

<div x-data="adminLayout" @click="closeOverlays()" class="min-h-screen">
    <div class="flex">
        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-72',
                sidebarCollapsed ? 'is-collapsed' : ''
            ]"
            class="admin-sidebar fixed inset-y-0 left-0 z-40 w-72 border-r border-admin-border text-slate-100 transition-all duration-300"
        >
            <div class="admin-sidebar-top border-b border-slate-700/70 px-4 py-4">
                <div class="admin-sidebar-brand" :class="sidebarCollapsed ? 'hidden lg:hidden' : ''">
                    <div class="flex items-start gap-3">
                        <div class="admin-sidebar-brand-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6 stroke-current stroke-[1.75]" aria-hidden="true">
                                <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z" stroke-linejoin="round"/>
                                <path d="M8 9h8M8 13h5M8 17h3" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1 pt-0.5">
                            <p class="text-[10px] uppercase tracking-[0.16em] text-slate-400">Enterprise Panel</p>
                            <h1 class="text-base font-semibold text-white/95">Admin Console</h1>
                        </div>
                    </div>
                </div>
                <div class="hidden justify-center lg:flex" x-show="sidebarCollapsed" x-transition.opacity>
                    <div class="admin-sidebar-brand-icon admin-sidebar-brand-icon--collapsed" aria-hidden="true" title="Admin Console">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 stroke-current stroke-[1.75]" aria-hidden="true">
                            <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z" stroke-linejoin="round"/>
                            <path d="M8 9h8M8 13h5M8 17h3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <button class="absolute right-3 top-3 rounded-md p-2 hover:bg-slate-800 lg:hidden" @click.stop="sidebarOpen = false">✕</button>
            </div>

            <nav class="flex h-[calc(100vh-4rem)] flex-col px-3 py-4">
                <div class="flex-1 space-y-4 overflow-y-auto pr-1">
                    @foreach($navGroups as $group)
                        <section>
                            <p class="admin-sidebar-section-label" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $group['title'] }}</p>
                            <ul class="space-y-1">
                                @foreach($group['items'] as $item)
                                    @php
                                        $isActive = request()->routeIs(...($item['active'] ?? [$item['route']]));
                                    @endphp
                                    <li>
                                        <a
                                            href="{{ route($item['route']) }}"
                                            title="{{ $item['label'] }}"
                                            :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                            class="admin-sidebar-link group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ $isActive ? 'is-active text-white' : 'text-slate-300' }}"
                                        >
                                            <span class="admin-sidebar-icon-wrap">
                                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 stroke-current stroke-2">
                                                    <path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </span>
                                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="mt-4 pb-4">
                    @csrf
                    <button type="submit" title="Sign out" :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''" class="admin-sidebar-logout flex w-full items-center gap-3 rounded-xl border px-3 py-2.5 text-left text-sm text-slate-300 hover:bg-slate-800/80">
                        <span aria-hidden="true">↩</span>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Sign out</span>
                    </button>
                </form>
            </nav>
        </aside>

        <div :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'" class="w-full transition-all duration-300">
            <header class="admin-top-header sticky top-0 z-30 border-b border-admin-border bg-admin-surface/90 backdrop-blur">
                <div class="flex min-h-16 flex-wrap items-center gap-3 px-4 py-2 lg:px-6">
                    <button class="rounded-lg border border-admin-border bg-white p-2 lg:hidden" @click.stop="sidebarOpen = true">☰</button>
                    <button class="hidden rounded-lg border border-admin-border bg-white p-2 text-admin-muted transition hover:border-slate-300 hover:text-admin-ink lg:inline-flex" @click.stop="toggleSidebarCollapse()" :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                        <span x-show="!sidebarCollapsed">⟨⟨</span>
                        <span x-show="sidebarCollapsed">⟩⟩</span>
                    </button>
                    <div class="admin-top-search relative max-w-xl flex-1 min-w-[220px]">
                        <input class="admin-input pl-9 pr-18" placeholder="Search clients, cases, invoices..." />
                        <span class="pointer-events-none absolute left-3 top-2.5 text-admin-muted">⌕</span>
                        <span class="admin-search-kbd pointer-events-none absolute right-3 top-2 text-[11px] text-admin-muted">Ctrl K</span>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <div class="relative">
                            <button @click.stop="notificationOpen = !notificationOpen; profileMenuOpen = false" class="admin-header-icon-btn relative rounded-lg border border-admin-border bg-white px-3 py-2 text-sm">
                                <span aria-hidden="true">🔔</span>
                                <span class="absolute -right-2 -top-2 rounded-full bg-rose-500 px-1.5 text-xs text-white" x-text="notifyCount"></span>
                            </button>
                            <div x-show="notificationOpen" x-transition class="absolute right-0 mt-2 w-80 overflow-hidden rounded-xl border border-admin-border bg-white shadow-xl">
                                <div class="border-b border-admin-border px-3 py-2.5">
                                    <p class="text-sm font-semibold">Notifications</p>
                                </div>
                                <ul class="space-y-2 text-sm text-admin-muted">
                                    <li class="mx-3 mt-3 rounded-lg bg-admin-primary-soft p-2">3 invoices are due today.</li>
                                    <li class="mx-3 rounded-lg bg-slate-50 p-2">2 demand letters pending approval.</li>
                                    <li class="mx-3 mb-3 rounded-lg bg-slate-50 p-2">New payment captured via bank transfer.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="relative">
                            <button @click.stop="profileMenuOpen = !profileMenuOpen; notificationOpen = false" class="admin-profile-btn flex items-center gap-2 rounded-lg border border-admin-border bg-white px-3 py-2 text-sm">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-admin-primary text-white">AD</span>
                                <span class="hidden sm:block text-left leading-tight">
                                    <strong class="block text-[13px] text-admin-ink">Admin User</strong>
                                    <small class="block text-[11px] text-admin-muted">Administrator</small>
                                </span>
                            </button>
                            <div x-show="profileMenuOpen" x-transition class="absolute right-0 mt-2 w-52 admin-card p-2 text-sm">
                                <a class="block rounded-md px-3 py-2 hover:bg-slate-50" href="{{ route('admin.profile') }}">Profile</a>
                                <a class="block rounded-md px-3 py-2 hover:bg-slate-50" href="{{ route('admin.account-settings') }}">Account Settings</a>
                                <a class="block rounded-md px-3 py-2 hover:bg-slate-50" href="{{ route('admin.change-password') }}">Change Password</a>
                                <form method="POST" action="{{ route('admin.logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-rose-600 hover:bg-rose-50">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toLabelText = (field) => {
            if (field.getAttribute('data-label')) return field.getAttribute('data-label');
            const placeholder = field.getAttribute('placeholder');
            if (placeholder && placeholder.trim() !== '') return placeholder.trim();
            const name = field.getAttribute('name') || '';
            if (!name) return 'Field';
            return name
                .replace(/[_\-]+/g, ' ')
                .replace(/\b\w/g, (c) => c.toUpperCase())
                .trim();
        };

        const ensureLabels = () => {
            const fields = document.querySelectorAll('main form input, main form select, main form textarea');
            fields.forEach((field, index) => {
                const type = (field.getAttribute('type') || '').toLowerCase();
                if (type === 'hidden' || type === 'checkbox' || type === 'radio' || type === 'submit' || type === 'button') return;
                if (field.getAttribute('data-no-autolabel') === 'true') return;

                let id = field.getAttribute('id');
                if (!id) {
                    id = `admin-field-${index}-${Math.random().toString(36).slice(2, 8)}`;
                    field.setAttribute('id', id);
                }

                const parent = field.parentElement;
                if (!parent) return;

                const existing = parent.querySelector(`label[for="${id}"]`);
                if (existing) return;

                const label = document.createElement('label');
                label.setAttribute('for', id);
                label.className = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted';
                label.textContent = toLabelText(field);
                parent.insertBefore(label, field);
            });
        };

        ensureLabels();

        if (!window.Quill) return;

        const textareas = document.querySelectorAll('main form textarea');
        textareas.forEach((textarea, index) => {
            if (textarea.dataset.noEditor === 'true') return;
            if (textarea.dataset.quillReady === 'true') return;

            const wrapper = document.createElement('div');
            wrapper.className = 'admin-quill-wrapper';
            wrapper.style.border = '1px solid #dbe2ea';
            wrapper.style.borderRadius = '10px';
            wrapper.style.background = '#fff';

            const editor = document.createElement('div');
            editor.id = `admin-quill-${index}`;
            editor.style.minHeight = '320px';

            textarea.style.display = 'none';
            textarea.parentNode.insertBefore(wrapper, textarea);
            wrapper.appendChild(editor);
            textarea.dataset.quillReady = 'true';

            const quill = new Quill(editor, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'blockquote', 'code-block'],
                        ['clean'],
                    ],
                },
            });

            quill.root.innerHTML = textarea.value || '';

            const form = textarea.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    textarea.value = quill.root.innerHTML;
                });
            }
        });
    });
</script>
</body>
</html>
