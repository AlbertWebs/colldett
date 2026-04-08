@extends('admin.layouts.app')

@section('content')
@php
    $isEdit = $mode === 'edit';
@endphp
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <article class="admin-card overflow-hidden p-0">
        <div class="border-b border-admin-panel-border bg-slate-50 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Content Editor</p>
            <h2 class="mt-1 text-2xl font-bold text-admin-text">
                {{ $isEdit ? 'Edit Website Content' : 'Create Website Content' }}
            </h2>
            <p class="mt-2 text-sm text-admin-muted">Update content blocks, metadata and publish state for website pages.</p>
        </div>

        <form method="POST" action="{{ $isEdit ? route('admin.website-content.update', $item['id']) : route('admin.website-content.store') }}" class="space-y-6 p-6">
            @csrf
            @if($isEdit)
                @method('PATCH')
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-label">Module</label>
                    <select class="admin-select @error('module') border-rose-300 @enderror" name="module">
                        @foreach(['Home','Services','Insights','About','FAQ','Contact','Team'] as $module)
                            <option value="{{ $module }}" @selected(old('module', $item['module']) === $module)>{{ $module }}</option>
                        @endforeach
                    </select>
                    @error('module') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">Status</label>
                    <select class="admin-select @error('status') border-rose-300 @enderror" name="status">
                        <option value="Draft" @selected(old('status', $item['status']) === 'Draft')>Draft</option>
                        <option value="Published" @selected(old('status', $item['status']) === 'Published')>Published</option>
                    </select>
                    @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="admin-label">Title</label>
                    <input class="admin-input @error('title') border-rose-300 @enderror" name="title" value="{{ old('title', $item['title']) }}" />
                    @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">Subtitle</label>
                    <input class="admin-input @error('subtitle') border-rose-300 @enderror" name="subtitle" value="{{ old('subtitle', $item['subtitle'] ?? '') }}" />
                    @error('subtitle') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="admin-label">Slug</label>
                    <input class="admin-input @error('slug') border-rose-300 @enderror" name="slug" value="{{ old('slug', $item['slug']) }}" placeholder="/example-page" />
                    @error('slug') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="admin-label">Content</label>
                    <textarea class="admin-input @error('content') border-rose-300 @enderror" name="content" rows="8">{{ old('content', $item['content']) }}</textarea>
                    @error('content') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">SEO Title</label>
                    <input class="admin-input @error('seo_title') border-rose-300 @enderror" name="seo_title" value="{{ old('seo_title', $item['seo_title'] ?? '') }}" />
                    @error('seo_title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">SEO Description</label>
                    <input class="admin-input @error('seo_description') border-rose-300 @enderror" name="seo_description" value="{{ old('seo_description', $item['seo_description'] ?? '') }}" />
                    @error('seo_description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="admin-label">Image URL</label>
                    <input class="admin-input @error('image') border-rose-300 @enderror" name="image" value="{{ old('image', $item['image'] ?? '') }}" placeholder="https://..." />
                    @error('image') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-admin-panel-border pt-5">
                <a href="{{ route('admin.website-content') }}" class="admin-btn-soft">Back</a>
                @if($isEdit)
                    <a href="{{ route('admin.website-content.delete-confirm', $item['id']) }}" class="admin-btn-soft text-rose-700">Delete</a>
                @endif
                <button type="submit" class="admin-btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Content' }}</button>
            </div>
        </form>
    </article>
</section>
@endsection
