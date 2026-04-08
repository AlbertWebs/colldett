@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">User Profile</h2>
            <p class="text-sm text-admin-muted">View user details and account information.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users') }}" class="admin-btn-soft">Back to Users</a>
            <a href="{{ route('admin.users.edit', $user['id']) }}" class="admin-btn-primary">Edit User</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <article class="admin-card p-6 xl:col-span-2">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-admin-muted">Account Summary</p>
                    <h3 class="mt-1 text-lg font-semibold text-admin-ink">{{ $user['name'] }}</h3>
                    <p class="text-sm text-admin-muted">{{ $user['email'] }}</p>
                </div>
                <span class="admin-status-chip {{ $user['status'] === 'Active' ? 'admin-status-chip-active' : 'admin-status-chip-draft' }}">{{ $user['status'] }}</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-admin-muted">Role</p>
                    <p class="mt-1 text-sm font-medium text-admin-ink">{{ $user['role'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-admin-muted">Last Login</p>
                    <p class="mt-1 text-sm font-medium text-admin-ink">{{ $user['last_login'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-admin-muted">2FA</p>
                    <p class="mt-1 text-sm font-medium text-admin-ink">Not Enabled</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-admin-muted">Session</p>
                    <p class="mt-1 text-sm font-medium text-admin-ink">Web Console</p>
                </div>
            </div>

            <div class="mt-6 border-t border-admin-border pt-4">
                <p class="mb-2 text-xs uppercase tracking-wide text-admin-muted">Recent Activity</p>
                <ul class="space-y-2 text-sm text-admin-muted">
                    <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Updated invoice permissions - 2 days ago</li>
                    <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Logged in from Nairobi office - 1 day ago</li>
                    <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Exported report snapshot - Today</li>
                </ul>
            </div>
        </article>

        <aside class="admin-card p-5 space-y-3">
            <h3 class="admin-card-title">Actions</h3>
            <form method="POST" action="{{ route('admin.users.toggle-status', $user['id']) }}">
                @csrf
                <button type="submit" class="admin-btn-soft w-full justify-center">
                    {{ $user['status'] === 'Active' ? 'Suspend User' : 'Activate User' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.users.reset-password', $user['id']) }}">
                @csrf
                <button type="submit" class="admin-btn-soft w-full justify-center">Send Password Reset</button>
            </form>
            <a href="{{ route('admin.users.edit', $user['id']) }}" class="admin-btn-soft w-full justify-center">Edit Profile</a>
            <a href="{{ route('admin.users.delete-confirm', $user['id']) }}" class="admin-btn-soft w-full justify-center !border-rose-200 !text-rose-700 hover:!bg-rose-50">Delete User</a>
        </aside>
    </div>
</section>
@endsection
