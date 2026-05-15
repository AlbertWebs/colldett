<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Billing documents and cases linked to a client company name (same value as billing client select).
 */
final class ClientBillingDocuments
{
    private const FEE_NOTES_PATH = 'admin/billing_fee_notes.json';

    private const INVOICES_PATH = 'admin/billing_invoices.json';

    private const CASES_PATH = 'admin/cases.json';

    /**
     * @return array{
     *     fee_notes: list<array<string, mixed>>,
     *     invoices: list<array<string, mixed>>,
     *     cases: list<array<string, mixed>>
     * }
     */
    public static function forCompany(string $company): array
    {
        $company = trim($company);
        if ($company === '') {
            return ['fee_notes' => [], 'invoices' => [], 'cases' => []];
        }

        return [
            'fee_notes' => self::feeNotesFor($company),
            'invoices' => self::invoicesFor($company),
            'cases' => self::casesFor($company),
        ];
    }

    public static function hasAny(array $documents): bool
    {
        return ($documents['fee_notes'] ?? []) !== []
            || ($documents['invoices'] ?? []) !== []
            || ($documents['cases'] ?? []) !== [];
    }

    /** @return list<array<string, mixed>> */
    private static function feeNotesFor(string $company): array
    {
        $rows = self::readJson(self::FEE_NOTES_PATH);
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row) || trim((string) ($row['client'] ?? '')) !== $company) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $isDraft = (bool) ($row['is_draft'] ?? false);
            $number = trim((string) ($row['number'] ?? ''));
            $out[] = [
                'id' => $id,
                'reference' => $isDraft && $number === '' ? 'Draft' : ($number !== '' ? $number : 'Draft'),
                'date' => self::formatDate($row['issued_date'] ?? ''),
                'amount' => self::formatAmount($row['amount'] ?? ''),
                'status' => $isDraft ? 'Draft' : 'Issued',
                'sort_key' => $number !== '' ? $number : 'zzz-draft-'.$id,
            ];
        }

        usort($out, static fn (array $a, array $b): int => strcmp((string) $b['sort_key'], (string) $a['sort_key']));

        return array_map(static function (array $row): array {
            unset($row['sort_key']);

            return $row;
        }, $out);
    }

    /** @return list<array<string, mixed>> */
    private static function invoicesFor(string $company): array
    {
        $rows = self::ensureInvoiceIds(self::readJson(self::INVOICES_PATH));
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row) || trim((string) ($row['client'] ?? '')) !== $company) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $number = trim((string) ($row['number'] ?? ''));
            $out[] = [
                'id' => $id,
                'reference' => $number !== '' ? $number : 'Invoice #'.$id,
                'date' => self::formatDate($row['issued_date'] ?? ''),
                'amount' => self::formatAmount($row['amount'] ?? ''),
                'status' => 'Issued',
                'sort_key' => $number,
            ];
        }

        usort($out, static fn (array $a, array $b): int => strcmp((string) $b['sort_key'], (string) $a['sort_key']));

        return array_map(static function (array $row): array {
            unset($row['sort_key']);

            return $row;
        }, $out);
    }

    /** @return list<array<string, mixed>> */
    private static function casesFor(string $company): array
    {
        $rows = self::readJson(self::CASES_PATH);
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row) || trim((string) ($row['client'] ?? '')) !== $company) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $out[] = [
                'id' => $id,
                'reference' => trim((string) ($row['case_number'] ?? '')) ?: 'Case #'.$id,
                'date' => self::formatDate($row['next_action_date'] ?? ''),
                'amount' => trim((string) ($row['amount'] ?? '')) ?: '—',
                'status' => trim((string) ($row['status'] ?? '')) ?: '—',
                'sort_key' => (string) ($row['case_number'] ?? ''),
            ];
        }

        usort($out, static fn (array $a, array $b): int => strcmp((string) $b['sort_key'], (string) $a['sort_key']));

        return array_map(static function (array $row): array {
            unset($row['sort_key']);

            return $row;
        }, $out);
    }

    /** @return list<array<string, mixed>> */
    private static function readJson(string $path): array
    {
        if (! Storage::disk('local')->exists($path)) {
            return [];
        }
        $decoded = json_decode(Storage::disk('local')->get($path), true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private static function ensureInvoiceIds(array $rows): array
    {
        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int) ($row['id'] ?? 0));
        }
        $changed = false;
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if ((int) ($row['id'] ?? 0) < 1) {
                $max++;
                $row['id'] = $max;
                $changed = true;
            }
            $out[] = $row;
        }
        if ($changed) {
            Storage::disk('local')->put(
                self::INVOICES_PATH,
                json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        return $out;
    }

    private static function formatDate(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '—';
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('j M Y');
        } catch (\Throwable) {
            return $value;
        }
    }

    private static function formatAmount(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '—';
        }
        if (is_numeric($value)) {
            $currency = AdminStoredSettings::invoice()['currency'] ?? 'KES';

            return $currency.' '.number_format((float) $value, 2, '.', ',');
        }

        return $value;
    }
}
