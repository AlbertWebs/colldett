@php
    use App\Support\AdminStoredSettings;
    use Illuminate\Support\Carbon;
    $inv = AdminStoredSettings::invoice();
    $currency = $inv['currency'] ?? 'Ksh';
    $rawAmount = $values['amount'] ?? 0;
    $amountNum = is_numeric($rawAmount)
        ? (float) $rawAmount
        : (float) preg_replace('/[^\d.]/', '', (string) $rawAmount);
    $fmtMoney = fn (float $n): string => $currency . ' ' . number_format($n, 2, '.', ',');
    $pay = $inv['payment_details'] ?? [];
    $payDate = isset($values['date']) && $values['date'] !== ''
        ? Carbon::parse($values['date'])
        : Carbon::now();
    $clientRaw = trim((string) ($values['client'] ?? ''));
    $fromLines = $clientRaw !== ''
        ? preg_split("/\r\n|\r|\n/", $clientRaw)
        : array_filter(['Client']);
    $pdfMode = $pdfMode ?? false;
@endphp

<div class="colldett-invoice {{ $pdfMode ? 'colldett-invoice--pdf' : '' }}">
    <div class="colldett-invoice__number-bar">
        Payment receipt #{{ $values['payment_id'] ?? '—' }}
    </div>

    <div class="colldett-invoice__meta-row">
        <div class="colldett-invoice__main">
            <div class="colldett-invoice__dates">
                <div><span class="colldett-invoice__date-label">Payment Date:</span> {{ $payDate->format('l, F jS, Y') }}</div>
                <div><span class="colldett-invoice__date-label">Invoice reference:</span> {{ $values['invoice'] ?? '—' }}</div>
                <div><span class="colldett-invoice__date-label">Payment method:</span> {{ $values['method'] ?? '—' }}</div>
            </div>
            <div class="colldett-invoice__to">
                <div class="colldett-invoice__to-title">Received From</div>
                <div class="colldett-invoice__to-lines">
                    @foreach($fromLines as $line)
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
                <th class="colldett-invoice__th-total">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Payment received
                    @if(!empty(trim((string) ($values['reference'] ?? ''))))
                        <span style="color:#475569;"> — Reference: {{ $values['reference'] }}</span>
                    @endif
                </td>
                <td class="colldett-invoice__cell-num">{{ $fmtMoney($amountNum) }}</td>
            </tr>
            <tr class="colldett-invoice__summary-row colldett-invoice__summary-row--total">
                <td class="colldett-invoice__summary-label">Total received</td>
                <td class="colldett-invoice__cell-num">{{ $fmtMoney($amountNum) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="colldett-invoice__generated">
        PDF Generated on {{ Carbon::now()->format('l, F jS, Y') }}
    </p>
</div>
