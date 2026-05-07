@extends('admin.layouts.app')

@section('content')
@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $src = $isEdit
        ? (str_starts_with($item->image_path, 'http') ? $item->image_path : asset(ltrim($item->image_path, '/')))
        : '';
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
                <h2 class="text-2xl font-bold">{{ $isEdit ? 'Edit' : 'Add' }} Gallery Item</h2>
                <p class="text-sm text-admin-muted">Drag and drop an image, then add a caption. You can hide items without deleting them.</p>
            </div>
            <a class="admin-btn-soft" href="{{ route('admin.gallery.index') }}">Back to Gallery</a>
        </div>
    </div>

    <form
        method="POST"
        action="{{ $isEdit ? route('admin.gallery.update', $item) : route('admin.gallery.store') }}"
        enctype="multipart/form-data"
        class="space-y-6"
        data-gallery-form
    >
        @csrf
        @if($isEdit)
            @method('PATCH')
        @endif

        <div class="grid gap-6 xl:grid-cols-12">
            <article class="admin-card p-6 xl:col-span-8 space-y-4">
                <div>
                    <h3 class="admin-card-title text-base">Image</h3>
                    <p class="mt-1 text-xs text-admin-muted">Upload a JPG/PNG/WebP. Recommended: 1600px wide or higher.</p>
                </div>

                <div class="admin-gallery-dropzone" data-dropzone>
                    <input class="sr-only" type="file" name="image_file" accept="image/*" {{ $isEdit ? '' : 'required' }} data-dropzone-input>
                    <div class="admin-gallery-dropzone__inner">
                        <div class="admin-gallery-dropzone__icon" aria-hidden="true">⬆</div>
                        <div>
                            <p class="text-sm font-semibold text-admin-ink">Drop image here</p>
                            <p class="mt-1 text-xs text-admin-muted">or click to choose a file</p>
                        </div>
                    </div>
                    <div class="admin-gallery-dropzone__preview" data-dropzone-preview @if($src==='') style="display:none" @endif>
                        <img src="{{ $src }}" alt="" data-dropzone-img>
                        <button type="button" class="admin-link-btn" data-dropzone-clear>Remove</button>
                    </div>
                </div>

                @error('image_file')
                    <p class="text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </article>

            <aside class="admin-card p-6 xl:col-span-4 space-y-4">
                <div>
                    <h3 class="admin-card-title text-base">Details</h3>
                    <p class="mt-1 text-xs text-admin-muted">Caption shows beneath the image on the public gallery page.</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Caption</label>
                    <input class="admin-input" name="caption" maxlength="255" value="{{ old('caption', $item->caption) }}" placeholder="e.g. Field verification engagement" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Sort order</label>
                        <input class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', (string) $item->sort_order) }}" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Visibility</label>
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center gap-2 rounded-lg border border-admin-border bg-slate-50 px-3 py-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active) ) />
                            <span class="text-admin-ink font-medium">Show publicly</span>
                        </label>
                    </div>
                </div>

                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif
            </aside>
        </div>

        <div class="sticky bottom-3 z-10 flex justify-end gap-2">
            <div class="flex gap-2 rounded-xl border border-admin-border bg-white/95 p-2 shadow-lg backdrop-blur">
                <a href="{{ route('admin.gallery.index') }}" class="admin-btn-soft">Cancel</a>
                <button type="submit" class="admin-btn-primary">{{ $isEdit ? 'Save changes' : 'Add to gallery' }}</button>
            </div>
        </div>
    </form>
</section>

<script>
    (function () {
        const form = document.querySelector('[data-gallery-form]');
        if (!form) return;

        const zone = form.querySelector('[data-dropzone]');
        const input = form.querySelector('[data-dropzone-input]');
        const preview = form.querySelector('[data-dropzone-preview]');
        const img = form.querySelector('[data-dropzone-img]');
        const clearBtn = form.querySelector('[data-dropzone-clear]');

        const showPreview = (file) => {
            const url = URL.createObjectURL(file);
            img.src = url;
            preview.style.display = 'flex';
            zone.classList.add('has-preview');
        };

        const clear = () => {
            input.value = '';
            if (img) img.src = '';
            preview.style.display = 'none';
            zone.classList.remove('has-preview');
        };

        zone.addEventListener('click', (e) => {
            if (e.target && e.target.closest && e.target.closest('[data-dropzone-clear]')) return;
            input.click();
        });

        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            showPreview(file);
        });

        ['dragenter', 'dragover'].forEach((evt) => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach((evt) => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('is-dragover');
            });
        });
        zone.addEventListener('drop', (e) => {
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            showPreview(file);
        });

        clearBtn.addEventListener('click', () => clear());
    })();
</script>
@endsection

