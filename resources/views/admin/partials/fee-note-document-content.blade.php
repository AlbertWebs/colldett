@php
    use Illuminate\Support\Carbon;

    $number = trim((string) ($values['number'] ?? '—'));
    $ourRef = trim((string) ($values['our_ref'] ?? '—'));
    $yourRef = trim((string) ($values['your_ref'] ?? '—'));
    $client = trim((string) ($values['client'] ?? '—'));
    $addressRaw = trim((string) ($values['address'] ?? ''));
    $addressLines = $addressRaw !== '' ? preg_split("/\r\n|\r|\n/", $addressRaw) : ['—'];
    $issueDate = ! empty($values['issued_date'])
        ? Carbon::parse((string) $values['issued_date'])->format('jS F, Y')
        : Carbon::now()->format('jS F, Y');
    $paymentTerms = trim((string) ($values['payment_terms'] ?? 'IMMEDIATE'));
    $description = trim((string) ($values['line_description'] ?? 'Professional fee note.'));
    $currency = \App\Support\AdminStoredSettings::invoice()['currency'] ?? 'KES';
    $amountRaw = (string) ($values['amount'] ?? '0');
    $amount = is_numeric($amountRaw) ? (float) $amountRaw : (float) preg_replace('/[^\d.]/', '', $amountRaw);
    $vatRateRaw = (string) ($values['vat_rate'] ?? '0.16');
    $vatRate = is_numeric($vatRateRaw) ? (float) $vatRateRaw : 0.16;
    if ($vatRate > 1) {
        $vatRate /= 100;
    }
    $vat = round($amount * $vatRate, 2);
    $total = round($amount + $vat, 2);
    $fmtMoney = fn (float $n): string => number_format($n, 2, '.', ',');
@endphp

<article class="colldett-fee-note">
    <div class="colldett-fee-note__meta-grid">
        <div class="colldett-fee-note__meta-left">
            <p class="colldett-fee-note__meta-line"><strong>Our Ref:</strong> {{ $ourRef }}</p>
            <p class="colldett-fee-note__meta-line"><strong>Client:</strong> {{ $client }}</p>
            <div class="colldett-fee-note__address">
                @foreach($addressLines as $line)
                    <div>{{ $line }}</div>
                @endforeach
            </div>
        </div>
        <div class="colldett-fee-note__meta-right">
            <p class="colldett-fee-note__meta-line"><strong>Fee Note No:</strong> {{ $number }}</p>
            <p class="colldett-fee-note__meta-line"><strong>Your Ref:</strong> {{ $yourRef }}</p>
            <p class="colldett-fee-note__meta-line"><strong>Date:</strong> {{ $issueDate }}</p>
            <p class="colldett-fee-note__meta-line"><strong>Payment Terms:</strong> {{ $paymentTerms }}</p>
        </div>
    </div>

    <table class="colldett-fee-note__table">
        <thead>
            <tr>
                <th>Particulars of Service Rendered</th>
                <th class="colldett-fee-note__num">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $description }}</td>
                <td class="colldett-fee-note__num">{{ $fmtMoney($amount) }}</td>
            </tr>
            <tr>
                <td class="colldett-fee-note__label">V.A.T ({{ number_format($vatRate * 100, 0) }}%)</td>
                <td class="colldett-fee-note__num">{{ $fmtMoney($vat) }}</td>
            </tr>
            <tr class="colldett-fee-note__row-total">
                <td class="colldett-fee-note__label">Total</td>
                <td class="colldett-fee-note__num">{{ $currency }} {{ $fmtMoney($total) }}</td>
            </tr>
        </tbody>
    </table>

    <section class="colldett-fee-note__bank">
        <h4>Please direct remittance to the following account details;</h4>
        <div class="colldett-fee-note__bank-grid">
            <div>Account Name:</div><div>{{ $values['account_name'] ?? '—' }}</div>
            <div>Account Number:</div><div>{{ $values['account_number'] ?? '—' }}</div>
            <div>Bank:</div><div>{{ $values['bank_name'] ?? '—' }}</div>
            <div>Branch:</div><div>{{ $values['branch'] ?? '—' }}</div>
            <div>Swift Code:</div><div>{{ $values['swift_code'] ?? '—' }}</div>
            <div>Bank Code:</div><div>{{ $values['bank_code'] ?? '—' }}</div>
            <div>Branch Code:</div><div>{{ $values['branch_code'] ?? '—' }}</div>
        </div>
    </section>

    @if(!empty(trim((string) ($values['notes'] ?? ''))))
        <p class="colldett-fee-note__note">{{ $values['notes'] }}</p>
    @endif
</article>
