@php
    $docRef = '#' . ($values['number'] ?? ($values['payment_id'] ?? ('REC-' . $recordId)));
    $isInvoice = $module === 'invoices';
    $isQuotation = $module === 'quotations';
    $isPayment = $module === 'payments';
    $isFeeNote = $module === 'fee-notes';
    $isDemand = $module === 'demand';
    $docTitle = $isInvoice ? 'Invoice' : ($isDemand ? 'Demand Letter' : ($isQuotation ? 'Quotation' : ($isPayment ? 'Payment receipt' : ($isFeeNote ? 'Fee Note' : $meta['singular'] . ' preview'))));
    $showPreviewFooterActions = $showPreviewFooterActions ?? false;
    $showLetterheadDocTitle = ! $isInvoice && ! $isQuotation && ! $isPayment;
@endphp
<x-colldett-document
    :document-title="$docTitle"
    :reference="$isInvoice || $isQuotation || $isPayment ? null : $docRef"
    :show-doc-title="$showLetterheadDocTitle"
>
    @if($isInvoice)
        @include('admin.partials.invoice-document-content', ['values' => $values])
    @elseif($isQuotation)
        @include('admin.partials.quotation-document-content', ['values' => $values])
    @elseif($isPayment)
        @include('admin.partials.payment-receipt-document-content', ['values' => $values])
    @elseif($isFeeNote)
        @include('admin.partials.fee-note-document-content', ['values' => $values])
    @elseif($isDemand)
        @include('admin.partials.demand-letter-preview', ['values' => $values])
    @else
        <table class="colldett-document-table">
            <thead>
                <tr>
                    <th style="width: 32%;">Field</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meta['fields'] as $field)
                    @php
                        $value = $values[$field['name']] ?? '—';
                    @endphp
                    <tr>
                        <td>{{ $field['label'] }}</td>
                        <td>{!! nl2br(e((string) $value)) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($showPreviewFooterActions)
        <div class="mt-6 flex flex-wrap justify-end gap-2 no-print">
            <a href="{{ route('admin.billing.module.create', $module) }}" class="admin-btn-soft">Create Another</a>
            <a href="{{ route('admin.billing.module.edit', [$module, $recordId]) }}" class="admin-btn-primary">Edit Document</a>
        </div>
    @endif
</x-colldett-document>
