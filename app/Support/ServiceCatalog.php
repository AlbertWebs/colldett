<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class ServiceCatalog
{
    private const STORAGE_PATH = 'admin/services.json';

    /** @return list<array{id: int, name: string, slug: string, description: string, image: string}> */
    public static function all(): array
    {
        if (Storage::disk('local')->exists(self::STORAGE_PATH)) {
            $decoded = json_decode(Storage::disk('local')->get(self::STORAGE_PATH), true);
            if (is_array($decoded)) {
                return array_values($decoded);
            }
        }

        return array_map(static function (array $service, int $index): array {
            return [
                'id' => $index + 1,
                'name' => $service['name'],
                'slug' => $service['slug'],
                'description' => $service['description'] ?? '',
                'image' => $service['image'] ?? '',
            ];
        }, config('colldett.services', []), array_keys(config('colldett.services', [])));
    }

    public static function find(int $id): ?array
    {
        foreach (self::all() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        foreach (self::all() as $row) {
            if (trim((string) ($row['slug'] ?? '')) === $slug) {
                return $row;
            }
        }

        return null;
    }

    /** @return list<array{id: int, name: string}> */
    public static function optionsForSelect(): array
    {
        return collect(self::all())
            ->map(fn (array $row): array => [
                'id' => (int) ($row['id'] ?? 0),
                'name' => (string) ($row['name'] ?? ''),
            ])
            ->filter(fn (array $row): bool => $row['id'] > 0 && $row['name'] !== '')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }
}
