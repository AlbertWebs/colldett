@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">System Settings</h2>
                <p class="mt-1 text-sm text-admin-muted">Update company profile, branding assets, social channels, and operational preferences.</p>
            </div>
            <span class="admin-chip">Admin Configuration</span>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid gap-6 xl:grid-cols-12">
            <article class="admin-card p-5 space-y-4 xl:col-span-7">
                <div>
                    <h3 class="admin-card-title text-base">General Settings</h3>
                    <p class="mt-1 text-xs text-admin-muted">Core business identity shown across the website and metadata.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Company Name</label>
                        <input class="admin-input" name="company_name" placeholder="Company Name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Tagline</label>
                        <input class="admin-input" name="company_tagline" placeholder="Company Tagline" value="{{ old('company_tagline', $settings['company_tagline'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Email</label>
                        <input class="admin-input" name="company_email" placeholder="Company Email" value="{{ old('company_email', $settings['company_email'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Phone</label>
                        <input class="admin-input" name="company_phone" placeholder="Company Phone" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Address</label>
                        <textarea class="admin-input min-h-20" name="company_address" placeholder="Company Address" rows="3">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Map embed URL</label>
                        <textarea class="admin-input min-h-16 font-mono text-xs" name="company_map_embed_url" placeholder="https://www.google.com/maps/embed?... or maps output=embed URL" rows="2">{{ old('company_map_embed_url', $settings['company_map_embed_url'] ?? '') }}</textarea>
                        <p class="text-xs text-admin-muted">Used on the public Contact page map. Paste the iframe <code class="rounded bg-slate-100 px-1">src</code> URL only.</p>
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Domain</label>
                        <input class="admin-input" name="company_domain" placeholder="Company Domain" value="{{ old('company_domain', $settings['company_domain'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Description</label>
                        <textarea class="admin-input min-h-24" name="company_description" placeholder="Company Description">{{ old('company_description', $settings['company_description'] ?? '') }}</textarea>
                    </div>
                </div>
            </article>

            <article class="admin-card p-5 space-y-4 xl:col-span-5">
                <div>
                    <h3 class="admin-card-title text-base">Branding Settings</h3>
                    <p class="mt-1 text-xs text-admin-muted">Upload visual assets used in header, footer, and browser tab.</p>
                </div>
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

            <article class="admin-card p-5 space-y-4 xl:col-span-7">
                <div>
                    <h3 class="admin-card-title text-base">Contact & Social Settings</h3>
                    <p class="mt-1 text-xs text-admin-muted">Public channels visitors use to connect with your business.</p>
                </div>
                <div class="rounded-lg border border-admin-border bg-slate-50 p-3 text-xs text-admin-muted">
                    Contact fields are managed in General Settings above.
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Facebook</label>
                        <input class="admin-input" name="social_facebook" placeholder="https://facebook.com/..." value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">X (Twitter)</label>
                        <input class="admin-input" name="social_twitter" placeholder="https://x.com/..." value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">LinkedIn</label>
                        <input class="admin-input" name="social_linkedin" placeholder="https://linkedin.com/company/..." value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Instagram</label>
                        <input class="admin-input" name="social_instagram" placeholder="https://instagram.com/..." value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">YouTube</label>
                        <input class="admin-input" name="social_youtube" placeholder="https://youtube.com/@..." value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" />
                    </div>
                </div>
            </article>

            <article class="admin-card p-5 space-y-4 xl:col-span-12">
                <div>
                    <h3 class="admin-card-title text-base">Invoices & printable documents</h3>
                    <p class="mt-1 text-xs text-admin-muted">These values populate invoice previews, PDFs, and letterhead-style documents. If a field is left empty, defaults from configuration are used where applicable.</p>
                </div>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Letterhead &amp; footer (PDF / preview)</p>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Website (shown in document footer)</label>
                            <input class="admin-input" name="document_website" placeholder="www.example.co.ke" value="{{ old('document_website', $settings['document_website'] ?? '') }}" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Phones (footer)</label>
                            <input class="admin-input" name="document_phones" placeholder="0720 … / 0712 …" value="{{ old('document_phones', $settings['document_phones'] ?? '') }}" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Address lines (header, one per line)</label>
                            <textarea class="admin-input min-h-28" name="document_address_lines" placeholder="Line 1&#10;Line 2">{{ old('document_address_lines', $settings['document_address_lines'] ?? '') }}</textarea>
                        </div>
                        <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-admin-muted">Optional letterhead background (PNG)</p>
                            @if(!empty($settings['document_letterhead_path']))
                                <img src="{{ asset(ltrim($settings['document_letterhead_path'], '/')) }}" alt="Letterhead preview" class="mb-2 max-h-24 w-auto rounded border border-admin-border bg-white p-1" />
                            @endif
                            <input class="admin-input" type="file" name="document_letterhead_file" accept=".jpg,.jpeg,.png,.webp,image/*" />
                        </div>
                    </div>
                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Invoice amounts &amp; payment block</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">VAT rate (%)</label>
                                <input class="admin-input" name="invoice_vat_rate" type="text" inputmode="decimal" placeholder="16" value="{{ old('invoice_vat_rate', $settings['invoice_vat_rate'] ?? '') }}" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Currency label</label>
                                <input class="admin-input" name="invoice_currency" placeholder="Ksh" value="{{ old('invoice_currency', $settings['invoice_currency'] ?? '') }}" />
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">VAT line label</label>
                            <input class="admin-input" name="invoice_vat_label" placeholder="16.00% Kenyan VAT" value="{{ old('invoice_vat_label', $settings['invoice_vat_label'] ?? '') }}" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Payment block title</label>
                            <input class="admin-input" name="invoice_payment_title" placeholder="Payment Details" value="{{ old('invoice_payment_title', $settings['invoice_payment_title'] ?? '') }}" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Bank section heading</label>
                            <input class="admin-input" name="invoice_bank_heading" value="{{ old('invoice_bank_heading', $settings['invoice_bank_heading'] ?? '') }}" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Bank lines (one per line)</label>
                            <textarea class="admin-input min-h-28" name="invoice_payment_bank_lines" placeholder="Bank name&#10;Account name&#10;Account number">{{ old('invoice_payment_bank_lines', $settings['invoice_payment_bank_lines'] ?? '') }}</textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Other payments heading (optional)</label>
                            <input class="admin-input" name="invoice_other_heading" placeholder="PayPal / Cash" value="{{ old('invoice_other_heading', $settings['invoice_other_heading'] ?? '') }}" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Other payment lines (optional)</label>
                            <textarea class="admin-input min-h-16" name="invoice_payment_other_lines" placeholder="PayPal: pay@example.com">{{ old('invoice_payment_other_lines', $settings['invoice_payment_other_lines'] ?? '') }}</textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Payment note (below payment details)</label>
                            <textarea class="admin-input min-h-20" name="invoice_payment_note" placeholder="NB: Quote your invoice number…">{{ old('invoice_payment_note', $settings['invoice_payment_note'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </article>

            <article class="admin-card p-5 space-y-4 xl:col-span-5">
                <div>
                    <h3 class="admin-card-title text-base">Email & Document Settings</h3>
                    <p class="mt-1 text-xs text-admin-muted">Operational defaults for communication and finance-related outputs.</p>
                </div>
                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">SMTP Host</label>
                        <input class="admin-input" name="smtp_host" placeholder="SMTP Host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">SMTP Credentials</label>
                        <input class="admin-input" name="smtp_credentials" placeholder="SMTP Port / Username / Password" value="{{ old('smtp_credentials', $settings['smtp_credentials'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Document Prefixes</label>
                        <input class="admin-input" name="document_prefixes" placeholder="Invoice Prefix / Receipt Prefix / Quotation Prefix" value="{{ old('document_prefixes', $settings['document_prefixes'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Currency & Tax</label>
                        <input class="admin-input" name="currency_tax" placeholder="Currency / Tax Rate" value="{{ old('currency_tax', $settings['currency_tax'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Regional Preferences</label>
                        <input class="admin-input" name="regional_preferences" placeholder="Timezone / Date Format / Language / Pagination Size" value="{{ old('regional_preferences', $settings['regional_preferences'] ?? '') }}" />
                    </div>
                </div>
            </article>

            <article class="admin-card p-5 space-y-4 xl:col-span-5">
                <div>
                    <h3 class="admin-card-title text-base">Dashboard Preferences</h3>
                    <p class="mt-1 text-xs text-admin-muted">Control admin navigation visibility for optional modules.</p>
                </div>
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

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="sticky bottom-3 z-10 flex justify-end">
            <div class="rounded-xl border border-admin-border bg-white/95 p-2 shadow-lg backdrop-blur">
                <button type="submit" class="admin-btn-primary">Save Settings</button>
            </div>
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
