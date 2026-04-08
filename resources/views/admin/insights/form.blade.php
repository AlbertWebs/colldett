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
            <h2 class="text-2xl font-bold">{{ $mode === 'create' ? 'Create' : 'Edit' }} Insight</h2>
            <p class="text-sm text-admin-muted">Manage insight article content.</p>
        </div>
        <a href="{{ route('admin.insights.index') }}" class="admin-btn-soft">Back to Insights</a>
    </div>

    <article class="admin-card p-6 max-w-4xl">
        <form method="POST" action="{{ $mode === 'create' ? route('admin.insights.store') : route('admin.insights.update', $item['id']) }}" class="space-y-4">
            @csrf
            @if($mode === 'edit')
                @method('PATCH')
            @endif
            <div>
                <label for="insight-title" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Title</label>
                <input id="insight-title" class="admin-input" name="title" value="{{ old('title', $item['title']) }}" placeholder="Title" />
            </div>
            <div>
                <label for="insight-slug" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Slug</label>
                <input id="insight-slug" class="admin-input" name="slug" value="{{ old('slug', $item['slug']) }}" placeholder="Slug" />
            </div>
            <div>
                <label for="insight-date" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Date</label>
                <input id="insight-date" class="admin-input" type="date" name="date" value="{{ old('date', $item['date']) }}" />
            </div>
            <div>
                <label for="insight-excerpt" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Excerpt</label>
                <textarea id="insight-excerpt" class="admin-input min-h-24" name="excerpt" placeholder="Excerpt">{{ old('excerpt', $item['excerpt']) }}</textarea>
            </div>
            <div>
                <label for="insight-content" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Content</label>
                <textarea id="insight-content" class="admin-input min-h-40" name="content" placeholder="Content">{{ old('content', $item['content']) }}</textarea>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.insights.index') }}" class="admin-btn-soft">Cancel</a>
                <button type="submit" class="admin-btn-primary">{{ $mode === 'create' ? 'Create' : 'Update' }} Insight</button>
            </div>
        </form>
    </article>
</section>
@endsection
