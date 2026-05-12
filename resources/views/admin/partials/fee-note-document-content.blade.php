@php
    use App\Support\DocumentPlainText;
    use Illuminate\Support\Carbon;

    $values = \App\Support\AdminStoredSettings::feeNoteFillRemittance($values);

    $plain = static fn (?string $s, string $fallback = '—'): string => DocumentPlainText::fromHtml($s) ?: $fallback;

    $number = $plain((string) ($values['number'] ?? ''), '—');
    $ourRef = $plain((string) ($values['our_ref'] ?? ''), '—');
    $yourRef = $plain((string) ($values['your_ref'] ?? ''), '—');
    $client = $plain((string) ($values['client'] ?? ''), '—');
    $addressRaw = DocumentPlainText::fromHtml(trim((string) ($values['address'] ?? '')));
    $addressLines = $addressRaw !== '' ? preg_split("/\r\n|\r|\n/", $addressRaw) : ['—'];
    $issueDate = ! empty($values['issued_date'])
        ? Carbon::parse((string) $values['issued_date'])->format('jS F, Y')
        : Carbon::now()->format('jS F, Y');
    $paymentTerms = $plain((string) ($values['payment_terms'] ?? ''), 'IMMEDIATE');
    $description = DocumentPlainText::fromHtml(trim((string) ($values['line_description'] ?? '')));
    if ($description === '') {
        $description = 'Professional fee note.';
    }
    $notesPlain = DocumentPlainText::fromHtml(trim((string) ($values['notes'] ?? '')));
    $currency = \App\Support\AdminStoredSettings::invoice()['currency'] ?? 'KES';
    $amountRaw = (string) ($values['amount'] ?? '0');
    $amount = is_numeric($amountRaw) ? (float) $amountRaw : (float) preg_replace('/[^\d.]/', '', $amountRaw);
    $vatRateRaw = (string) ($values['vat_rate'] ?? '0.16');
    $vatRate = is_numeric($vatRateRaw) ? (float) $vatRateRaw : 0.16;
    if ($vatRate > 1 && $vatRate <= 100) {
        $vatRate /= 100;
    }
    if ($vatRate > 1 || $vatRate < 0) {
        $vatRate = (float) (\App\Support\AdminStoredSettings::invoice()['vat_rate'] ?? 0.16);
        if ($vatRate > 1) {
            $vatRate /= 100;
        }
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
                <td>{!! nl2br(e($description)) !!}</td>
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
            <div>Account Name:</div><div>{{ $plain($values['account_name'] ?? null) }}</div>
            <div>Account Number:</div><div>{{ $plain($values['account_number'] ?? null) }}</div>
            <div>Bank:</div><div>{{ $plain($values['bank_name'] ?? null) }}</div>
            <div>Branch:</div><div>{{ $plain($values['branch'] ?? null) }}</div>
            <div>Swift Code:</div><div>{{ $plain($values['swift_code'] ?? null) }}</div>
            <div>Bank Code:</div><div>{{ $plain($values['bank_code'] ?? null) }}</div>
            <div>Branch Code:</div><div>{{ $plain($values['branch_code'] ?? null) }}</div>
        </div>
    </section>

    @if($notesPlain !== '')
        <p class="colldett-fee-note__note">{!! nl2br(e($notesPlain)) !!}</p>
    @endif
</article>
