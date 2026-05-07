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
                <h2 class="text-2xl font-bold">Gallery</h2>
                <p class="text-sm text-admin-muted">Upload images, set captions, and control what appears on the public gallery page.</p>
            </div>
            <a class="admin-btn-primary" href="{{ route('admin.gallery.create') }}">Add gallery item</a>
        </div>
    </div>

    <article class="admin-card p-5">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($items as $item)
                @php
                    $src = str_starts_with($item->image_path, 'http')
                        ? $item->image_path
                        : asset(ltrim($item->image_path, '/'));
                @endphp
                <div class="rounded-xl border border-admin-border bg-white p-3">
                    <img src="{{ $src }}" alt="" class="h-44 w-full rounded-lg border border-admin-border object-cover bg-slate-50">
                    <div class="mt-3 space-y-1">
                        <p class="text-sm font-semibold text-admin-ink">{{ $item->caption ?: '—' }}</p>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-admin-muted">
                            <span>Sort: <strong class="text-admin-ink">{{ $item->sort_order }}</strong></span>
                            <span class="admin-status-chip {{ $item->is_active ? 'admin-status-chip-active' : 'admin-status-chip-draft' }}">
                                {{ $item->is_active ? 'Active' : 'Hidden' }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a class="admin-btn-soft" href="{{ route('admin.gallery.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}" onsubmit="return confirm('Delete this gallery item?')">
                            @csrf
                            @method('DELETE')
                            <button class="admin-btn-soft" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-admin-border bg-slate-50 p-6 text-center sm:col-span-2 xl:col-span-3">
                    <p class="text-sm text-admin-muted">No gallery items yet. Click <strong>Add gallery item</strong> to upload your first image.</p>
                </div>
            @endforelse
        </div>
    </article>
</section>
@endsection

