@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Documents</h2>
                <p class="mt-1 text-sm text-admin-muted">Invoice previews, PDF letterhead, and payment remittance details for Management billing.</p>
            </div>
            <span class="admin-chip">Billing outputs</span>
        </div>
    </div>

    @include('admin.settings._nav')
    @include('admin.settings._status')

    <form method="POST" action="{{ route('admin.settings.documents.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <article class="admin-card p-5 space-y-4">
            <p class="text-xs text-admin-muted">Company <strong>KRA PIN</strong> is managed under <a href="{{ route('admin.settings.company') }}" class="font-semibold underline decoration-slate-400 underline-offset-2 hover:text-admin-ink">Company settings</a>.</p>
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Letterhead &amp; footer (PDF / preview)</p>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Website (document footer)</label>
                        <input class="admin-input" name="document_website" value="{{ old('document_website', $settings['document_website'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Phones (footer)</label>
                        <input class="admin-input" name="document_phones" placeholder="0720 … / 0712 …" value="{{ old('document_phones', $settings['document_phones'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Address lines (one per line)</label>
                        <textarea class="admin-input min-h-28" name="document_address_lines" rows="4">{{ old('document_address_lines', $settings['document_address_lines'] ?? '') }}</textarea>
                    </div>
                    <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-admin-muted">Letterhead background (optional)</p>
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
                            <input class="admin-input" name="invoice_vat_rate" value="{{ old('invoice_vat_rate', $settings['invoice_vat_rate'] ?? '') }}" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Currency label</label>
                            <input class="admin-input" name="invoice_currency" value="{{ old('invoice_currency', $settings['invoice_currency'] ?? '') }}" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">VAT line label</label>
                        <input class="admin-input" name="invoice_vat_label" value="{{ old('invoice_vat_label', $settings['invoice_vat_label'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Payment block title</label>
                        <input class="admin-input" name="invoice_payment_title" value="{{ old('invoice_payment_title', $settings['invoice_payment_title'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Bank section heading</label>
                        <input class="admin-input" name="invoice_bank_heading" value="{{ old('invoice_bank_heading', $settings['invoice_bank_heading'] ?? '') }}" />
                    </div>
                    <div class="rounded-lg border border-admin-border bg-white p-3 space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Bank remittance</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Account name</label>
                                <input class="admin-input" name="remittance_account_name" value="{{ old('remittance_account_name', $settings['remittance_account_name'] ?? '') }}" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Account number</label>
                                <input class="admin-input" name="remittance_account_number" value="{{ old('remittance_account_number', $settings['remittance_account_number'] ?? '') }}" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Bank</label>
                                <input class="admin-input" name="remittance_bank" value="{{ old('remittance_bank', $settings['remittance_bank'] ?? '') }}" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Branch</label>
                                <input class="admin-input" name="remittance_branch" value="{{ old('remittance_branch', $settings['remittance_branch'] ?? '') }}" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Swift code</label>
                                <input class="admin-input" name="remittance_swift_code" value="{{ old('remittance_swift_code', $settings['remittance_swift_code'] ?? '') }}" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Bank code</label>
                                <input class="admin-input" name="remittance_bank_code" value="{{ old('remittance_bank_code', $settings['remittance_bank_code'] ?? '') }}" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Branch code</label>
                                <input class="admin-input" name="remittance_branch_code" value="{{ old('remittance_branch_code', $settings['remittance_branch_code'] ?? '') }}" />
                            </div>
                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Payment reference note</label>
                                <input class="admin-input" name="remittance_reference_line" value="{{ old('remittance_reference_line', $settings['remittance_reference_line'] ?? '') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Other payments heading</label>
                        <input class="admin-input" name="invoice_other_heading" value="{{ old('invoice_other_heading', $settings['invoice_other_heading'] ?? '') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Other payment lines</label>
                        <textarea class="admin-input min-h-16" name="invoice_payment_other_lines" rows="3">{{ old('invoice_payment_other_lines', $settings['invoice_payment_other_lines'] ?? '') }}</textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Payment note</label>
                        <textarea class="admin-input min-h-20" name="invoice_payment_note" rows="3">{{ old('invoice_payment_note', $settings['invoice_payment_note'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="admin-btn-primary">Save document settings</button>
            </div>
        </article>
    </form>
</section>
@endsection
