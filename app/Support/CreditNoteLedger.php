<?php

namespace App\Support;

/**
 * Credit notes are issued against an existing fee note (same client, VAT treatment, and references).
 */
final class CreditNoteLedger
{
    public const INDEX_PATH = 'admin/billing_credit_notes.json';

    public const SEQ_PATH = 'admin/billing_credit_note_seq.json';

    public static function parseAmount(mixed $raw): float
    {
        if (is_numeric($raw)) {
            return round((float) $raw, 2);
        }

        $cleaned = preg_replace('/[^\d.]/', '', (string) $raw) ?? '0';

        return round((float) $cleaned, 2);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array{amount: float, vat: float, total: float, apply_vat: bool, vat_rate: float}
     */
    public static function totals(array $values): array
    {
        $amount = self::parseAmount($values['amount'] ?? 0);
        $applyVat = DocumentVat::applies($values);
        $vatRate = DocumentVat::rate($values);
        $vat = $applyVat ? round($amount * $vatRate, 2) : 0.0;

        return [
            'amount' => $amount,
            'vat' => $vat,
            'total' => round($amount + $vat, 2),
            'apply_vat' => $applyVat,
            'vat_rate' => $vatRate,
        ];
    }

    /**
     * @param  array<string, mixed>  $feeNote
     * @param  list<array<string, mixed>>  $creditNotes
     */
    public static function remainingExVat(array $feeNote, array $creditNotes, ?int $exceptCreditId = null): float
    {
        $feeAmount = self::parseAmount($feeNote['amount'] ?? 0);
        $credited = 0.0;
        $feeId = (int) ($feeNote['id'] ?? 0);
        $feeNumber = trim((string) ($feeNote['number'] ?? ''));

        foreach ($creditNotes as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            if ($exceptCreditId !== null && $id === $exceptCreditId) {
                continue;
            }
            $againstId = (int) ($row['fee_note_id'] ?? 0);
            $againstNumber = trim((string) ($row['fee_note_number'] ?? ''));
            $matches = ($feeId > 0 && $againstId === $feeId)
                || ($feeNumber !== '' && $againstNumber === $feeNumber);
            if (! $matches) {
                continue;
            }
            $credited += self::parseAmount($row['amount'] ?? 0);
        }

        return round(max(0, $feeAmount - $credited), 2);
    }

    /**
     * Snapshot of fee-note identity copied onto the credit note at issue time.
     *
     * @param  array<string, mixed>  $feeNote
     * @return array<string, mixed>
     */
    public static function snapshotFromFeeNote(array $feeNote): array
    {
        $vat = DocumentVat::forForm($feeNote);
        $applies = DocumentVat::applies($vat);

        return [
            'fee_note_id' => (int) ($feeNote['id'] ?? 0),
            'fee_note_number' => trim((string) ($feeNote['number'] ?? '')),
            'fee_note_date' => trim((string) ($feeNote['issued_date'] ?? '')),
            'service_id' => (string) ($feeNote['service_id'] ?? ''),
            'our_ref' => trim((string) ($feeNote['our_ref'] ?? '')),
            'your_ref' => trim((string) ($feeNote['your_ref'] ?? '')),
            'client' => trim((string) ($feeNote['client'] ?? '')),
            'address' => DocumentPlainText::fromHtml((string) ($feeNote['address'] ?? '')),
            'apply_vat' => $applies ? '1' : '0',
            'vat_rate' => $applies
                ? (string) DocumentVat::normalizeRate((float) (AdminStoredSettings::invoice()['vat_rate'] ?? 0.16))
                : '0',
            'fee_note_amount' => (string) self::parseAmount($feeNote['amount'] ?? 0),
        ];
    }
}
