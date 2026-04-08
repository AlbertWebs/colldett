@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Client Management</h2>
            <p class="text-sm text-admin-muted">Add, edit, view and manage client records.</p>
        </div>
        <a href="{{ route('admin.clients.create') }}" class="admin-btn-primary">Add Client</a>
    </div>

    <form method="GET" action="{{ route('admin.clients') }}" class="admin-card p-5">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3 border-b border-admin-border pb-4">
            <div>
                <h3 class="text-sm font-semibold text-admin-ink">Find clients</h3>
                <p class="mt-0.5 text-xs text-admin-muted">Filter by keywords or account status.</p>
            </div>
        </div>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:gap-4">
            <div class="min-w-0 flex-1 space-y-1.5">
                <label for="client-filter-q" class="block text-xs font-semibold uppercase tracking-wide text-admin-muted">Search</label>
                <input
                    id="client-filter-q"
                    class="admin-input w-full"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    type="search"
                    autocomplete="off"
                    placeholder="Name, company, email, phone, account #, notes…"
                />
            </div>
            <div class="w-full space-y-1.5 sm:max-w-[14rem] lg:w-44 lg:max-w-none lg:shrink-0">
                <label for="client-filter-status" class="block text-xs font-semibold uppercase tracking-wide text-admin-muted">Status</label>
                <select id="client-filter-status" class="admin-select w-full" name="status">
                    <option value="">All statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-2 lg:shrink-0 lg:pb-0.5">
                <button type="submit" class="admin-btn-soft min-w-[7rem]">Apply filters</button>
                <a href="{{ route('admin.clients') }}" class="admin-btn-soft inline-flex min-w-[7rem] items-center justify-center">Reset</a>
            </div>
        </div>
    </form>

    <article class="admin-card p-0">
        <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Account Number</th>
                    <th>Status</th>
                    <th class="whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients ?? [] as $row)
                    <tr>
                        <td>{{ $row['name'] ?? '—' }}</td>
                        <td>{{ $row['company'] ?? '—' }}</td>
                        <td>{{ $row['email'] ?? '—' }}</td>
                        <td>{{ $row['phone'] ?? '—' }}</td>
                        <td>{{ $row['account_number'] ?? '—' }}</td>
                        <td>
                            @if(($row['status'] ?? '') === 'active')
                                <span class="admin-status-chip admin-status-chip-active">Active</span>
                            @else
                                <span class="admin-status-chip bg-slate-100 text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.clients.show', $row['id']) }}" class="admin-link-btn">View</a>
                                <a href="{{ route('admin.clients.edit', $row['id']) }}" class="admin-link-btn">Edit</a>
                                <a href="{{ route('admin.clients.delete-confirm', $row['id']) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-admin-muted">No clients match your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </article>
</section>
@endsection
