<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class ClientDirectory
{
    private const STORAGE_PATH = 'admin/clients.json';

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
}
