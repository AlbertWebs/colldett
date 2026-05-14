<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class ClientDirectory
{
    private const STORAGE_PATH = 'admin/clients.json';

    /** Last-used fee note “Client address” block keyed by company name (same values as billing client select). */
    private const FEE_NOTE_ADDRESSES_PATH = 'admin/fee_note_client_addresses.json';

    /** Companies always offered in billing/case dropdowns until migrated into stored clients. */
    private const LEGACY_COMPANY_EXTRAS = ['Metro Health', 'City Freight Ltd', 'Summit Traders'];

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        foreach (self::all() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public static function all(): array
    {
        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            $seed = self::defaultSeed();
            self::save($seed);

            return $seed;
        }
        $raw = json_decode(Storage::disk('local')->get(self::STORAGE_PATH), true);
        if (! is_array($raw) || $raw === []) {
            return self::defaultSeed();
        }

        return array_values($raw);
    }

    /** @return list<array<string, mixed>> */
    public static function defaultSeed(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'James Otieno',
                'company' => 'Prime Foods Ltd',
                'email' => 'j.otieno@prime.com',
                'phone' => '+254 700 001100',
                'phone_alt' => '',
                'contact_title' => '',
                'address' => '',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'tax_pin' => '',
                'industry' => 'Food & beverage',
                'website' => '',
                'notes' => '',
                'account_number' => 'AC-000238',
                'status' => 'active',
            ],
            [
                'id' => 2,
                'name' => 'Mercy Njeri',
                'company' => 'Apex Motors',
                'email' => 'mercy@apex.com',
                'phone' => '+254 700 001200',
                'phone_alt' => '',
                'contact_title' => '',
                'address' => '',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'tax_pin' => '',
                'industry' => 'Automotive',
                'website' => '',
                'notes' => '',
                'account_number' => 'AC-000239',
                'status' => 'active',
            ],
        ];
    }

    /**
     * Unique company names for billing and case &lt;select&gt; options, sorted.
     *
     * @return list<string>
     */
    public static function companyNamesForSelect(): array
    {
        $fromRecords = collect(self::all())
            ->pluck('company')
            ->filter()
            ->map(fn ($s) => trim((string) $s));

        return $fromRecords
            ->merge(self::LEGACY_COMPANY_EXTRAS)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /** @param  list<array<string, mixed>>  $items */
    public static function save(array $items): void
    {
        Storage::disk('local')->put(
            self::STORAGE_PATH,
            json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * @param  list<array<string, mixed>>  $existing
     */
    public static function nextAccountNumber(array $existing): string
    {
        $max = 0;
        foreach ($existing as $row) {
            if (preg_match('/AC-(\d+)/', (string) ($row['account_number'] ?? ''), $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return 'AC-'.str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Client KRA PIN / tax ID for billing documents: optional explicit {@see client_tax_pin} on the
     * document row, otherwise matched from {@see clients.json} by company name (same as client select).
     */
    public static function clientTaxPinForDocument(array $values): string
    {
        $manual = trim((string) ($values['client_tax_pin'] ?? ''));
        if ($manual !== '') {
            return $manual;
        }
        $client = trim((string) ($values['client'] ?? ''));
        if ($client === '') {
            return '';
        }
        foreach (self::all() as $row) {
            if (trim((string) ($row['company'] ?? '')) === $client) {
                return trim((string) ($row['tax_pin'] ?? ''));
            }
        }

        return '';
    }

    /**
     * Company name → last saved fee note recipient address (plain text, newlines preserved).
     *
     * @return array<string, string>
     */
    public static function feeNoteAddressesByCompany(): array
    {
        if (! Storage::disk('local')->exists(self::FEE_NOTE_ADDRESSES_PATH)) {
            return [];
        }
        $raw = json_decode(Storage::disk('local')->get(self::FEE_NOTE_ADDRESSES_PATH), true);
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $k => $v) {
            if (is_string($k) && is_string($v)) {
                $out[trim($k)] = $v;
            }
        }

        return $out;
    }

    /**
     * Map for fee note forms: file store plus optional {@see fee_note_address} on client rows.
     *
     * @return array<string, string>
     */
    public static function feeNoteAddressesForForm(): array
    {
        $map = self::feeNoteAddressesByCompany();
        foreach (self::all() as $row) {
            $company = trim((string) ($row['company'] ?? ''));
            $block = trim((string) ($row['fee_note_address'] ?? ''));
            if ($company !== '' && $block !== '' && ! array_key_exists($company, $map)) {
                $map[$company] = $block;
            }
        }
        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);

        return $map;
    }

    /**
     * Remember the client address block for reuse on later fee notes (draft, issued, or update).
     */
    public static function rememberFeeNoteAddress(string $company, string $address): void
    {
        $company = trim($company);
        $address = trim(DocumentPlainText::fromHtml($address));
        if ($company === '' || $address === '') {
            return;
        }

        $map = self::feeNoteAddressesByCompany();
        $map[$company] = $address;
        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);

        Storage::disk('local')->put(
            self::FEE_NOTE_ADDRESSES_PATH,
            json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        self::mirrorFeeNoteAddressIntoClientsJson($company, $address);
    }

    /**
     * @internal
     */
    private static function mirrorFeeNoteAddressIntoClientsJson(string $company, string $address): void
    {
        $items = self::all();
        $changed = false;
        foreach ($items as $i => $row) {
            if (trim((string) ($row['company'] ?? '')) !== $company) {
                continue;
            }
            $items[$i]['fee_note_address'] = $address;
            $changed = true;
        }
        if ($changed) {
            self::save($items);
        }
    }
}
