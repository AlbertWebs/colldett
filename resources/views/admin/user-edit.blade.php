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
            <h2 class="text-2xl font-bold">Edit User</h2>
            <p class="text-sm text-admin-muted">Update all editable account details, permissions, and access controls.</p>
        </div>
        <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-soft">Back to Profile</a>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="admin-card p-6 space-y-4">
                <h3 class="admin-card-title">Identity & Contact</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input class="admin-input" name="name" value="{{ old('name', $user->name) }}" placeholder="Full Name" />
                    <input class="admin-input" name="email" value="{{ old('email', $user->email) }}" placeholder="Email Address" />
                </div>
            </article>

            <article class="admin-card p-6 space-y-4">
                <h3 class="admin-card-title">Access & Account State</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select class="admin-select" name="role">
                        <option value="Admin" @selected(old('role', $user->role) === 'Admin')>Admin</option>
                        <option value="Manager" @selected(old('role', $user->role) === 'Manager')>Manager</option>
                        <option value="Viewer" @selected(old('role', $user->role) === 'Viewer')>Viewer</option>
                    </select>
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center gap-2 rounded-lg border border-admin-border bg-slate-50 px-3 py-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) />
                            <span class="text-admin-ink font-medium">Active</span>
                        </label>
                    </div>
                </div>

                <div class="rounded-lg border border-admin-border bg-slate-50 p-3 text-sm space-y-2">
                    <div class="text-xs uppercase tracking-wide text-admin-muted font-semibold">Reset user PIN / Password</div>
                    <input class="admin-input" name="access_code" value="" placeholder="Leave blank to keep current PIN/password" />
                    <p class="text-xs text-admin-muted">If you set a new value here, it becomes the user's login PIN/password.</p>
                </div>
            </article>

            <article class="admin-card p-6 space-y-4 xl:col-span-2">
                <h3 class="admin-card-title">Notes</h3>
                <p class="text-sm text-admin-muted">This simple admin user system stores only name, email, role, status, and a login PIN/password.</p>
            </article>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-soft">Cancel</a>
            <button type="submit" class="admin-btn-primary">Save Changes</button>
        </div>
    </form>
</section>
@endsection
