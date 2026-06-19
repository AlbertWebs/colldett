@extends('admin.layouts.app')

@section('content')
@php
    $total = $items->count();
@endphp
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Careers Management</h2>
                <p class="text-sm text-admin-muted">Publish open positions and manage applications from the careers page.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.career-applications.index') }}" class="admin-btn-soft">View Applications</a>
                <a href="{{ route('admin.careers.create') }}" class="admin-btn-primary">Create Position</a>
            </div>
        </div>
    </div>

    <article class="admin-card p-0 overflow-hidden">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>
                                <div class="font-medium text-admin-ink">{{ $item->title }}</div>
                                <div class="text-xs text-admin-muted">{{ $item->slug }}</div>
                            </td>
                            <td>{{ $item->location ?: '—' }}</td>
                            <td>{{ $item->employment_type ?: '—' }}</td>
                            <td>{{ $item->applications_count }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="admin-status-chip admin-status-chip-active">Active</span>
                                @else
                                    <span class="admin-status-chip">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="admin-row-actions">
                                    <a href="{{ route('careers.show', $item->slug) }}" class="admin-link-btn" target="_blank" rel="noopener noreferrer">View Live</a>
                                    <a href="{{ route('admin.careers.edit', $item->id) }}" class="admin-link-btn">Edit</a>
                                    <a href="{{ route('admin.careers.delete-confirm', $item->id) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($total === 0)
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-admin-muted">No positions yet. Create your first opening to get started.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
