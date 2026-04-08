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

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Website Content CMS</h2>
            <p class="text-sm text-admin-muted">Manage Home, About, Services, Team, Contact, Blog, Testimonials and FAQs.</p>
        </div>
        <a class="admin-btn-primary" href="{{ route('admin.website-content.create') }}">Create Content</a>
    </div>

    <form method="GET" action="{{ route('admin.website-content') }}" class="admin-card p-4">
        <div class="grid gap-3 md:grid-cols-4">
            <input class="admin-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by title/slug..." />
            <select class="admin-select" name="module">
                <option value="">All Modules</option>
                @foreach(['Home','Services','Insights','About','FAQ'] as $module)
                    <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ $module }}</option>
                @endforeach
            </select>
            <select class="admin-select" name="status">
                <option value="">Any Status</option>
                <option value="Published" @selected(($filters['status'] ?? '') === 'Published')>Published</option>
                <option value="Draft" @selected(($filters['status'] ?? '') === 'Draft')>Draft</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="admin-btn-soft">Apply Filters</button>
                <a href="{{ route('admin.website-content') }}" class="admin-btn-soft">Reset</a>
            </div>
        </div>
    </form>

    <article class="admin-card p-0 overflow-hidden">
        <div class="admin-table-wrap !rounded-none !border-0">
        <table class="admin-table">
            <thead><tr><th>Page</th><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach(($items ?? []) as $item)
                    <tr>
                        <td>{{ $item['module'] }}</td>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['slug'] }}</td>
                        <td>
                            <span class="admin-status-chip {{ $item['status'] === 'Published' ? 'admin-status-chip-active' : 'admin-status-chip-draft' }}">
                                {{ $item['status'] }}
                            </span>
                        </td>
                        <td>{{ $item['date'] ?? '—' }}</td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.website-content.edit', $item['id']) }}" class="admin-link-btn">Edit</a>
                                <a href="{{ route('admin.website-content.delete-confirm', $item['id']) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if($count === 0)
                    <tr>
                        <td colspan="6" class="py-10 text-center text-sm text-admin-muted">No content entries found for selected filters.</td>
                    </tr>
                @endif
            </tbody>
        </table>
        </div>
        <div class="flex items-center justify-between p-4 text-sm text-admin-muted">
            <span>Showing {{ $count }} of {{ $total ?? $count }}</span>
            <div class="flex gap-2"><button class="admin-btn-soft">Prev</button><button class="admin-btn-soft">Next</button></div>
        </div>
    </article>
</section>
@endsection
