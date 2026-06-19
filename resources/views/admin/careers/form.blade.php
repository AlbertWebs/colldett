@extends('admin.layouts.app')

@section('content')
@php
    $isEdit = $mode === 'edit';
    $itemId = $isEdit ? $item->id : null;
@endphp
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">{{ $isEdit ? 'Edit' : 'Create' }} Position</h2>
            <p class="text-sm text-admin-muted">Manage job listing content shown on the public careers page.</p>
        </div>
        <a href="{{ route('admin.careers.index') }}" class="admin-btn-soft">Back to Careers</a>
    </div>

    <article class="admin-card p-6 max-w-4xl">
        <form method="POST" action="{{ $isEdit ? route('admin.careers.update', $itemId) : route('admin.careers.store') }}" class="space-y-4">
            @csrf
            @if($isEdit)
                @method('PATCH')
            @endif

            <div>
                <label for="career-title" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Title</label>
                <input id="career-title" class="admin-input" name="title" value="{{ old('title', $isEdit ? $item->title : $item['title']) }}" placeholder="e.g. Debt Recovery Officer" required />
                @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="career-slug" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Slug</label>
                <input id="career-slug" class="admin-input" name="slug" value="{{ old('slug', $isEdit ? $item->slug : $item['slug']) }}" placeholder="Auto-filled from title" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" />
                <p class="mt-1 text-xs text-admin-muted">
                    @if($isEdit)
                        Public URL: /careers/<span id="career-slug-preview">{{ old('slug', $item->slug) }}</span>
                    @else
                        Generated from <strong>Title</strong> as you type. Edit manually if needed.
                    @endif
                </p>
                @error('slug') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="career-location" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Location</label>
                    <input id="career-location" class="admin-input" name="location" value="{{ old('location', $isEdit ? $item->location : $item['location']) }}" placeholder="Nairobi, Kenya" />
                </div>
                <div>
                    <label for="career-department" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Department</label>
                    <input id="career-department" class="admin-input" name="department" value="{{ old('department', $isEdit ? $item->department : $item['department']) }}" placeholder="Operations" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="career-type" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Employment type</label>
                    <input id="career-type" class="admin-input" name="employment_type" value="{{ old('employment_type', $isEdit ? $item->employment_type : $item['employment_type']) }}" placeholder="Full-time" />
                </div>
                <div>
                    <label for="career-closes" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Closes on</label>
                    <input id="career-closes" class="admin-input" type="date" name="closes_at" value="{{ old('closes_at', $isEdit && $item->closes_at ? $item->closes_at->format('Y-m-d') : $item['closes_at']) }}" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="career-sort" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Sort order</label>
                    <input id="career-sort" class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $isEdit ? $item->sort_order : $item['sort_order']) }}" />
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-admin-ink">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $isEdit ? $item->is_active : $item['is_active'])) />
                        Active (visible on public site)
                    </label>
                </div>
            </div>

            <div>
                <label for="career-excerpt" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Excerpt</label>
                <textarea id="career-excerpt" class="admin-input min-h-24" name="excerpt" placeholder="Short summary for the careers listing card">{{ old('excerpt', $isEdit ? $item->excerpt : $item['excerpt']) }}</textarea>
            </div>

            <div>
                <label for="career-description" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Description</label>
                <textarea id="career-description" class="admin-input min-h-48" name="description" placeholder="Full role description, responsibilities, and requirements">{{ old('description', $isEdit ? $item->description : $item['description']) }}</textarea>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.careers.index') }}" class="admin-btn-soft">Cancel</a>
                <button type="submit" class="admin-btn-primary">{{ $isEdit ? 'Update' : 'Create' }} Position</button>
            </div>
        </form>
    </article>
</section>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const titleInput = document.getElementById('career-title');
            const slugInput = document.getElementById('career-slug');
            const slugPreview = document.getElementById('career-slug-preview');
            const isEdit = @json($isEdit);

            const toSlug = function (value) {
                return (value || '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            };

            if (!titleInput || !slugInput) {
                return;
            }

            if (isEdit) {
                slugInput.dataset.touched = '1';
            }

            titleInput.addEventListener('input', function () {
                const next = toSlug(titleInput.value);
                if (!slugInput.dataset.touched) {
                    slugInput.value = next;
                }
                if (slugPreview) {
                    slugPreview.textContent = next || slugPreview.textContent;
                }
            });

            slugInput.addEventListener('input', function () {
                slugInput.dataset.touched = slugInput.value.trim() !== '' ? '1' : '';
                if (slugPreview) {
                    slugPreview.textContent = slugInput.value.trim() || slugPreview.textContent;
                }
            });
        });
    </script>
@endpush
@endsection
