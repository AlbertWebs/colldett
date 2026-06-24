@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Branding</h2>
                <p class="mt-1 text-sm text-admin-muted">Logos, favicon, and social channels used on the public website.</p>
            </div>
            <span class="admin-chip">Visual identity</span>
        </div>
    </div>

    @include('admin.settings._nav')
    @include('admin.settings._status')

    <form method="POST" action="{{ route('admin.settings.branding.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="grid gap-6 lg:grid-cols-2">
            <article class="admin-card p-5 space-y-4">
                <h3 class="admin-card-title text-base">Brand assets</h3>
                <div class="grid gap-4">
                    <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-admin-muted">Company Logo</p>
                        <img src="{{ $settings['company_logo'] ?? '' }}" alt="Company logo preview" class="h-14 w-auto rounded border border-admin-border bg-white p-1" data-preview-image="company_logo_file">
                        <input class="admin-input mt-3" type="file" name="company_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg,image/*" data-preview-target="company_logo_file" />
                    </div>
                    <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-admin-muted">Footer Logo</p>
                        <img src="{{ $settings['footer_logo'] ?? '' }}" alt="Footer logo preview" class="h-14 w-auto rounded border border-admin-border bg-white p-1" data-preview-image="footer_logo_file">
                        <input class="admin-input mt-3" type="file" name="footer_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg,image/*" data-preview-target="footer_logo_file" />
                    </div>
                    <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-admin-muted">Favicon</p>
                        <img src="{{ $settings['favicon'] ?? '' }}" alt="Favicon preview" class="h-12 w-12 rounded border border-admin-border bg-white p-1 object-contain" data-preview-image="favicon_file">
                        <input class="admin-input mt-3" type="file" name="favicon_file" accept=".png,.ico,.webp,image/*" data-preview-target="favicon_file" />
                    </div>
                </div>
            </article>

            <article class="admin-card p-5 space-y-4">
                <h3 class="admin-card-title text-base">Social links</h3>
                <div class="grid gap-3">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Facebook</label>
                        <input class="admin-input" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">X (Twitter)</label>
                        <input class="admin-input" name="social_twitter" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">LinkedIn</label>
                        <input class="admin-input" name="social_linkedin" value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Instagram</label>
                        <input class="admin-input" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">YouTube</label>
                        <input class="admin-input" name="social_youtube" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" />
                    </div>
                </div>
            </article>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="admin-btn-primary">Save branding settings</button>
        </div>
    </form>
</section>

<script>
    document.querySelectorAll('[data-preview-target]').forEach((input) => {
        input.addEventListener('change', (event) => {
            const [file] = event.target.files || [];
            if (!file) return;
            const previewKey = event.target.getAttribute('data-preview-target');
            const previewImage = document.querySelector(`[data-preview-image="${previewKey}"]`);
            if (!previewImage) return;
            const fileReader = new FileReader();
            fileReader.onload = (loadEvent) => {
                previewImage.src = loadEvent.target?.result || previewImage.src;
            };
            fileReader.readAsDataURL(file);
        });
    });
</script>
@endsection
