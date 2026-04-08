@extends('admin.layouts.app')

@section('content')
@php
    $c = $client;
    $name = trim((string) ($c['name'] ?? ''));
    $company = trim((string) ($c['company'] ?? ''));
    $email = trim((string) ($c['email'] ?? ''));
    $phone = trim((string) ($c['phone'] ?? ''));
    $phoneAlt = trim((string) ($c['phone_alt'] ?? ''));
    $tel = fn (string $p): string => $p === '' ? '#' : 'tel:'.preg_replace('/[^\d+]/', '', $p);
    $web = trim((string) ($c['website'] ?? ''));
    $webHref = $web !== '' ? (str_starts_with($web, 'http://') || str_starts_with($web, 'https://') ? $web : 'https://'.ltrim($web, '/')) : '';
    $isActive = ($c['status'] ?? '') === 'active';
    $locLine = collect([$c['city'] ?? '', $c['country'] ?? ''])->filter()->implode(', ');
@endphp
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- Breadcrumb + title --}}
    <div class="space-y-3">
        <nav class="flex flex-wrap items-center gap-1.5 text-sm text-admin-muted" aria-label="Breadcrumb">
            <a href="{{ route('admin.clients') }}" class="font-medium text-admin-primary hover:underline">Clients</a>
            <span aria-hidden="true">/</span>
            <span class="text-admin-ink">{{ $company !== '' ? $company : 'Client #'.$c['id'] }}</span>
        </nav>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 gap-y-1">
                    <h2 class="text-2xl font-bold text-admin-ink">{{ $name !== '' ? $name : 'Unnamed contact' }}</h2>
                    @if(($c['account_number'] ?? '') !== '')
                        <span class="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 font-mono text-xs font-semibold text-slate-700">{{ $c['account_number'] }}</span>
                    @endif
                </div>
                @if(($c['contact_title'] ?? '') !== '')
                    <p class="mt-1 text-sm text-admin-muted">{{ $c['contact_title'] }}</p>
                @endif
                @if($company !== '')
                    <p class="mt-0.5 text-sm font-medium text-admin-ink">{{ $company }}</p>
                @endif
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @if($isActive)
                    <span class="admin-status-chip admin-status-chip-active">Active</span>
                @else
                    <span class="admin-status-chip bg-slate-100 text-slate-600">Inactive</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Main column --}}
        <div class="space-y-6 xl:col-span-2">
            {{-- Contact --}}
            <article class="admin-card overflow-hidden p-0">
                <div class="border-b border-admin-border bg-slate-50/80 px-5 py-3">
                    <h3 class="text-sm font-semibold text-admin-ink">Contact &amp; communication</h3>
                    <p class="text-xs text-admin-muted">Reach this client using the details below.</p>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Email</p>
                        @if($email !== '')
                            <a href="mailto:{{ $email }}" class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-admin-primary hover:underline">{{ $email }}</a>
                        @else
                            <p class="mt-1 text-sm text-admin-muted">Not set</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Primary phone</p>
                        @if($phone !== '')
                            <a href="{{ $tel($phone) }}" class="mt-1 block text-sm font-medium text-admin-ink hover:text-admin-primary">{{ $phone }}</a>
                        @else
                            <p class="mt-1 text-sm text-admin-muted">Not set</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Alternate phone</p>
                        @if($phoneAlt !== '')
                            <a href="{{ $tel($phoneAlt) }}" class="mt-1 block text-sm font-medium text-admin-ink hover:text-admin-primary">{{ $phoneAlt }}</a>
                        @else
                            <p class="mt-1 text-sm text-admin-muted">Not set</p>
                        @endif
                    </div>
                </div>
            </article>

            {{-- Company --}}
            <article class="admin-card overflow-hidden p-0">
                <div class="border-b border-admin-border bg-slate-50/80 px-5 py-3">
                    <h3 class="text-sm font-semibold text-admin-ink">Company &amp; online</h3>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Legal / trading name</p>
                        <p class="mt-1 text-sm font-medium text-admin-ink">{{ $company !== '' ? $company : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Industry</p>
                        <p class="mt-1 text-sm text-admin-ink">{{ ($c['industry'] ?? '') !== '' ? $c['industry'] : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Website</p>
                        @if($web !== '' && $webHref !== '')
                            <a href="{{ $webHref }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex items-center gap-1 text-sm font-medium text-admin-primary hover:underline">{{ $web }}<span class="text-xs opacity-70" aria-hidden="true">↗</span></a>
                        @else
                            <p class="mt-1 text-sm text-admin-muted">Not set</p>
                        @endif
                    </div>
                </div>
            </article>

            {{-- Location --}}
            <article class="admin-card overflow-hidden p-0">
                <div class="border-b border-admin-border bg-slate-50/80 px-5 py-3">
                    <h3 class="text-sm font-semibold text-admin-ink">Location</h3>
                </div>
                <div class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Address</p>
                    @if(($c['address'] ?? '') !== '')
                        <p class="mt-1 whitespace-pre-line text-sm text-admin-ink">{{ $c['address'] }}</p>
                    @else
                        <p class="mt-1 text-sm text-admin-muted">Not set</p>
                    @endif
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-admin-muted">City &amp; country</p>
                    <p class="mt-1 text-sm text-admin-ink">{{ $locLine !== '' ? $locLine : '—' }}</p>
                </div>
            </article>

            {{-- Compliance --}}
            <article class="admin-card overflow-hidden p-0">
                <div class="border-b border-admin-border bg-slate-50/80 px-5 py-3">
                    <h3 class="text-sm font-semibold text-admin-ink">Billing &amp; compliance</h3>
                </div>
                <div class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">KRA PIN / tax ID</p>
                    <p class="mt-1 font-mono text-sm text-admin-ink">{{ ($c['tax_pin'] ?? '') !== '' ? $c['tax_pin'] : '—' }}</p>
                </div>
            </article>

            {{-- Notes --}}
            <article class="admin-card overflow-hidden p-0">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-admin-border bg-amber-50/50 px-5 py-3">
                    <div>
                        <h3 class="text-sm font-semibold text-admin-ink">Internal notes</h3>
                        <p class="text-xs text-admin-muted">Visible to admins only — not shown to the client.</p>
                    </div>
                    <a href="{{ route('admin.clients.edit', $c['id']) }}" class="text-xs font-semibold text-admin-primary hover:underline">Edit notes</a>
                </div>
                <div class="p-5">
                    @if(($c['notes'] ?? '') !== '')
                        <p class="whitespace-pre-line text-sm leading-relaxed text-admin-ink">{{ $c['notes'] }}</p>
                    @else
                        <p class="text-sm text-admin-muted">No notes yet. <a href="{{ route('admin.clients.edit', $c['id']) }}" class="font-medium text-admin-primary hover:underline">Add notes when editing</a>.</p>
                    @endif
                </div>
            </article>
        </div>

        {{-- Sidebar CTAs --}}
        <aside class="space-y-4">
            <article class="admin-card p-5">
                <h3 class="admin-card-title mb-3 text-sm">Quick actions</h3>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('admin.clients.edit', $c['id']) }}" class="admin-btn-primary w-full justify-center">Edit client</a>
                    @if($email !== '')
                        <a href="mailto:{{ $email }}" class="admin-btn-soft w-full justify-center">Send email</a>
                    @endif
                    @if($phone !== '')
                        <a href="{{ $tel($phone) }}" class="admin-btn-soft w-full justify-center">Call primary</a>
                    @endif
                    @if($phoneAlt !== '')
                        <a href="{{ $tel($phoneAlt) }}" class="admin-btn-soft w-full justify-center">Call alternate</a>
                    @endif
                    @if($webHref !== '')
                        <a href="{{ $webHref }}" target="_blank" rel="noopener noreferrer" class="admin-btn-soft w-full justify-center">Open website</a>
                    @endif
                </div>
            </article>

            <article class="admin-card p-5">
                <h3 class="admin-card-title mb-3 text-sm">Related work</h3>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('admin.cases.create') }}" class="admin-btn-soft w-full justify-center">New case</a>
                    <a href="{{ route('admin.billing.module.create', 'invoices') }}" class="admin-btn-soft w-full justify-center">New invoice</a>
                    <a href="{{ route('admin.billing.module.create', 'payments') }}" class="admin-btn-soft w-full justify-center">Record payment</a>
                    <a href="{{ route('admin.clients.create') }}" class="admin-btn-soft w-full justify-center">Add another client</a>
                </div>
            </article>

            <article class="admin-card p-5">
                <h3 class="admin-card-title mb-3 text-sm">Directory</h3>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('admin.clients') }}" class="admin-btn-soft w-full justify-center">All clients</a>
                    <a href="{{ route('admin.clients.delete-confirm', $c['id']) }}" class="admin-btn-soft w-full justify-center !border-rose-200 !text-rose-700 hover:!bg-rose-50">Delete client</a>
                </div>
            </article>
        </aside>
    </div>
</section>
@endsection
