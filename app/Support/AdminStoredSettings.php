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

    public static function companyKraPin(): string
    {
        $saved = self::all();

        return trim((string) ($saved['company_kra_pin'] ?? '')) ?: trim((string) (config('colldett.company.kra_pin', '') ?? ''));
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
        if ($bankRaw === '') {
            $bankRaw = self::composeInvoicePaymentBankLinesFromRemittanceFields($saved);
        }
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
        } elseif (isset($defaultSections[1]) && is_array($defaultSections[1]) && ! empty($defaultSections[1]['lines'])) {
            $out[] = $defaultSections[1];
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

    /**
     * Merge "Label:" lines where the value is on the following line (common in pasted bank blocks).
     */
    private static function normalizeBankLinesForColonParse(string $raw): string
    {
        $parts = preg_split("/\r\n|\r|\n/", $raw);
        if ($parts === false) {
            return $raw;
        }
        $merged = [];
        $pendingLabel = null;
        foreach ($parts as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            if ($pendingLabel !== null) {
                $merged[] = $pendingLabel.': '.$line;
                $pendingLabel = null;

                continue;
            }
            if (! str_contains($line, ':')) {
                continue;
            }
            [$left, $right] = explode(':', $line, 2);
            $right = trim((string) $right);
            if ($right === '') {
                $pendingLabel = trim((string) $left);
                if ($pendingLabel === '') {
                    $pendingLabel = null;
                }

                continue;
            }
            $merged[] = $line;
        }

        return implode("\n", $merged);
    }

    private static function feeNoteRemittanceValueIsPlaceholder(string $value): bool
    {
        $t = trim($value);
        if ($t === '' || $t === '—' || $t === '-' || $t === '–') {
            return true;
        }

        return match (strtolower($t)) {
            '(configure)', 'configure', 'n/a', 'na', 'tbd', 'pending', 'todo' => true,
            default => false,
        };
    }

    /**
     * Human-readable bank lines for the fee note (omits blanks / placeholders).
     * Always reflects current Admin → Settings remittance fields, not data stored on the fee note row.
     *
     * @param  array<string, mixed>  $_values  unused; kept for call-site compatibility
     * @return list<string>
     */
    public static function feeNoteBankDisplayLines(array $_values = []): array
    {
        // Always use current Settings — never frozen per-record bank lines (legacy JSON snapshots).
        $filled = self::feeNoteRemittanceDefaults();
        $rows = [
            'Account Name' => (string) ($filled['account_name'] ?? ''),
            'Account Number' => (string) ($filled['account_number'] ?? ''),
            'Bank' => (string) ($filled['bank_name'] ?? ''),
            'Branch' => (string) ($filled['branch'] ?? ''),
            'Swift Code' => (string) ($filled['swift_code'] ?? ''),
            'Bank Code' => (string) ($filled['bank_code'] ?? ''),
            'Branch Code' => (string) ($filled['branch_code'] ?? ''),
        ];
        $lines = [];
        foreach ($rows as $label => $v) {
            $v = trim($v);
            if ($v === '' || self::feeNoteRemittanceValueIsPlaceholder($v)) {
                continue;
            }
            $lines[] = $label.': '.$v;
        }
        if ($lines !== []) {
            return $lines;
        }
        $inv = self::invoice();
        $fallback = $inv['payment_details']['sections'][0]['lines'] ?? [];

        if (! is_array($fallback)) {
            return [];
        }

        $trimmed = array_map(static fn (mixed $s): string => trim((string) $s), $fallback);

        return array_values(array_filter($trimmed, static fn (string $s): bool => $s !== ''));
    }

    /**
     * Keys stored in settings.json for the structured bank / remittance form (admin).
     *
     * @return list<string>
     */
    public static function remittanceSettingKeys(): array
    {
        return [
            'remittance_account_name',
            'remittance_account_number',
            'remittance_bank',
            'remittance_branch',
            'remittance_swift_code',
            'remittance_bank_code',
            'remittance_branch_code',
            'remittance_reference_line',
        ];
    }

    /**
     * Default remittance field values (KCB Haile Selassie — used for new installs and form fallbacks).
     *
     * @return array<string, string>
     */
    public static function remittanceFormDefaults(): array
    {
        return [
            'remittance_account_name' => 'Colldett Trace Limited',
            'remittance_account_number' => '1351221760',
            'remittance_bank' => 'KENYA COMMERCIAL BANK',
            'remittance_branch' => 'HAILE SELASSIE',
            'remittance_swift_code' => 'KCBLKENX',
            'remittance_bank_code' => '01',
            'remittance_branch_code' => '288',
            'remittance_reference_line' => 'your invoice number',
        ];
    }

    /**
     * Values for the admin settings form (saved remittance fields, else parsed bank lines, else defaults).
     *
     * @param  array<string, mixed>  $saved
     * @return array<string, string>
     */
    public static function remittanceFormValuesForAdmin(array $saved): array
    {
        $defaults = self::remittanceFormDefaults();
        if (array_key_exists('remittance_account_name', $saved)) {
            $out = [];
            foreach (self::remittanceSettingKeys() as $key) {
                $out[$key] = trim((string) ($saved[$key] ?? ''));
            }

            return $out;
        }

        $parsed = self::parseBankLinesIntoRemittanceFields((string) ($saved['invoice_payment_bank_lines'] ?? ''));
        $out = [];
        foreach (self::remittanceSettingKeys() as $key) {
            $v = $parsed[$key] ?? '';
            $out[$key] = $v !== '' ? $v : ($defaults[$key] ?? '');
        }

        return $out;
    }

    /**
     * Build invoice "bank lines" text from structured remittance fields (also used for fee note parsing).
     *
     * @param  array<string, mixed>  $fields  keys remittance_* or plain POST names
     */
    public static function composeInvoicePaymentBankLinesFromRemittanceFields(array $fields): string
    {
        $g = static fn (string $k): string => trim((string) ($fields[$k] ?? ''));

        $pairs = [
            'Account Name' => $g('remittance_account_name'),
            'Account Number' => $g('remittance_account_number'),
            'Bank' => $g('remittance_bank'),
            'Branch' => $g('remittance_branch'),
            'Swift Code' => $g('remittance_swift_code'),
            'Bank Code' => $g('remittance_bank_code'),
            'Branch Code' => $g('remittance_branch_code'),
            'Reference' => $g('remittance_reference_line'),
        ];
        $lines = [];
        foreach ($pairs as $label => $val) {
            if ($val !== '') {
                $lines[] = $label.': '.$val;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $saved
     * @return array{account_name: string, account_number: string, bank_name: string, branch: string, swift_code: string, bank_code: string, branch_code: string}|null
     */
    private static function feeNoteSlotsFromRemittanceSettings(array $saved): ?array
    {
        if (! array_key_exists('remittance_account_name', $saved)) {
            return null;
        }

        $out = [
            'account_name' => trim((string) ($saved['remittance_account_name'] ?? '')),
            'account_number' => trim((string) ($saved['remittance_account_number'] ?? '')),
            'bank_name' => trim((string) ($saved['remittance_bank'] ?? '')),
            'branch' => trim((string) ($saved['remittance_branch'] ?? '')),
            'swift_code' => trim((string) ($saved['remittance_swift_code'] ?? '')),
            'bank_code' => trim((string) ($saved['remittance_bank_code'] ?? '')),
            'branch_code' => trim((string) ($saved['remittance_branch_code'] ?? '')),
        ];
        foreach ($out as $v) {
            if ($v !== '') {
                return $out;
            }
        }

        return null;
    }

    /**
     * Parse stored bank lines (HTML allowed) into remittance_*-shaped keys for the admin form.
     *
     * @return array<string, string>
     */
    private static function parseBankLinesIntoRemittanceFields(string $raw): array
    {
        $empty = array_fill_keys(self::remittanceSettingKeys(), '');
        $plain = trim(DocumentPlainText::fromHtml($raw));
        if ($plain === '') {
            return $empty;
        }

        $norm = self::normalizeBankLinesForColonParse($plain);
        $out = $empty;

        foreach (preg_split("/\r\n|\r|\n/", $norm) as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            if (stripos($line, 'paybill') !== false) {
                continue;
            }
            [$label, $value] = explode(':', $line, 2);
            $labelNorm = strtolower(trim(preg_replace('/\s+/', ' ', $label)));
            $value = trim($value);
            if ($value === '' || self::feeNoteRemittanceValueIsPlaceholder($value)) {
                continue;
            }

            if (str_contains($labelNorm, 'reference')) {
                $out['remittance_reference_line'] = $value;

                continue;
            }

            $slot = match (true) {
                str_contains($labelNorm, 'account name') => 'remittance_account_name',
                str_contains($labelNorm, 'account number') => 'remittance_account_number',
                str_contains($labelNorm, 'bank name') => 'remittance_bank',
                $labelNorm === 'bank' || str_starts_with($labelNorm, 'bank ') => 'remittance_bank',
                str_contains($labelNorm, 'branch code') => 'remittance_branch_code',
                str_contains($labelNorm, 'bank code') => 'remittance_bank_code',
                str_contains($labelNorm, 'swift') => 'remittance_swift_code',
                $labelNorm === 'branch' || str_ends_with($labelNorm, ' branch') => 'remittance_branch',
                default => null,
            };

            if ($slot !== null) {
                $out[$slot] = $value;
            }
        }

        return $out;
    }

    /**
     * Bank remittance slots for fee notes — prefers structured Admin remittance fields, else parses
     * {@see invoice_payment_bank_lines} / config invoice bank section.
     *
     * @return array{account_name: string, account_number: string, bank_name: string, branch: string, swift_code: string, bank_code: string, branch_code: string}
     */
    public static function feeNoteRemittanceDefaults(): array
    {
        $out = [
            'account_name' => '',
            'account_number' => '',
            'bank_name' => '',
            'branch' => '',
            'swift_code' => '',
            'bank_code' => '',
            'branch_code' => '',
        ];

        $saved = self::all();
        $fromSettings = self::feeNoteSlotsFromRemittanceSettings($saved);
        if ($fromSettings !== null) {
            return $fromSettings;
        }

        $raw = trim((string) ($saved['invoice_payment_bank_lines'] ?? ''));
        if ($raw === '') {
            $inv = self::invoice();
            $lines = $inv['payment_details']['sections'][0]['lines'] ?? [];
            $raw = is_array($lines) ? implode("\n", $lines) : '';
        }

        $raw = self::normalizeBankLinesForColonParse(DocumentPlainText::fromHtml($raw));

        foreach (preg_split("/\r\n|\r|\n/", $raw) as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            if (stripos($line, 'paybill') !== false) {
                continue;
            }
            [$label, $value] = explode(':', $line, 2);
            $labelNorm = strtolower(trim(preg_replace('/\s+/', ' ', $label)));
            $value = trim($value);
            if ($value === '' || self::feeNoteRemittanceValueIsPlaceholder($value)) {
                continue;
            }
            if (str_contains($labelNorm, 'reference')) {
                continue;
            }

            $slot = match (true) {
                str_contains($labelNorm, 'account name') => 'account_name',
                str_contains($labelNorm, 'account number') => 'account_number',
                str_contains($labelNorm, 'bank name') => 'bank_name',
                $labelNorm === 'bank' || str_starts_with($labelNorm, 'bank ') => 'bank_name',
                str_contains($labelNorm, 'branch code') => 'branch_code',
                str_contains($labelNorm, 'bank code') => 'bank_code',
                str_contains($labelNorm, 'swift') => 'swift_code',
                $labelNorm === 'branch' || str_ends_with($labelNorm, ' branch') => 'branch',
                default => null,
            };

            if ($slot !== null) {
                $out[$slot] = $value;
            }
        }

        return $out;
    }

    /**
     * Keys persisted on fee note rows in older versions; bank details always come from Admin → Settings.
     * Strip these before save / before fill so previews and PDFs track current remittance settings.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function feeNoteStripStoredRemittance(array $row): array
    {
        foreach (['account_name', 'account_number', 'bank_name', 'branch', 'swift_code', 'bank_code', 'branch_code'] as $k) {
            unset($row[$k]);
        }

        return $row;
    }

    /**
     * Fill empty remittance keys on a fee note value set (stored row or preview payload).
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function feeNoteFillRemittance(array $values): array
    {
        $defaults = self::feeNoteRemittanceDefaults();
        foreach ($defaults as $key => $default) {
            $current = $values[$key] ?? '';
            if (! is_string($current)) {
                $values[$key] = $default;

                continue;
            }
            $t = trim($current);
            if ($t === '' || self::feeNoteRemittanceValueIsPlaceholder($t)) {
                $values[$key] = $default;
            }
        }

        return $values;
    }
}
