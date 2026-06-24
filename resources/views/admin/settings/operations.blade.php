@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Operations</h2>
                <p class="mt-1 text-sm text-admin-muted">Email delivery defaults, document numbering, and admin panel preferences.</p>
            </div>
            <span class="admin-chip">Back office</span>
        </div>
    </div>

    @include('admin.settings._nav')
    @include('admin.settings._status')

    <form method="POST" action="{{ route('admin.settings.operations.update') }}" class="space-y-6">
        @csrf
        <div class="grid gap-6 lg:grid-cols-2">
            <article class="admin-card p-5 space-y-4">
                <h3 class="admin-card-title text-base">Email &amp; documents</h3>
                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">SMTP Host</label>
                        <input class="admin-input" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">SMTP Credentials</label>
                        <input class="admin-input" name="smtp_credentials" placeholder="Port / username / password" value="{{ old('smtp_credentials', $settings['smtp_credentials'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Document Prefixes</label>
                        <input class="admin-input" name="document_prefixes" placeholder="Invoice / receipt / quotation prefixes" value="{{ old('document_prefixes', $settings['document_prefixes'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Currency &amp; Tax</label>
                        <input class="admin-input" name="currency_tax" value="{{ old('currency_tax', $settings['currency_tax'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Regional Preferences</label>
                        <input class="admin-input" name="regional_preferences" placeholder="Timezone / date format / language / pagination" value="{{ old('regional_preferences', $settings['regional_preferences'] ?? '') }}" />
                    </div>
                </div>
            </article>

            <article class="admin-card p-5 space-y-4">
                <h3 class="admin-card-title text-base">Panel preferences</h3>
                <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                    <input type="hidden" name="show_reports_nav" value="0">
                    <label class="flex items-start gap-3">
                        <input
                            class="mt-0.5 h-4 w-4 rounded border-admin-border text-admin-primary focus:ring-admin-primary/30"
                            type="checkbox"
                            name="show_reports_nav"
                            value="1"
                            @checked(old('show_reports_nav', $settings['show_reports_nav'] ?? false))
                        />
                        <span>
                            <span class="block text-sm font-semibold text-admin-ink">Show Reports in sidebar</span>
                            <span class="mt-0.5 block text-xs text-admin-muted">When disabled, Reports is hidden from the left navigation menu.</span>
                        </span>
                    </label>
                </div>
            </article>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="admin-btn-primary">Save operations settings</button>
        </div>
    </form>
</section>
@endsection
