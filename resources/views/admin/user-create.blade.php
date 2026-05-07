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
            <h2 class="text-2xl font-bold">Create User</h2>
            <p class="text-sm text-admin-muted">Add a new admin user who can login with their own PIN/password.</p>
        </div>
        <a href="{{ route('admin.users') }}" class="admin-btn-soft">Back to Users</a>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6 max-w-3xl">
        @csrf

        <article class="admin-card p-6 space-y-4">
            <h3 class="admin-card-title">Identity</h3>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Name</label>
                    <input class="admin-input" name="name" value="{{ old('name') }}" placeholder="Full name" required />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Email</label>
                    <input class="admin-input" type="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Role</label>
                    <select class="admin-select" name="role" required>
                        <option value="Admin" @selected(old('role', 'Admin') === 'Admin')>Admin</option>
                        <option value="Manager" @selected(old('role') === 'Manager')>Manager</option>
                        <option value="Viewer" @selected(old('role') === 'Viewer')>Viewer</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Status</label>
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-2 rounded-lg border border-admin-border bg-slate-50 px-3 py-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) />
                        <span class="text-admin-ink font-medium">Active</span>
                    </label>
                </div>
            </div>
        </article>

        <article class="admin-card p-6 space-y-3">
            <h3 class="admin-card-title">Login PIN / Password</h3>
            <p class="text-sm text-admin-muted">This is what the user will enter on `/admin/login`. Share it securely.</p>
            <div class="space-y-1.5">
                <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Access code</label>
                <input class="admin-input" name="access_code" value="" placeholder="e.g. 29385876 or a strong password" required />
            </div>
        </article>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.users') }}" class="admin-btn-soft">Cancel</a>
            <button type="submit" class="admin-btn-primary">Create User</button>
        </div>
    </form>
</section>
@endsection

