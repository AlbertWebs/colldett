@extends('admin.layouts.app')

@section('content')
@php
    $totalInsights = count($items ?? []);
    $latestInsight = $totalInsights ? ($items[0]['title'] ?? 'N/A') : 'N/A';
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
                <h2 class="text-2xl font-bold">Insights Management</h2>
                <p class="text-sm text-admin-muted">Publish, refine, and maintain editorial insight resources for your public site.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="admin-chip">EDITORIAL</span>
                <a href="{{ route('admin.insights.create') }}" class="admin-btn-primary">Create Insight</a>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="admin-stat">
            <p class="text-xs uppercase tracking-wide text-admin-muted">Total Insights</p>
            <p class="mt-2 text-xl font-semibold">{{ $totalInsights }}</p>
        </article>
        <article class="admin-stat">
            <p class="text-xs uppercase tracking-wide text-admin-muted">Latest Publication</p>
            <p class="mt-2 text-sm font-semibold">{{ $latestInsight }}</p>
        </article>
        <article class="admin-stat">
            <p class="text-xs uppercase tracking-wide text-admin-muted">Editorial Status</p>
            <p class="mt-2"><span class="admin-status-chip admin-status-chip-active">Up to date</span></p>
        </article>
    </div>

    <article class="admin-card p-4">
        <div class="grid gap-3 md:grid-cols-6">
            <input class="admin-input md:col-span-3" placeholder="Search insight title or slug..." />
            <input class="admin-input md:col-span-1" type="date" />
            <select class="admin-select md:col-span-1">
                <option>Any Status</option>
                <option>Published</option>
                <option>Draft</option>
            </select>
            <button class="admin-btn-soft md:col-span-1">Filter</button>
        </div>
    </article>

    <article class="admin-card p-0 overflow-hidden">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Article</th><th>Slug</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>
                                <div class="font-medium text-admin-ink">{{ $item['title'] }}</div>
                                <div class="text-xs text-admin-muted">{{ \Illuminate\Support\Str::limit($item['excerpt'] ?? '', 90) }}</div>
                            </td>
                            <td>{{ $item['slug'] }}</td>
                            <td>{{ $item['date'] }}</td>
                            <td><span class="admin-status-chip admin-status-chip-active">Published</span></td>
                            <td>
                                <div class="admin-row-actions">
                                    <a href="{{ route('insights.show', $item['slug']) }}" class="admin-link-btn" target="_blank" rel="noopener noreferrer">View Live</a>
                                    <a href="{{ route('admin.insights.edit', $item['id']) }}" class="admin-link-btn">Edit</a>
                                    <a href="{{ route('admin.insights.delete-confirm', $item['id']) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($totalInsights === 0)
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-admin-muted">No insights available. Create your first insight to get started.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between p-4 text-sm text-admin-muted">
            <span>Showing {{ $totalInsights }} {{ \Illuminate\Support\Str::plural('insight', $totalInsights) }}</span>
            <div class="flex gap-2">
                <button class="admin-btn-soft">Prev</button>
                <button class="admin-btn-soft">Next</button>
            </div>
        </div>
    </article>
</section>
@endsection
