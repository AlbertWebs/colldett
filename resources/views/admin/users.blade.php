@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">User Management</h2>
                <p class="text-sm text-admin-muted">Manage admin accounts, assign roles, and control access status.</p>
            </div>
            <button class="admin-btn-primary">Create User</button>
        </div>
    </div>

    <article class="admin-card p-4">
        <form method="GET" action="{{ route('admin.users') }}" class="grid gap-3 md:grid-cols-6">
            <input
                class="admin-input md:col-span-3"
                name="q"
                value="{{ $filters['q'] ?? '' }}"
                placeholder="Search by name or email..."
            />
            <select class="admin-select md:col-span-1" name="role">
                <option value="">All Roles</option>
                <option value="Admin" @selected(($filters['role'] ?? '') === 'Admin')>Admin</option>
                <option value="Manager" @selected(($filters['role'] ?? '') === 'Manager')>Manager</option>
                <option value="Viewer" @selected(($filters['role'] ?? '') === 'Viewer')>Viewer</option>
            </select>
            <select class="admin-select md:col-span-1" name="status">
                <option value="">Any Status</option>
                <option value="Active" @selected(($filters['status'] ?? '') === 'Active')>Active</option>
                <option value="Suspended" @selected(($filters['status'] ?? '') === 'Suspended')>Suspended</option>
            </select>
            <div class="flex gap-2 md:col-span-1">
                <button type="submit" class="admin-btn-soft w-full">Filter</button>
                <a href="{{ route('admin.users') }}" class="admin-btn-soft">Reset</a>
            </div>
        </form>
    </article>

    <article class="admin-card p-0">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($users ?? []) as $user)
                        <tr>
                            <td>
                                <div class="font-medium text-admin-ink">{{ $user['name'] }}</div>
                                <div class="text-xs text-admin-muted">Last login: {{ $user['last_login'] }}</div>
                            </td>
                            <td>{{ $user['email'] }}</td>
                            <td>{{ $user['role'] }}</td>
                            <td><span class="admin-status-chip admin-status-chip-active">{{ $user['status'] }}</span></td>
                            <td>
                                <div class="admin-row-actions">
                                    <a href="{{ route('admin.users.show', $user['id']) }}" class="admin-link-btn">View</a>
                                    <a href="{{ route('admin.users.edit', $user['id']) }}" class="admin-link-btn">Edit</a>
                                    <a href="{{ route('admin.users.delete-confirm', $user['id']) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if(empty($users))
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm text-admin-muted">No users match the selected filters.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div>
            <div class="flex items-center justify-between p-4 text-sm text-admin-muted">
                <span>Showing {{ count($users ?? []) }} of {{ $totalUsers ?? count($users ?? []) }} users</span>
                <div class="flex gap-2">
                    <button class="admin-btn-soft">Prev</button>
                    <button class="admin-btn-soft">Next</button>
                </div>
            </div>
        </div>
    </article>
</section>
@endsection
