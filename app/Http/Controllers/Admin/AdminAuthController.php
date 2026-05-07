<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLoginForm(Request $request): RedirectResponse|View
    {
        if ($request->session()->get($this->sessionKey()) === true) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'access_code' => ['required', 'string', 'max:500'],
        ]);

        $provided = (string) $request->input('access_code', '');
        $secret = (string) config('colldett.admin.access_secret', '');
        $pin = (string) config('colldett.admin.access_pin', '');

        $hasAnyAdminUsers = AdminUser::query()->where('is_active', true)->exists();

        if ($secret === '' && $pin === '' && ! $hasAnyAdminUsers) {
            return back()
                ->withErrors(['access_code' => 'Admin access is not configured. Set ADMIN_ACCESS_SECRET or ADMIN_ACCESS_PIN in your environment file.'])
                ->onlyInput('access_code');
        }

        $valid = ($secret !== '' && hash_equals($secret, $provided))
            || ($pin !== '' && hash_equals($pin, $provided));

        $user = null;
        if (! $valid) {
            $user = AdminUser::query()
                ->where('is_active', true)
                ->get(['id', 'name', 'email', 'role', 'access_code_hash'])
                ->first(fn (AdminUser $u) => Hash::check($provided, $u->access_code_hash));
            $valid = $user !== null;
        }

        if (! $valid) {
            return back()
                ->withErrors(['access_code' => 'Incorrect password or PIN.'])
                ->onlyInput('access_code');
        }

        $request->session()->put($this->sessionKey(), true);
        if ($user) {
            $request->session()->put('admin_user_id', $user->id);
            $request->session()->put('admin_user_name', $user->name);
            $request->session()->put('admin_user_role', $user->role);
            $user->forceFill(['last_login_at' => now()])->save();
        } else {
            $request->session()->forget(['admin_user_id', 'admin_user_name', 'admin_user_role']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget($this->sessionKey());
        $request->session()->forget(['admin_user_id', 'admin_user_name', 'admin_user_role']);
        $request->session()->regenerate();

        return redirect()->route('admin.login');
    }

    private function sessionKey(): string
    {
        return (string) config('colldett.admin.session_key', 'admin_panel_authenticated');
    }
}
