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
                <h2 class="text-2xl font-bold">Career Applications</h2>
                <p class="text-sm text-admin-muted">Review submissions from the public careers page.</p>
            </div>
            <a href="{{ route('admin.careers.index') }}" class="admin-btn-soft">Manage Positions</a>
        </div>
    </div>

    <article class="admin-card p-4">
        <form method="GET" action="{{ route('admin.career-applications.index') }}" class="grid gap-3 md:grid-cols-4">
            <select class="admin-select md:col-span-3" name="career_id">
                <option value="">All positions</option>
                @foreach($careers as $career)
                    <option value="{{ $career->id }}" @selected((int) $careerId === $career->id)>{{ $career->title }}</option>
                @endforeach
            </select>
            <button class="admin-btn-soft" type="submit">Filter</button>
        </form>
    </article>

    <article class="admin-card p-0 overflow-hidden">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Submitted</th>
                        <th>Applicant</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $application)
                        <tr>
                            <td>{{ $application->created_at?->format('j M Y H:i') }}</td>
                            <td>
                                <div class="font-medium text-admin-ink">{{ $application->name }}</div>
                                <div class="text-xs text-admin-muted">{{ $application->email }}</div>
                            </td>
                            <td>{{ $application->career?->title ?? '—' }}</td>
                            <td><span class="admin-status-chip">{{ ucfirst($application->status) }}</span></td>
                            <td>
                                <a href="{{ route('admin.career-applications.show', $application->id) }}" class="admin-link-btn">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-admin-muted">No applications yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
            <div class="p-4">{{ $applications->links() }}</div>
        @endif
    </article>
</section>
@endsection
