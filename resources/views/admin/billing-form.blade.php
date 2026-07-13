@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">{{ $mode === 'create' ? 'Create' : 'Edit' }} {{ $meta['singular'] }}</h2>
                <p class="text-sm text-admin-muted">{{ $meta['description'] }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="admin-chip">{{ strtoupper($module) }}</span>
                @if($mode === 'edit')
                    <a href="{{ route('admin.billing.module.preview', [$module, $recordId]) }}" class="admin-btn-soft">Preview</a>
                @endif
                <a href="{{ route('admin.billing.module.index', $module) }}" class="admin-btn-soft">View All</a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $mode === 'create' ? route('admin.billing.module.store', $module) : route('admin.billing.module.update', [$module, $recordId]) }}" class="space-y-6">
            @csrf
            @if($mode === 'edit')
                @method('PATCH')
            @endif

            <div class="grid gap-6 xl:grid-cols-12">
                <article class="admin-card p-6 xl:col-span-8">
                    <div class="mb-4">
                        <h3 class="admin-card-title text-base">Document Details</h3>
                        <p class="mt-1 text-xs text-admin-muted">Fill all required fields to generate a complete {{ strtolower($meta['singular']) }} record.</p>
                        @if($module === 'fee-notes')
                            <p class="mt-2 rounded-lg border border-sky-200/80 bg-sky-50/80 px-3 py-2 text-sm text-sky-950">
                                <strong>Save draft</strong> keeps all details without using the next FN number; finish later, then click <strong>Issue fee note (assign FN number)</strong> on the edit screen.
                                Or click <strong>Issue fee note</strong> on create to assign FN-… immediately. Fill <strong>reference details</strong> → <strong>client particulars</strong> → <strong>fee computation</strong> → optional notes.
                                Bank remittance lines on the printed fee note come from <a href="{{ route('admin.settings.documents') }}" class="font-semibold underline decoration-sky-700/50 underline-offset-2 hover:text-sky-900">Admin → Settings → Documents</a> (bank lines), so you do not re-enter them here.
                            </p>
                        @endif
                        @if($module === 'demand')
                            <p class="mt-2 rounded-lg border border-emerald-200/80 bg-emerald-50/80 px-3 py-2 text-sm text-emerald-950">
                                The <strong>engaging client</strong> is who instructs you. Write the full letter — including recipient, address, and salutation if needed — in <strong>Letter (body)</strong>.
                            </p>
                        @endif
                    </div>

                    @php
                        $feeNoteSectionStarts = [
                            'number' => ['title' => 'Reference Details', 'desc' => 'Core identifiers and issuance details. Service + client auto-build Our Ref (e.g. 1/001/2026).'],
                            'address' => ['title' => 'Client Particulars', 'desc' => 'Use the full recipient block exactly as it should appear on the fee note.'],
                            'line_description' => ['title' => 'Fee Computation', 'desc' => 'Enter the rendered service and tax inputs used to compute totals.'],
                            'notes' => ['title' => 'Closing notes', 'desc' => 'Optional text at the foot of the fee note. Bank remittance details use Settings → bank lines (same as invoices).'],
                        ];
                        $feeNoteFieldHelp = [
                            'service_id' => 'Service number is the first part of Our Ref (from Admin → Services).',
                            'our_ref' => 'Auto-generated as {service}/{account}/{year} e.g. 1/001/2026 when service and client are selected.',
                            'your_ref' => 'Client reference if provided.',
                            'payment_terms' => 'Example: IMMEDIATE, 7 DAYS, 14 DAYS.',
                            'line_description' => 'This text appears in the particulars row in the table.',
                            'apply_vat' => 'Choose With VAT to add the configured rate (default 16%), or Without VAT for fee only.',
                            'address' => 'After you save a fee note, this address is remembered for the same client — pick the client again on a new fee note to auto-fill.',
                        ];
                        $feeNotePlaceholders = [
                            'our_ref' => 'e.g. 1/001/2026',
                            'your_ref' => 'e.g. 4523',
                            'payment_terms' => 'e.g. IMMEDIATE',
                            'line_description' => 'Professional fees for debt collection ...',
                            'amount' => 'e.g. 5321.60',
                        ];
                    @endphp
                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach($meta['fields'] as $field)
                            @if($module === 'fee-notes' && isset($feeNoteSectionStarts[$field['name']]))
                                <div class="md:col-span-2 mt-2 rounded-lg border border-admin-border bg-slate-50 px-3 py-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-admin-ink">{{ $feeNoteSectionStarts[$field['name']]['title'] }}</p>
                                    <p class="mt-0.5 text-xs text-admin-muted">{{ $feeNoteSectionStarts[$field['name']]['desc'] }}</p>
                                </div>
                            @endif
                            <div class="{{ ($field['type'] ?? 'text') === 'textarea' ? 'md:col-span-2' : '' }}">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">{{ $field['label'] }}</label>
                                @if($module === 'fee-notes' && $field['name'] === 'service_id')
                                    @php
                                        $currentServiceId = (string) old('service_id', $values['service_id'] ?? '');
                                        $serviceOptions = $feeNoteServices ?? [];
                                    @endphp
                                    <select class="admin-select" name="service_id" id="fee-note-service-id" data-no-autolabel="true">
                                        <option value="">Select service</option>
                                        @foreach($serviceOptions as $svc)
                                            <option value="{{ $svc['id'] }}" @selected($currentServiceId === (string) $svc['id'])>{{ $svc['name'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif($module === 'fee-notes' && $field['name'] === 'our_ref')
                                    <input
                                        class="admin-input cursor-not-allowed bg-slate-50 font-mono text-admin-ink"
                                        type="text"
                                        name="our_ref"
                                        id="fee-note-our-ref"
                                        value="{{ old('our_ref', $values['our_ref'] ?? '') }}"
                                        readonly
                                        data-no-autolabel="true"
                                        placeholder="Select service and client"
                                    />
                                @elseif($field['name'] === 'client')
                                    <select class="admin-select" name="{{ $field['name'] }}" id="fee-note-client" data-no-autolabel="true">
                                        <option value="">
                                            {{ $module === 'demand' ? 'Select engaging client' : ($module === 'fee-notes' ? 'Select client organization' : 'Select client') }}
                                        </option>
                                        @foreach(($clients ?? []) as $client)
                                            <option value="{{ $client }}" @selected(old($field['name'], $values[$field['name']] ?? '') === $client)>{{ $client }}</option>
                                        @endforeach
                                    </select>
                                @elseif($module === 'demand' && $field['name'] === 'case_ref')
                                    @php
                                        $currentCaseRef = (string) old($field['name'], $values[$field['name']] ?? '');
                                        $caseRefOptions = $caseReferences ?? [];
                                        if ($currentCaseRef !== '' && ! in_array($currentCaseRef, $caseRefOptions, true)) {
                                            $caseRefOptions = array_values(array_unique(array_merge([$currentCaseRef], $caseRefOptions)));
                                            sort($caseRefOptions);
                                        }
                                    @endphp
                                    <select class="admin-select" name="{{ $field['name'] }}" data-no-autolabel="true">
                                        <option value="">Select case reference</option>
                                        @foreach($caseRefOptions as $ref)
                                            <option value="{{ $ref }}" @selected($currentCaseRef === $ref)>{{ $ref }}</option>
                                        @endforeach
                                    </select>
                                @elseif($module === 'payments' && $mode === 'create' && $field['name'] === 'payment_id')
                                    <input
                                        class="admin-input cursor-not-allowed bg-slate-50 text-admin-ink"
                                        type="text"
                                        name="{{ $field['name'] }}"
                                        value="{{ old($field['name'], $values[$field['name']] ?? '') }}"
                                        readonly
                                        data-no-autolabel="true"
                                    />
                                    <p class="mt-1 text-xs text-admin-muted">Auto-generated on save (PM-YEAR-####). Shown here as the next available payment ID.</p>
                                @elseif($module === 'payments' && $field['name'] === 'invoice')
                                    @php
                                        $currentInvoiceNum = (string) old($field['name'], $values[$field['name']] ?? '');
                                        $currency = \App\Support\AdminStoredSettings::invoice()['currency'] ?? config('colldett.invoice.currency', 'Ksh');
                                        $invoiceOpts = $invoiceOptions ?? [];
                                        $numbersInIndex = [];
                                        foreach ($invoiceOpts as $opt) {
                                            if (is_array($opt) && ($opt['number'] ?? '') !== '') {
                                                $numbersInIndex[$opt['number']] = true;
                                            }
                                        }
                                        if ($currentInvoiceNum !== '' && ! isset($numbersInIndex[$currentInvoiceNum])) {
                                            $clientExtra = trim((string) old('client', $values['client'] ?? ''));
                                            $amtRaw = (string) old('amount', $values['amount'] ?? '');
                                            $amtDisp = $amtRaw !== '' && is_numeric($amtRaw)
                                                ? $currency.' '.number_format((float) $amtRaw, 2, '.', ',')
                                                : ($amtRaw !== '' ? $amtRaw : '—');
                                            $clientDisp = $clientExtra !== '' ? $clientExtra : '—';
                                            array_unshift($invoiceOpts, [
                                                'number' => $currentInvoiceNum,
                                                'label' => $currentInvoiceNum.' — '.$clientDisp.' — '.$amtDisp,
                                            ]);
                                        }
                                    @endphp
                                    <select class="admin-select" name="{{ $field['name'] }}" data-no-autolabel="true">
                                        <option value="">Select invoice number</option>
                                        @foreach($invoiceOpts as $opt)
                                            @php
                                                $invNo = is_array($opt) ? (string) ($opt['number'] ?? '') : (string) $opt;
                                                $invLabel = is_array($opt) ? (string) ($opt['label'] ?? $invNo) : (string) $opt;
                                            @endphp
                                            <option value="{{ $invNo }}" @selected($currentInvoiceNum === $invNo)>{{ $invLabel }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-admin-muted">Choose the invoice this payment applies to (number, client, and amount from issued invoices).</p>
                                @elseif(in_array($module, ['invoices', 'quotations'], true) && $field['name'] === 'number' && $mode === 'create')
                                    <input
                                        class="admin-input cursor-not-allowed bg-slate-50 text-admin-ink"
                                        type="text"
                                        name="{{ $field['name'] }}"
                                        value="{{ old($field['name'], $values[$field['name']] ?? '') }}"
                                        readonly
                                        data-no-autolabel="true"
                                    />
                                    <p class="mt-1 text-xs text-admin-muted">
                                        @if($module === 'invoices')
                                            Auto-generated on save (INV-YEAR-####). Shown here as the next available number.
                                        @else
                                            Auto-generated on save (QTN-YEAR-####). Shown here as the next available number.
                                        @endif
                                    </p>
                                @elseif($module === 'fee-notes' && $field['name'] === 'number' && $mode === 'create')
                                    <input
                                        class="admin-input cursor-not-allowed bg-slate-50 text-admin-ink"
                                        type="text"
                                        name="{{ $field['name'] }}"
                                        value="{{ old($field['name'], $values[$field['name']] ?? '') }}"
                                        readonly
                                        data-no-autolabel="true"
                                    />
                                    <p class="mt-1 text-xs text-admin-muted">
                                        <strong>Save draft</strong> does not use this number yet. <strong>Issue fee note</strong> assigns the next FN-YEAR-#### and opens your printable fee note.
                                    </p>
                                @elseif($module === 'fee-notes' && $field['name'] === 'number' && $mode === 'edit')
                                    <input
                                        class="admin-input cursor-not-allowed bg-slate-50 text-admin-ink"
                                        type="text"
                                        name="{{ $field['name'] }}"
                                        value="{{ old($field['name'], $values[$field['name']] ?? '') }}"
                                        readonly
                                        data-no-autolabel="true"
                                    />
                                    <p class="mt-1 text-xs text-admin-muted">
                                        @if($feeNoteIsDraft ?? false)
                                            No official number yet. Click <strong>Issue fee note</strong> below to assign FN-YEAR-#### and open the document.
                                        @else
                                            Official fee note number (locked).
                                        @endif
                                    </p>
                                @elseif(($field['type'] ?? 'text') === 'select' && $field['name'] === 'apply_vat')
                                    @php
                                        $applyVatCurrent = (string) old('apply_vat', $values['apply_vat'] ?? '1');
                                        if (! in_array($applyVatCurrent, ['0', '1'], true)) {
                                            $applyVatCurrent = \App\Support\DocumentVat::applies($values) ? '1' : '0';
                                        }
                                    @endphp
                                    <select class="admin-select" name="apply_vat" data-no-autolabel="true">
                                        @foreach(($field['options'] ?? ['1' => 'With VAT (16%)', '0' => 'Without VAT']) as $optValue => $optLabel)
                                            <option value="{{ $optValue }}" @selected($applyVatCurrent === (string) $optValue)>{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-admin-muted">With VAT adds the rate from document settings (default 16%). Without VAT, the total equals the amount entered.</p>
                                @elseif(($field['type'] ?? 'text') === 'textarea')
                                    <textarea
                                        class="admin-input min-h-32"
                                        name="{{ $field['name'] }}"
                                        placeholder="{{ $module === 'demand' && $field['name'] === 'body' ? 'Full letter text: recipient, address, salutation, and paragraphs as needed.' : ($module === 'fee-notes' ? ($feeNotePlaceholders[$field['name']] ?? 'Enter '.strtolower($field['label'])) : 'Enter '.strtolower($field['label'])) }}"
                                        data-no-autolabel="true"
                                        data-no-editor="true"
                                    >{{ \App\Support\DocumentPlainText::fromHtml((string) old($field['name'], $values[$field['name']] ?? '')) }}</textarea>
                                @else
                                    <input
                                        class="admin-input"
                                        type="{{ $field['type'] ?? 'text' }}"
                                        name="{{ $field['name'] }}"
                                        value="{{ old($field['name'], $values[$field['name']] ?? '') }}"
                                        placeholder="{{ $module === 'fee-notes' ? ($feeNotePlaceholders[$field['name']] ?? 'Enter '.strtolower($field['label'])) : 'Enter '.strtolower($field['label']) }}"
                                        data-no-autolabel="true"
                                    />
                                @endif
                                @if($module === 'fee-notes' && isset($feeNoteFieldHelp[$field['name']]))
                                    <p class="mt-1 text-xs text-admin-muted">{{ $feeNoteFieldHelp[$field['name']] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </article>

                <aside class="admin-card p-5 xl:col-span-4 space-y-4">
                    <h3 class="admin-card-title">Workflow Tips</h3>
                    <ul class="space-y-2 text-sm text-admin-muted">
                        <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Use consistent numbering format for easier tracking.</li>
                        @if($module === 'demand')
                            <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Use <strong>Subject</strong> and <strong>Letter (body)</strong> for the wording; put recipient lines in the body when you need them.</li>
                        @else
                            <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Confirm client details before creating legal/financial documents.</li>
                        @endif
                        @if($module === 'fee-notes')
                            <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Use <strong>Save draft</strong> to record details before you are ready for an official FN number.</li>
                        @endif
                        <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Add concise notes for future audit and reconciliation.</li>
                    </ul>
                    <div class="rounded-lg border border-dashed border-admin-border bg-slate-50 p-3 text-xs text-admin-muted">
                        Mode: <strong>{{ $mode === 'create' ? 'Create New' : 'Edit Existing' }}</strong><br>
                        Template: <strong>{{ $meta['singular'] }}</strong>
                    </div>
                </aside>
            </div>

            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="sticky bottom-3 z-10 flex flex-wrap justify-end gap-2">
                <div class="flex flex-wrap items-center gap-2 rounded-xl border border-admin-border bg-white/95 p-2 shadow-lg backdrop-blur">
                    <a href="{{ route('admin.billing') }}" class="admin-btn-soft">Cancel</a>
                    @if($module === 'fee-notes' && $mode === 'create')
                        <button type="submit" name="fee_note_action" value="issue" class="admin-btn-primary">Issue fee note</button>
                        <button type="submit" name="fee_note_action" value="draft" class="admin-btn-soft">Save draft</button>
                    @else
                        <button type="submit" class="admin-btn-primary">{{ $mode === 'create' ? 'Create' : 'Update' }} {{ $meta['singular'] }}</button>
                    @endif
                </div>
            </div>
    </form>

    @if($module === 'fee-notes' && $mode === 'edit' && ($feeNoteIsDraft ?? false))
        <div class="sticky bottom-16 z-10 flex justify-end">
            <form method="POST" action="{{ route('admin.billing.fee-notes.finalize', $recordId) }}" class="rounded-xl border border-admin-border bg-white/95 p-2 shadow-lg backdrop-blur" onsubmit="return confirm('Assign the next official fee note number (FN-…)? This cannot be undone.')">
                @csrf
                <button type="submit" class="admin-btn-primary">Issue fee note (assign FN number)</button>
            </form>
        </div>
    @endif
</section>
@if($module === 'fee-notes')
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const map = @json($feeNoteAddressByClient ?? []);
                const accountRefs = @json($feeNoteClientAccountRefs ?? []);
                const clientSel = document.getElementById('fee-note-client') || document.querySelector('select[name="client"]');
                const serviceSel = document.getElementById('fee-note-service-id');
                const ourRefInput = document.getElementById('fee-note-our-ref');
                const issuedDateInput = document.querySelector('input[name="issued_date"]');
                const addr = document.querySelector('textarea[name="address"]');

                const buildOurRef = function () {
                    if (!ourRefInput || !serviceSel || !clientSel) {
                        return;
                    }
                    const serviceId = (serviceSel.value || '').trim();
                    const company = (clientSel.value || '').trim();
                    if (!serviceId || !company) {
                        ourRefInput.value = '';
                        return;
                    }
                    const accountSeg = Object.prototype.hasOwnProperty.call(accountRefs, company)
                        ? accountRefs[company]
                        : '000';
                    let year = new Date().getFullYear();
                    if (issuedDateInput && issuedDateInput.value) {
                        const parsed = new Date(issuedDateInput.value);
                        if (!Number.isNaN(parsed.getTime())) {
                            year = parsed.getFullYear();
                        }
                    }
                    ourRefInput.value = serviceId + '/' + accountSeg + '/' + year;
                };

                if (serviceSel) {
                    serviceSel.addEventListener('change', buildOurRef);
                }
                if (issuedDateInput) {
                    issuedDateInput.addEventListener('change', buildOurRef);
                }

                if (clientSel && addr && typeof map === 'object' && map !== null) {
                    const fillAddressFor = function (company) {
                        if (!company || !Object.prototype.hasOwnProperty.call(map, company)) {
                            return;
                        }
                        const next = map[company];
                        if (typeof next !== 'string' || next.trim() === '') {
                            return;
                        }
                        addr.value = next;
                    };
                    clientSel.addEventListener('change', function () {
                        fillAddressFor((clientSel.value || '').trim());
                        buildOurRef();
                    });
                    if ((addr.value || '').trim() === '') {
                        fillAddressFor((clientSel.value || '').trim());
                    }
                } else if (clientSel) {
                    clientSel.addEventListener('change', buildOurRef);
                }

                buildOurRef();
            });
        </script>
    @endpush
@endif
@endsection
