<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Team roster for public profiles (/team/{slug}) and about page.
 * Stored at storage/app/admin/team.json; falls back to config('colldett.team') when missing.
 */
final class TeamDirectory
{
    public const STORAGE_PATH = 'admin/team.json';

    /**
     * Full roster (including inactive), merged shape expected by pages/team-show.blade.php.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return self::normalizeList(config('colldett.team', []));
        }

        $decoded = json_decode((string) Storage::disk('local')->get(self::STORAGE_PATH), true);
        if (! is_array($decoded)) {
            return self::normalizeList(config('colldett.team', []));
        }

        return self::normalizeList($decoded);
    }

    /**
     * Members shown on the About page (active only).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function forPublicSite(): array
    {
        return array_values(array_filter(self::all(), static function (array $member): bool {
            return (bool) ($member['is_active'] ?? true);
        }));
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     */
    public static function saveMembers(array $members): void
    {
        $normalized = self::normalizeList($members);
        Storage::disk('local')->put(
            self::STORAGE_PATH,
            json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * @param  array<int, mixed>  $list
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeList(array $list): array
    {
        $out = [];
        foreach ($list as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = self::normalizeMember($row);
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $m
     * @return array<string, mixed>
     */
    public static function normalizeMember(array $m): array
    {
        $exp = $m['experience_years'] ?? null;
        if ($exp === '' || $exp === null) {
            $exp = null;
        } else {
            $exp = max(0, min(80, (int) $exp));
        }

        return [
            'slug' => (string) ($m['slug'] ?? ''),
            'name' => (string) ($m['name'] ?? ''),
            'role' => (string) ($m['role'] ?? ''),
            'department' => (string) ($m['department'] ?? ''),
            'image' => (string) ($m['image'] ?? ''),
            'bio' => (string) ($m['bio'] ?? ''),
            'experience_years' => $exp,
            'location' => (string) ($m['location'] ?? ''),
            'email' => (string) ($m['email'] ?? ''),
            'seo_description' => (string) ($m['seo_description'] ?? ''),
            'specialties' => self::stringList($m['specialties'] ?? []),
            'credentials' => self::stringList($m['credentials'] ?? []),
            'industries' => self::stringList($m['industries'] ?? []),
            'principles' => self::stringList($m['principles'] ?? []),
            'is_active' => (bool) ($m['is_active'] ?? true),
        ];
    }

    /**
     * @param  array<int|string, mixed>|mixed  $value
     * @return array<int, string>
     */
    private static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(static function ($item): string {
            return trim((string) $item);
        }, $value), static fn (string $s): bool => $s !== ''));
    }

    /**
     * True when the image field is empty or points at a known stock / placeholder source.
     */
    public static function imageIsGenericPlaceholder(string $image): bool
    {
        $image = trim($image);
        if ($image === '') {
            return true;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $host = parse_url($image, PHP_URL_HOST);
            $host = is_string($host) ? strtolower($host) : '';
            $hostNorm = preg_replace('/^www\./', '', $host) ?? $host;
            foreach (config('colldett.team_generic_image_hosts', []) as $pattern) {
                $pattern = strtolower(trim((string) $pattern));
                if ($pattern === '') {
                    continue;
                }
                if ($hostNorm === $pattern || str_ends_with($hostNorm, '.'.$pattern)) {
                    return true;
                }
            }

            return false;
        }

        $base = strtolower(basename($image));
        foreach (config('colldett.team_generic_image_filename_fragments', []) as $frag) {
            $frag = strtolower(trim((string) $frag));
            if ($frag !== '' && str_contains($base, $frag)) {
                return true;
            }
        }

        return false;
    }

    public static function memberPortraitImageUrl(string $image): string
    {
        return str_starts_with($image, 'http') ? $image : asset($image);
    }

    /**
     * Two-letter initials from a display name (handles commas and suffixes).
     */
    public static function initialsFromMemberName(string $name): string
    {
        $primary = trim(explode(',', $name, 2)[0] ?? '');
        $primary = trim(preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $primary) ?? '');
        $parts = preg_split('/\s+/u', $primary, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false || $parts === []) {
            return '?';
        }

        if (count($parts) === 1) {
            $word = $parts[0];
            $len = mb_strlen($word);

            return mb_strtoupper($len <= 1 ? $word : mb_substr($word, 0, min(2, $len)));
        }

        $first = mb_substr($parts[0], 0, 1);
        $lastWord = $parts[array_key_last($parts)];
        $last = mb_substr($lastWord, 0, 1);

        return mb_strtoupper($first.$last);
    }
}
