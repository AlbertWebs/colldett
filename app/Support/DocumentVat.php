<?php

namespace App\Support;

/**
 * Per-document optional VAT for invoices, quotations, and fee notes.
 */
final class DocumentVat
{
    /**
     * Whether VAT should be applied. Defaults to on for existing records without the flag.
     *
     * @param  array<string, mixed>  $values
     */
    public static function applies(array $values): bool
    {
        if (array_key_exists('apply_vat', $values) && $values['apply_vat'] !== null && $values['apply_vat'] !== '') {
            $flag = strtolower(trim((string) $values['apply_vat']));

            return in_array($flag, ['1', 'true', 'yes', 'on'], true);
        }

        // Legacy fee notes: explicit zero rate means without VAT.
        if (array_key_exists('vat_rate', $values) && $values['vat_rate'] !== null && $values['vat_rate'] !== '') {
            $rate = self::normalizeRate((float) $values['vat_rate']);

            return $rate > 0;
        }

        return true;
    }

    /**
     * Effective VAT rate (0 when without VAT). Uses document settings when VAT is on.
     *
     * @param  array<string, mixed>  $values
     */
    public static function rate(array $values): float
    {
        if (! self::applies($values)) {
            return 0.0;
        }

        $configured = (float) (AdminStoredSettings::invoice()['vat_rate'] ?? 0.16);

        return self::normalizeRate($configured > 0 ? $configured : 0.16);
    }

    /**
     * Normalize form / stored apply_vat and sync fee-note vat_rate.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeInput(array $data, bool $syncFeeNoteRate = false): array
    {
        $applies = self::applies(array_merge(['apply_vat' => '1'], $data));
        $data['apply_vat'] = $applies ? '1' : '0';

        if ($syncFeeNoteRate) {
            $data['vat_rate'] = $applies
                ? (string) self::normalizeRate((float) (AdminStoredSettings::invoice()['vat_rate'] ?? 0.16))
                : '0';
        }

        return $data;
    }

    public static function normalizeRate(float $rate): float
    {
        if ($rate > 1 && $rate <= 100) {
            $rate /= 100;
        }
        if ($rate < 0 || $rate > 1) {
            return 0.16;
        }

        return $rate;
    }

    /**
     * Derive apply_vat for the edit form from stored row (including legacy vat_rate).
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function forForm(array $values): array
    {
        $values['apply_vat'] = self::applies($values) ? '1' : '0';

        return $values;
    }
}
