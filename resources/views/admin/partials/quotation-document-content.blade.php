@php
    use App\Support\AdminStoredSettings;
    use Illuminate\Support\Carbon;
    $inv = AdminStoredSettings::invoice();
    $vatRate = (float) ($inv['vat_rate'] ?? 0.16);
    $vatLabel = $inv['vat_label'] ?? (number_format($vatRate * 100, 2) . '% VAT');
    $currency = $inv['currency'] ?? 'Ksh';
    $rawAmount = $values['amount'] ?? 0;
    $subtotal = is_numeric($rawAmount)
        ? (float) $rawAmount
        : (float) preg_replace('/[^\d.]/', '', (string) $rawAmount);
    $vat = round($subtotal * $vatRate, 2);
    $credit = 0.0;
    $total = round($subtotal + $vat - $credit, 2);
    $qDate = Carbon::now();
    $valid = isset($values['valid_until']) && $values['valid_until'] !== ''
        ? Carbon::parse($values['valid_until'])
        : Carbon::now()->addDays(30);
    $fmtMoney = fn (float $n): string => $currency . ' ' . number_format($n, 2, '.', ',');
    $pay = $inv['payment_details'] ?? [];
    $scope = trim((string) ($values['scope'] ?? ''));
    if ($scope === '') {
        $scope = 'Professional services — see engagement terms.';
    }
    $clientRaw = trim((string) ($values['client'] ?? ''));
    $quotedLines = $clientRaw !== ''
        ? preg_split("/\r\n|\r|\n/", $clientRaw)
        : array_filter(['Client', 'Address on file']);
    $pdfMode = $pdfMode ?? false;
@endphp

<div class="colldett-invoice {{ $pdfMode ? 'colldett-invoice--pdf' : '' }}">
    <div class="colldett-invoice__number-bar">
        Quotation #{{ $values['number'] ?? '—' }}
    </div>

    <div class="colldett-invoice__meta-row">
        <div class="colldett-invoice__main">
            <div class="colldett-invoice__dates">
                <div><span class="colldett-invoice__date-label">Quotation Date:</span> {{ $qDate->format('l, F jS, Y') }}</div>
                <div><span class="colldett-invoice__date-label">Valid Until:</span> {{ $valid->format('l, F jS, Y') }}</div>
            </div>
            <div class="colldett-invoice__to">
                <div class="colldett-invoice__to-title">Quoted To</div>
                <div class="colldett-invoice__to-lines">
                    @foreach($quotedLines as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="colldett-invoice__payment" aria-label="Payment details">
            <div class="colldett-invoice__payment-title">{{ $pay['title'] ?? 'Payment Details' }}</div>
            @foreach($pay['sections'] ?? [] as $section)
                <div class="colldett-invoice__payment-block">
                    @if(!empty($section['heading']))
                        <div class="colldett-invoice__payment-heading">{{ $section['heading'] }}</div>
                    @endif
                    @foreach($section['lines'] ?? [] as $line)
                        <div class="colldett-invoice__payment-line">{{ $line }}</div>
                    @endforeach
                </div>
            @endforeach
            @if(!empty($pay['note']))
                <p class="colldett-invoice__payment-note">{{ $pay['note'] }}</p>
            @endif
        </aside>
    </div>

    <table class="colldett-invoice__table colldett-invoice__table--items">
        <thead>
            <tr>
                <th class="colldett-invoice__th-desc">Description</th>
                <th class="colldett-invoice__th-total">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{!! nl2br(e($scope)) !!}</td>
                <td class="colldett-invoice__cell-num">{{ $fmtMoney($subtotal) }}</td>
            </tr>
            <tr class="colldett-invoice__summary-row">
                <td class="colldett-invoice__summary-label">Sub Total</td>
                <td class="colldett-invoice__cell-num">{{ $fmtMoney($subtotal) }}</td>
            </tr>
            <tr class="colldett-invoice__summary-row">
                <td class="colldett-invoice__summary-label">{{ $vatLabel }}</td>
                <td class="colldett-invoice__cell-num">{{ $fmtMoney($vat) }}</td>
            </tr>
            <tr class="colldett-invoice__summary-row">
                <td class="colldett-invoice__summary-label">Credit</td>
                <td class="colldett-invoice__cell-num">{{ $fmtMoney($credit) }}</td>
            </tr>
            <tr class="colldett-invoice__summary-row colldett-invoice__summary-row--total">
                <td class="colldett-invoice__summary-label">Total</td>
                <td class="colldett-invoice__cell-num">{{ $fmtMoney($total) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="colldett-invoice__generated">
        PDF Generated on {{ Carbon::now()->format('l, F jS, Y') }}
    </p>
</div>
