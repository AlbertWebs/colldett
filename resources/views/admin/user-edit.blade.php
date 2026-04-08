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
        <a href="{{ route('admin.users.show', $user['id']) }}" class="admin-btn-soft">Back to Profile</a>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user['id']) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="admin-card p-6 space-y-4">
                <h3 class="admin-card-title">Identity & Contact</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input class="admin-input" name="name" value="{{ old('name', $user['name']) }}" placeholder="Full Name" />
                    <input class="admin-input" name="email" value="{{ old('email', $user['email']) }}" placeholder="Email Address" />
                    <input class="admin-input" name="phone" value="{{ old('phone', $user['phone']) }}" placeholder="Phone Number" />
                    <input class="admin-input" name="employee_id" value="{{ old('employee_id', $user['employee_id']) }}" placeholder="Employee ID" />
                    <input class="admin-input sm:col-span-2" name="department" value="{{ old('department', $user['department']) }}" placeholder="Department" />
                    <input class="admin-input sm:col-span-2" name="job_title" value="{{ old('job_title', $user['job_title']) }}" placeholder="Job Title" />
                </div>
            </article>

            <article class="admin-card p-6 space-y-4">
                <h3 class="admin-card-title">Access & Account State</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select class="admin-select" name="role">
                        <option value="Admin" @selected(old('role', $user['role']) === 'Admin')>Admin</option>
                        <option value="Manager" @selected(old('role', $user['role']) === 'Manager')>Manager</option>
                        <option value="Viewer" @selected(old('role', $user['role']) === 'Viewer')>Viewer</option>
                    </select>
                    <select class="admin-select" name="status">
                        <option value="Active" @selected(old('status', $user['status']) === 'Active')>Active</option>
                        <option value="Suspended" @selected(old('status', $user['status']) === 'Suspended')>Suspended</option>
                    </select>
                    <input class="admin-input" name="timezone" value="{{ old('timezone', $user['timezone']) }}" placeholder="Timezone" />
                    <input class="admin-input" name="language" value="{{ old('language', $user['language']) }}" placeholder="Language" />
                </div>

                <div class="grid gap-2 rounded-lg border border-admin-border bg-slate-50 p-3 text-sm">
                    <label class="inline-flex items-center gap-2"><input type="hidden" name="two_factor_enabled" value="0"><input type="checkbox" name="two_factor_enabled" value="1" @checked(old('two_factor_enabled', $user['two_factor_enabled']))> Two-factor authentication enabled</label>
                    <label class="inline-flex items-center gap-2"><input type="hidden" name="email_verified" value="0"><input type="checkbox" name="email_verified" value="1" @checked(old('email_verified', $user['email_verified']))> Email verified</label>
                </div>
            </article>

            <article class="admin-card p-6 space-y-4 xl:col-span-2">
                <h3 class="admin-card-title">Permissions & Notes</h3>
                <div class="grid gap-2 sm:grid-cols-2 text-sm">
                    <label class="inline-flex items-center gap-2"><input type="hidden" name="can_manage_users" value="0"><input type="checkbox" name="can_manage_users" value="1" @checked(old('can_manage_users', $user['can_manage_users']))> Can manage users</label>
                    <label class="inline-flex items-center gap-2"><input type="hidden" name="can_manage_billing" value="0"><input type="checkbox" name="can_manage_billing" value="1" @checked(old('can_manage_billing', $user['can_manage_billing']))> Can manage billing</label>
                    <label class="inline-flex items-center gap-2"><input type="hidden" name="can_manage_cases" value="0"><input type="checkbox" name="can_manage_cases" value="1" @checked(old('can_manage_cases', $user['can_manage_cases']))> Can manage cases</label>
                    <label class="inline-flex items-center gap-2"><input type="hidden" name="can_publish_content" value="0"><input type="checkbox" name="can_publish_content" value="1" @checked(old('can_publish_content', $user['can_publish_content']))> Can publish content</label>
                </div>
                <textarea class="admin-input min-h-28" name="notes" placeholder="Internal notes about this user">{{ old('notes', $user['notes']) }}</textarea>
            </article>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.users.show', $user['id']) }}" class="admin-btn-soft">Cancel</a>
            <button type="submit" class="admin-btn-primary">Save Changes</button>
        </div>
    </form>
</section>
@endsection
