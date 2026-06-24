<?php

namespace App\Support;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AdminAccess
{
    public const PANEL_PIN_HASH_KEY = 'admin_panel_pin_hash';

    public static function panelPinHash(): ?string
    {
        $hash = AdminStoredSettings::all()[self::PANEL_PIN_HASH_KEY] ?? null;

        return is_string($hash) && $hash !== '' ? $hash : null;
    }

    public static function hasPanelPin(): bool
    {
        return self::panelPinHash() !== null;
    }

    public static function hasAnyLoginMethod(): bool
    {
        if (self::hasPanelPin()) {
            return true;
        }

        if (self::legacyEnvConfigured()) {
            return true;
        }

        return AdminUser::query()->where('is_active', true)->exists();
    }

    public static function setPanelPin(string $plain): void
    {
        AdminStoredSettings::setValue(self::PANEL_PIN_HASH_KEY, Hash::make($plain));
    }

    public static function verifyPanelPin(string $plain): bool
    {
        $hash = self::panelPinHash();

        return $hash !== null && Hash::check($plain, $hash);
    }

    public static function verifyStaffPin(string $plain): ?AdminUser
    {
        return AdminUser::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'role', 'access_code_hash'])
            ->first(fn (AdminUser $user): bool => Hash::check($plain, (string) $user->access_code_hash));
    }

    /**
     * @return array{user: AdminUser|null}|null
     */
    public static function authenticate(string $provided): ?array
    {
        if (self::verifyPanelPin($provided)) {
            return ['user' => null];
        }

        $user = self::verifyStaffPin($provided);
        if ($user !== null) {
            return ['user' => $user];
        }

        if (! self::hasPanelPin() && self::legacyEnvMatches($provided)) {
            self::setPanelPin($provided);

            return ['user' => null];
        }

        return null;
    }

    public static function authorizeSensitive(Request $request, string $provided): bool
    {
        if (self::verifyPanelPin($provided)) {
            return true;
        }

        if (self::legacyEnvMatches($provided)) {
            return true;
        }

        $adminUserId = (int) $request->session()->get('admin_user_id', 0);
        if ($adminUserId > 0) {
            $user = AdminUser::query()->whereKey($adminUserId)->first(['id', 'access_code_hash']);
            if ($user && Hash::check($provided, (string) $user->access_code_hash)) {
                return true;
            }
        }

        return false;
    }

    private static function legacyEnvConfigured(): bool
    {
        return self::legacyEnvSecret() !== '' || self::legacyEnvPin() !== '';
    }

    private static function legacyEnvMatches(string $provided): bool
    {
        $secret = self::legacyEnvSecret();
        $pin = self::legacyEnvPin();

        return ($secret !== '' && hash_equals($secret, $provided))
            || ($pin !== '' && hash_equals($pin, $provided));
    }

    private static function legacyEnvSecret(): string
    {
        return (string) config('colldett.admin.access_secret', '');
    }

    private static function legacyEnvPin(): string
    {
        return (string) config('colldett.admin.access_pin', '');
    }
}
