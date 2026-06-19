<?php

namespace App\Models;

use App\Support\DocumentPlainText;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Public-facing contact block (phone, email, address, map) for /contact and site footer.
 * Synced from admin Settings save; falls back to JSON settings and config when fields are empty.
 */
class ContactDetail extends Model
{
    protected $fillable = [
        'phone',
        'phone_alt',
        'email',
        'address',
        'map_embed_url',
    ];

    protected function address(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null || trim($value) === '') {
                    return null;
                }

                $plain = DocumentPlainText::fromHtml($value);

                return $plain !== '' ? $plain : null;
            },
        );
    }

    /**
     * @param  array{phone?: ?string, phone_alt?: ?string, email?: ?string, address?: ?string, map_embed_url?: ?string}  $attributes
     */
    public static function syncFromAdminSettings(array $attributes): void
    {
        $normalize = static function (?string $value): ?string {
            if ($value === null) {
                return null;
            }
            $t = trim($value);

            return $t === '' ? null : $t;
        };

        $row = self::query()->first();
        $payload = [
            'phone' => $normalize($attributes['phone'] ?? null),
            'phone_alt' => $normalize($attributes['phone_alt'] ?? null),
            'email' => $normalize($attributes['email'] ?? null),
            'address' => $normalize(self::plainAddress($attributes['address'] ?? null)),
            'map_embed_url' => $normalize($attributes['map_embed_url'] ?? null),
        ];

        if ($row === null) {
            self::query()->create($payload);
        } else {
            $row->update($payload);
        }
    }

    private static function plainAddress(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $plain = DocumentPlainText::fromHtml($value);

        return $plain !== '' ? $plain : null;
    }
}
