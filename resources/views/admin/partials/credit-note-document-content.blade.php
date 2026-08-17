@php
    use App\Support\CreditNoteLedger;
    use App\Support\DocumentPlainText;
    use Illuminate\Support\Carbon;

    $plain = static fn (?string $s, string $fallback = '—'): string => DocumentPlainText::fromHtml($s) ?: $fallback;

    $number = $plain((string) ($values['number'] ?? ''), '—');
    $feeNoteNumber = $plain((string) ($values['fee_note_number'] ?? ''), '—');
    $ourRef = $plain((string) ($values['our_ref'] ?? ''), '—');
    $yourRef = $plain((string) ($values['your_ref'] ?? ''), '—');
    $client = $plain((string) ($values['client'] ?? ''), '—');
    $addressRaw = DocumentPlainText::fromHtml(trim((string) ($values['address'] ?? '')));
    $clientKra = \App\Support\ClientDirectory::clientTaxPinForDocument($values);
    $addressLines = $addressRaw !== '' ? preg_split("/\r\n|\r|\n/", $addressRaw) : ['—'];
    $addressLines = \App\Support\ClientDirectory::feeNoteAddressLinesOmitDuplicateClientPin($addressLines, $clientKra);
    if ($addressLines === []) {
        $addressLines = ['—'];
    }
    $issueDate = ! empty($values['issued_date'])
        ? Carbon::parse((string) $values['issued_date'])->format('jS F, Y')
        : Carbon::now()->format('jS F, Y');
    $feeNoteDate = ! empty($values['fee_note_date'])
        ? Carbon::parse((string) $values['fee_note_date'])->format('jS F, Y')
        : '';
    $description = DocumentPlainText::fromHtml(trim((string) ($values['line_description'] ?? '')));
    if ($description === '') {
        $description = 'Credit in respect of professional fees previously billed.';
    }
    $notesPlain = DocumentPlainText::fromHtml(trim((string) ($values['notes'] ?? '')));
    $currency = \App\Support\AdminStoredSettings::invoice()['currency'] ?? 'KES';
    $credit = CreditNoteLedger::totals($values);
    $feeSnapshot = CreditNoteLedger::totals([
        'amount' => $values['fee_note_amount'] ?? $values['amount'] ?? 0,
        'apply_vat' => $values['apply_vat'] ?? '1',
        'vat_rate' => $values['vat_rate'] ?? null,
    ]);
    $balance = array_key_exists('balance_remaining_total', $values)
        ? (float) $values['balance_remaining_total']
        : round(max(0, $feeSnapshot['total'] - $credit['total']), 2);
    $fmtMoney = fn (float $n): string => number_format($n, 2, '.', ',');
    $companyKra = \App\Support\AdminStoredSettings::companyKraPin();
@endphp

<article class="colldett-fee-note colldett-fee-note--credit">
    <div class="colldett-fee-note__banner">Credit Note</div>

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
            <p class="colldett-fee-note__meta-line"><strong>Credit Note No:</strong> {{ $number }}</p>
            <p class="colldett-fee-note__meta-line"><strong>Against Fee Note:</strong> {{ $feeNoteNumber }}</p>
            @if($feeNoteDate !== '')
                <p class="colldett-fee-note__meta-line"><strong>Fee Note Date:</strong> {{ $feeNoteDate }}</p>
            @endif
            <p class="colldett-fee-note__meta-line"><strong>Your Ref:</strong> {{ $yourRef }}</p>
            <p class="colldett-fee-note__meta-line"><strong>Date:</strong> {{ $issueDate }}</p>
            <p class="colldett-fee-note__meta-line"><strong>Company KRA PIN:</strong> {{ $companyKra !== '' ? $companyKra : '—' }}</p>
            <p class="colldett-fee-note__meta-line"><strong>Client KRA PIN / Tax ID:</strong> {{ $clientKra !== '' ? $clientKra : '—' }}</p>
        </div>
    </div>

    <p class="colldett-fee-note__credit-lead">
        This credit note is issued against Fee Note <strong>{{ $feeNoteNumber }}</strong>@if($feeNoteDate !== '') dated {{ $feeNoteDate }}@endif
        and reduces the amount payable thereunder. It is not a request for payment.
    </p>

    <table class="colldett-fee-note__table">
        <thead>
            <tr>
                <th>Particulars of Credit</th>
                <th class="colldett-fee-note__num">Amount (CR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{!! nl2br(e($description)) !!}</td>
                <td class="colldett-fee-note__num">{{ $fmtMoney($credit['amount']) }}</td>
            </tr>
            @if($credit['apply_vat'])
                <tr>
                    <td class="colldett-fee-note__label">V.A.T ({{ number_format($credit['vat_rate'] * 100, 0) }}%)</td>
                    <td class="colldett-fee-note__num">{{ $fmtMoney($credit['vat']) }}</td>
                </tr>
            @endif
            <tr class="colldett-fee-note__row-total">
                <td class="colldett-fee-note__label">Total Credit</td>
                <td class="colldett-fee-note__num">{{ $currency }} {{ $fmtMoney($credit['total']) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="colldett-fee-note__table colldett-fee-note__settlement">
        <thead>
            <tr>
                <th colspan="2">Application against the original fee note</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Original fee note{{ $credit['apply_vat'] ? ' (inclusive of V.A.T)' : '' }}</td>
                <td class="colldett-fee-note__num">{{ $currency }} {{ $fmtMoney($feeSnapshot['total']) }}</td>
            </tr>
            <tr>
                <td>This credit{{ $credit['apply_vat'] ? ' (inclusive of V.A.T)' : '' }}</td>
                <td class="colldett-fee-note__num">({{ $currency }} {{ $fmtMoney($credit['total']) }})</td>
            </tr>
            <tr class="colldett-fee-note__row-total">
                <td class="colldett-fee-note__label">Balance remaining on fee note</td>
                <td class="colldett-fee-note__num">{{ $currency }} {{ $fmtMoney($balance) }}</td>
            </tr>
        </tbody>
    </table>

    @if($notesPlain !== '')
        <p class="colldett-fee-note__note">{!! nl2br(e($notesPlain)) !!}</p>
    @else
        <p class="colldett-fee-note__note">When replying please quote our reference and this credit note number.</p>
    @endif
</article>
