@extends('admin.layouts.app')

@section('content')
@php
    $count = count($items ?? []);
@endphp
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">Case Management</h2>
            <p class="text-sm text-admin-muted">Create, update, close cases and track next actions.</p>
        </div>
        <a href="{{ route('admin.cases.create') }}" class="admin-btn-primary">Create Case</a>
    </div>

    <form method="GET" action="{{ route('admin.cases') }}" class="admin-card p-4">
        <div class="grid gap-3 md:grid-cols-4">
            <input class="admin-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search case number, client, debtor..." />
            <select class="admin-select" name="status">
                <option value="">Any Status</option>
                @foreach(['Pending','In Progress','Closed'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <select class="admin-select" name="officer">
                <option value="">Any Officer</option>
                @foreach(($officers ?? []) as $officer)
                    <option value="{{ $officer }}" @selected(($filters['officer'] ?? '') === $officer)>{{ $officer }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="admin-btn-soft" type="submit">Apply Filters</button>
                <a href="{{ route('admin.cases') }}" class="admin-btn-soft">Reset</a>
            </div>
        </div>
    </form>

    <article class="admin-card p-0">
        <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Client</th>
                    <th>Debtor</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="whitespace-nowrap">Close case</th>
                    <th>Assigned Officer</th>
                    <th>Next Action Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($items ?? []) as $item)
                    <tr>
                        <td><a href="{{ route('admin.cases.show', $item['id']) }}" class="admin-link-btn">{{ $item['case_number'] }}</a></td>
                        <td>{{ $item['client'] }}</td>
                        <td>{{ $item['debtor'] }}</td>
                        <td>{{ $item['amount'] }}</td>
                        <td>
                            <span class="admin-status-chip {{ $item['status'] === 'Pending' ? 'admin-status-chip-pending' : ($item['status'] === 'In Progress' ? 'admin-status-chip-progress' : 'admin-status-chip-active') }}">
                                {{ $item['status'] }}
                            </span>
                        </td>
                        <td class="align-middle">
                            @if($item['status'] === 'Closed')
                                <div class="inline-flex items-center gap-2" title="Case is closed">
                                    <span class="relative inline-block h-6 w-11 shrink-0 rounded-full bg-emerald-600 shadow-inner" role="img" aria-label="Closed">
                                        <span class="absolute right-0.5 top-0.5 block h-5 w-5 rounded-full bg-white shadow"></span>
                                    </span>
                                    <span class="text-xs font-medium text-emerald-700">Closed</span>
                                </div>
                            @else
                                <form method="POST" action="{{ route('admin.cases.close', $item['id']) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    <label class="group relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center">
                                        <input
                                            type="checkbox"
                                            class="peer sr-only"
                                            aria-label="Close case {{ $item['case_number'] }}"
                                            onchange="if (this.checked) { this.form.submit(); }"
                                        />
                                        <span class="block h-6 w-11 rounded-full bg-slate-300 transition-colors duration-200 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-admin-primary peer-checked:bg-emerald-600"></span>
                                        <span class="pointer-events-none absolute left-0.5 top-0.5 block h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 will-change-transform peer-checked:translate-x-5"></span>
                                    </label>
                                    <span class="text-xs text-admin-muted max-sm:hidden">Flip to close</span>
                                </form>
                            @endif
                        </td>
                        <td>{{ $item['officer'] }}</td>
                        <td>{{ $item['next_action_date'] }}</td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.cases.edit', $item['id']) }}" class="admin-link-btn">Update</a>
                                <a href="{{ route('admin.cases.show', $item['id']) }}" class="admin-link-btn">Add Note</a>
                                @if($item['status'] === 'Closed')
                                    <a href="{{ route('admin.cases.delete-confirm', $item['id']) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-sm text-admin-muted">No cases found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="border-t border-admin-border px-4 py-3 text-sm text-admin-muted">
            Showing {{ $count }} of {{ $total ?? $count }} cases
        </div>
    </article>
</section>
@endsection
