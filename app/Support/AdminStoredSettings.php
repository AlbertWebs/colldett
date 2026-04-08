<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Reads storage/app/private/admin/settings.json (same source as SettingsController).
 */
final class AdminStoredSettings
{
    private const STORAGE_PATH = 'admin/settings.json';

    private static ?array $cache = null;

    public static function flushCache(): void
    {
        self::$cache = null;
    }

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return self::$cache = [];
        }

        $json = Storage::disk('local')->get(self::STORAGE_PATH);
        $decoded = json_decode($json, true);

        return self::$cache = is_array($decoded) ? $decoded : [];
    }

    public static function companyLogoRelativePath(): string
    {
        $saved = self::all();
        $path = $saved['company_logo'] ?? null;
        if (is_string($path) && $path !== '' && ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
            return ltrim($path, '/');
        }

        return 'uploads/logo.png';
    }

    public static function companyEmail(): string
    {
        $saved = self::all();

        return (string) ($saved['company_email'] ?? config('colldett.company.email', ''));
    }

    /**
     * Merged document theme for letterhead preview/PDF (footer contacts, header address).
     *
     * @return array{website: string, phones: string, address_lines: array<int, string>, letterhead_image: ?string}
     */
    public static function documentTheme(): array
    {
        $saved = self::all();
        $theme = config('colldett.document_theme', []);
        $company = config('colldett.company', []);

        $addressLines = $theme['address_lines'] ?? array_filter([(string) ($company['address'] ?? '')]);
        if (! empty($saved['document_address_lines'])) {
            $addressLines = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", (string) $saved['document_address_lines']))));
        } elseif (! empty($saved['company_address'])) {
            $addressLines = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", (string) $saved['company_address']))));
        }

        $website = trim((string) ($saved['document_website'] ?? ''));
        if ($website === '') {
            $website = (string) ($theme['website'] ?? '');
        }

        $phones = trim((string) ($saved['document_phones'] ?? ''));
        if ($phones === '') {
            $phones = (string) ($theme['phones'] ?? ($saved['company_phone'] ?? $company['phone'] ?? ''));
        }

        $letterhead = $saved['document_letterhead_path'] ?? null;
        if (! is_string($letterhead) || $letterhead === '') {
            $letterhead = $theme['letterhead_image'] ?? null;
        }

        return [
            'website' => $website,
            'phones' => $phones,
            'address_lines' => $addressLines,
            'letterhead_image' => is_string($letterhead) && $letterhead !== '' ? $letterhead : null,
        ];
    }

    /**
     * Invoice VAT, currency, payment block — merged with config defaults.
     *
     * @return array{vat_rate: float, vat_label: string, currency: string, payment_details: array{title: string, sections: array<int, array{heading: string, lines: array<int, string>}>, note: string}}
     */
    public static function invoice(): array
    {
        $saved = self::all();
        $defaults = config('colldett.invoice', []);
        $pd = $defaults['payment_details'] ?? ['title' => 'Payment Details', 'sections' => [], 'note' => ''];

        $vatRate = self::parseVatRate($saved['invoice_vat_rate'] ?? null, (float) ($defaults['vat_rate'] ?? 0.16));
        $vatLabel = trim((string) ($saved['invoice_vat_label'] ?? ''));
        if ($vatLabel === '') {
            $vatLabel = (string) ($defaults['vat_label'] ?? 'VAT');
        }

        $currency = trim((string) ($saved['invoice_currency'] ?? ''));
        if ($currency === '') {
            $currency = (string) ($defaults['currency'] ?? 'Ksh');
        }

        $title = trim((string) ($saved['invoice_payment_title'] ?? ''));
        if ($title === '') {
            $title = (string) ($pd['title'] ?? 'Payment Details');
        }

        $sections = self::buildPaymentSections($saved, $pd);

        $note = trim((string) ($saved['invoice_payment_note'] ?? ''));
        if ($note === '') {
            $note = (string) ($pd['note'] ?? '');
        }

        return [
            'vat_rate' => $vatRate,
            'vat_label' => $vatLabel,
            'currency' => $currency,
            'payment_details' => [
                'title' => $title,
                'sections' => $sections,
                'note' => $note,
            ],
        ];
    }

    private static function parseVatRate(mixed $raw, float $fallback): float
    {
        if ($raw === null || $raw === '') {
            return $fallback;
        }
        $r = (float) $raw;
        if ($r > 1) {
            return round($r / 100, 6);
        }

        return $r;
    }

    /**
     * @param  array<string, mixed>  $saved
     * @param  array<string, mixed>  $defaultPaymentDetails
     * @return array<int, array{heading: string, lines: array<int, string>}>
     */
    private static function buildPaymentSections(array $saved, array $defaultPaymentDetails): array
    {
        $defaultSections = $defaultPaymentDetails['sections'] ?? [];
        $out = [];

        $bankHeading = trim((string) ($saved['invoice_bank_heading'] ?? '')) ?: 'Bank';
        $bankRaw = trim((string) ($saved['invoice_payment_bank_lines'] ?? ''));
        if ($bankRaw !== '') {
            $lines = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $bankRaw))));
            if ($lines !== []) {
                $out[] = ['heading' => $bankHeading, 'lines' => $lines];
            }
        } elseif (isset($defaultSections[0]) && is_array($defaultSections[0])) {
            $out[] = $defaultSections[0];
        }

        $otherHeading = trim((string) ($saved['invoice_other_heading'] ?? '')) ?: 'Other';
        $otherRaw = trim((string) ($saved['invoice_payment_other_lines'] ?? ''));
        if ($otherRaw !== '') {
            $lines = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $otherRaw))));
            if ($lines !== []) {
                $out[] = ['heading' => $otherHeading, 'lines' => $lines];
            }
        }

        if ($out === []) {
            foreach ($defaultSections as $section) {
                if (is_array($section) && ! empty($section['lines'])) {
                    $out[] = $section;
                }
            }
        }

        return $out;
    }
}
