@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Company</h2>
                <p class="mt-1 text-sm text-admin-muted">Core business identity and public contact details shown across the website.</p>
            </div>
            <span class="admin-chip">Website profile</span>
        </div>
    </div>

    @include('admin.settings._nav')
    @include('admin.settings._status')

    <form method="POST" action="{{ route('admin.settings.company.update') }}" class="space-y-6">
        @csrf
        <article class="admin-card p-5 space-y-4 max-w-4xl">
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Company Name</label>
                    <input class="admin-input" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Tagline</label>
                    <input class="admin-input" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline'] ?? '') }}" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Email</label>
                    <input class="admin-input" name="company_email" type="email" value="{{ old('company_email', $settings['company_email'] ?? '') }}" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Primary phone</label>
                    <input class="admin-input" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Alternate phone</label>
                    <input class="admin-input" name="company_phone_alt" value="{{ old('company_phone_alt', $settings['company_phone_alt'] ?? '') }}" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">KRA PIN</label>
                    <input class="admin-input" name="company_kra_pin" value="{{ old('company_kra_pin', $settings['company_kra_pin'] ?? '') }}" />
                    <p class="text-xs text-admin-muted">Used on printable documents and billing outputs.</p>
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Address</label>
                    <textarea class="admin-input min-h-20" name="company_address" rows="3" data-no-editor="true">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Map embed URL</label>
                    <textarea class="admin-input min-h-16 font-mono text-xs" name="company_map_embed_url" rows="2">{{ old('company_map_embed_url', $settings['company_map_embed_url'] ?? '') }}</textarea>
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Domain</label>
                    <input class="admin-input" name="company_domain" value="{{ old('company_domain', $settings['company_domain'] ?? '') }}" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Description</label>
                    <textarea class="admin-input min-h-24" name="company_description">{{ old('company_description', $settings['company_description'] ?? '') }}</textarea>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="admin-btn-primary">Save company settings</button>
            </div>
        </article>
    </form>
</section>
@endsection
