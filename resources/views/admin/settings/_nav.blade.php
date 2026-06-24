@php
    $tabs = [
        ['route' => 'admin.settings.company', 'label' => 'Company', 'hint' => 'Public profile & contact'],
        ['route' => 'admin.settings.branding', 'label' => 'Branding', 'hint' => 'Logos & social links'],
        ['route' => 'admin.settings.documents', 'label' => 'Documents', 'hint' => 'Invoices & letterhead'],
        ['route' => 'admin.settings.operations', 'label' => 'Operations', 'hint' => 'Email & panel prefs'],
        ['route' => 'admin.settings.security', 'label' => 'Security', 'hint' => 'PIN & data tools'],
    ];
@endphp
<nav class="admin-card flex flex-wrap gap-2 p-2" aria-label="Settings sections">
    @foreach($tabs as $tab)
        @php $active = request()->routeIs($tab['route']); @endphp
        <a
            href="{{ route($tab['route']) }}"
            title="{{ $tab['hint'] }}"
            class="rounded-lg px-3 py-2 text-sm font-semibold transition {{ $active ? 'bg-admin-primary text-white shadow-sm' : 'text-admin-muted hover:bg-slate-50 hover:text-admin-ink' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
