@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Security</h2>
                <p class="mt-1 text-sm text-admin-muted">Manage admin PINs and sensitive maintenance actions.</p>
            </div>
            <span class="admin-chip">Access control</span>
        </div>
    </div>

    @include('admin.settings._nav')
    @include('admin.settings._status')

    @if($usesPanelAccess)
        <article class="admin-card max-w-3xl space-y-4 p-5">
            <div>
                <h3 class="admin-card-title text-base">Panel PIN</h3>
                <p class="mt-1 text-xs text-admin-muted">
                    @if($panelPinConfigured)
                        Change the shared admin panel PIN stored on this server (not in <code class="rounded bg-slate-100 px-1">.env</code>).
                    @else
                        Set the shared admin panel PIN. It is stored securely on the server and used for sign-in at <code class="rounded bg-slate-100 px-1">/admin</code>.
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('admin.settings.security.panel-pin') }}" class="grid gap-3 max-w-xl">
                @csrf
                @if($panelPinConfigured)
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted" for="panel_current_access_code">Current panel PIN</label>
                        <input id="panel_current_access_code" class="admin-input" type="password" name="current_access_code" autocomplete="current-password" required />
                        @error('current_access_code')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted" for="panel_access_code">{{ $panelPinConfigured ? 'New panel PIN' : 'Panel PIN' }}</label>
                    <input id="panel_access_code" class="admin-input" type="password" name="access_code" autocomplete="new-password" minlength="4" required />
                    @error('access_code')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                    @error('panel_pin')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted" for="panel_access_code_confirmation">Confirm panel PIN</label>
                    <input id="panel_access_code_confirmation" class="admin-input" type="password" name="access_code_confirmation" autocomplete="new-password" minlength="4" required />
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="admin-btn-primary">{{ $panelPinConfigured ? 'Update panel PIN' : 'Set panel PIN' }}</button>
                </div>
            </form>
            <p class="text-xs text-admin-muted">For per-user access, create staff accounts under <a href="{{ route('admin.users') }}" class="font-semibold text-admin-ink underline">Users</a>.</p>
        </article>
    @else
        <article class="admin-card max-w-3xl space-y-4 p-5">
            <div>
                <h3 class="admin-card-title text-base">Change PIN / password</h3>
                <p class="mt-1 text-xs text-admin-muted">Update the login PIN or password for <strong>{{ $adminUser->name }}</strong> ({{ $adminUser->email }}).</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.security.pin') }}" class="grid gap-3 max-w-xl">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted" for="current_access_code">Current PIN / password</label>
                    <input id="current_access_code" class="admin-input" type="password" name="current_access_code" autocomplete="current-password" required />
                    @error('current_access_code')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted" for="access_code">New PIN / password</label>
                    <input id="access_code" class="admin-input" type="password" name="access_code" autocomplete="new-password" minlength="4" required />
                    @error('access_code')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted" for="access_code_confirmation">Confirm new PIN / password</label>
                    <input id="access_code_confirmation" class="admin-input" type="password" name="access_code_confirmation" autocomplete="new-password" minlength="4" required />
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="admin-btn-primary">Update PIN / password</button>
                </div>
            </form>
        </article>
    @endif

    <article class="admin-card max-w-3xl space-y-4 border-rose-200 bg-rose-50/40 p-5">
        <div>
            <h3 class="admin-card-title text-base text-rose-800">Danger zone</h3>
            <p class="mt-1 text-xs text-rose-800/80">Purge test/management data (clients, cases, billing sequences) and wipe inbound inquiries. This cannot be undone.</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.purge-test-data') }}" class="grid gap-3 md:grid-cols-12">
            @csrf
            <div class="space-y-1.5 md:col-span-4">
                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Type PURGE to confirm</label>
                <input class="admin-input" name="confirm" value="{{ old('confirm') }}" placeholder="PURGE" required />
            </div>
            <div class="space-y-1.5 md:col-span-5">
                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Re-enter admin PIN / password</label>
                <input class="admin-input" type="password" name="access_code" placeholder="PIN or password" required />
                @error('purge_access_code')
                    <p class="text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-end justify-end md:col-span-3">
                <button
                    type="submit"
                    class="admin-btn-primary !w-full justify-center !bg-rose-600 hover:!bg-rose-700"
                    onclick="return confirm('This will permanently delete test/management data. Continue?')"
                >
                    Purge test data
                </button>
            </div>
        </form>
    </article>
</section>
@endsection
