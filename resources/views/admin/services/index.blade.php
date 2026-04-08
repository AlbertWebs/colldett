@extends('admin.layouts.app')

@section('content')
@php
    $serviceCount = count($items ?? []);
@endphp
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Services Management</h2>
            <p class="text-sm text-admin-muted">Manage all public-facing services with a cleaner editor and searchable listing.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="admin-chip">{{ $serviceCount }} visible</span>
            <a href="{{ route('admin.services.create') }}" class="admin-btn-primary">Create Service</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <article class="admin-card p-4">
            <p class="text-xs uppercase tracking-wide text-admin-muted">Total Services</p>
            <p class="mt-2 text-2xl font-bold text-admin-text">{{ $total ?? $serviceCount }}</p>
        </article>
        <article class="admin-card p-4">
            <p class="text-xs uppercase tracking-wide text-admin-muted">Filtered Results</p>
            <p class="mt-2 text-2xl font-bold text-admin-text">{{ $serviceCount }}</p>
        </article>
        <article class="admin-card p-4">
            <p class="text-xs uppercase tracking-wide text-admin-muted">Coverage</p>
            <p class="mt-2 text-sm text-admin-muted">Debt recovery, tracing, investigations and portfolio support services.</p>
        </article>
    </div>

    <form method="GET" action="{{ route('admin.services.index') }}" class="admin-card p-4">
        <div class="grid gap-3 md:grid-cols-[1fr_auto_auto]">
            <input class="admin-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name, slug or description..." />
            <button type="submit" class="admin-btn-soft">Apply Search</button>
            <a href="{{ route('admin.services.index') }}" class="admin-btn-soft">Reset</a>
        </div>
    </form>

    <article class="admin-card overflow-hidden p-0">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Service</th><th>Featured Image</th><th>Slug</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>
                            <div>
                                <p class="font-semibold text-admin-text">{{ $item['name'] }}</p>
                                <p class="text-xs text-admin-muted">Service ID: #{{ $item['id'] }}</p>
                            </div>
                        </td>
                        <td>
                            @php
                                $image = $item['image'] ?? '';
                                $imageUrl = $image ? (str_starts_with($image, 'http') ? $image : asset($image)) : null;
                            @endphp
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $item['name'] }} image" class="h-12 w-20 rounded border border-admin-border object-cover bg-white" />
                            @else
                                <span class="text-xs text-admin-muted">No image</span>
                            @endif
                        </td>
                        <td><code class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $item['slug'] }}</code></td>
                        <td class="max-w-xl">
                            <p class="text-sm text-admin-muted">{{ \Illuminate\Support\Str::limit($item['description'], 140) }}</p>
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.services.edit', $item['id']) }}" class="admin-link-btn">Edit</a>
                                <a href="{{ route('admin.services.delete-confirm', $item['id']) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-sm text-admin-muted">No services match the current search. Try a different keyword.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-admin-border px-4 py-3 text-sm text-admin-muted">
            Showing {{ $serviceCount }} of {{ $total ?? $serviceCount }} services
        </div>
    </article>
</section>
@endsection
