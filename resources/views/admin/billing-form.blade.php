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
                        @if($module === 'demand')
                            <p class="mt-2 rounded-lg border border-emerald-200/80 bg-emerald-50/80 px-3 py-2 text-sm text-emerald-950">
                                The <strong>engaging client</strong> is who instructs you. Write the full letter — including recipient, address, and salutation if needed — in <strong>Letter (body)</strong>.
                            </p>
                        @endif
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach($meta['fields'] as $field)
                            <div class="{{ ($field['type'] ?? 'text') === 'textarea' ? 'md:col-span-2' : '' }}">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">{{ $field['label'] }}</label>
                                @if($field['name'] === 'client')
                                    <select class="admin-select" name="{{ $field['name'] }}" data-no-autolabel="true">
                                        <option value="">{{ $module === 'demand' ? 'Select engaging client' : 'Select client' }}</option>
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
                                @elseif(($field['type'] ?? 'text') === 'textarea')
                                    <textarea
                                        class="admin-input min-h-32"
                                        name="{{ $field['name'] }}"
                                        placeholder="{{ $module === 'demand' && $field['name'] === 'body' ? 'Full letter text: recipient, address, salutation, and paragraphs as needed.' : 'Enter '.strtolower($field['label']) }}"
                                        data-no-autolabel="true"
                                    >{{ old($field['name'], $values[$field['name']] ?? '') }}</textarea>
                                @else
                                    <input
                                        class="admin-input"
                                        type="{{ $field['type'] ?? 'text' }}"
                                        name="{{ $field['name'] }}"
                                        value="{{ old($field['name'], $values[$field['name']] ?? '') }}"
                                        placeholder="Enter {{ strtolower($field['label']) }}"
                                        data-no-autolabel="true"
                                    />
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

            <div class="sticky bottom-3 z-10 flex justify-end gap-2">
                <div class="flex gap-2 rounded-xl border border-admin-border bg-white/95 p-2 shadow-lg backdrop-blur">
                    <a href="{{ route('admin.billing') }}" class="admin-btn-soft">Cancel</a>
                    <button type="submit" class="admin-btn-primary">{{ $mode === 'create' ? 'Create' : 'Update' }} {{ $meta['singular'] }}</button>
                </div>
            </div>
    </form>
</section>
@endsection
