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
                <h2 class="text-2xl font-bold">{{ $mode === 'create' ? 'Create' : 'Edit' }} Service</h2>
                <p class="text-sm text-admin-muted">Manage service metadata for public pages with stronger presentation and SEO control.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="admin-chip">SERVICE PROFILE</span>
                <a href="{{ route('admin.services.index') }}" class="admin-btn-soft">Back to Services</a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $mode === 'create' ? route('admin.services.store') : route('admin.services.update', $item['id']) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($mode === 'edit')
                @method('PATCH')
            @endif

            <div class="grid gap-6 xl:grid-cols-12">
                <article class="admin-card p-6 xl:col-span-8">
                    <div class="mb-4">
                        <h3 class="admin-card-title text-base">Core Service Details</h3>
                        <p class="mt-1 text-xs text-admin-muted">These details are shown publicly and used for service routing.</p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Service Name</label>
                            <input class="admin-input" name="name" value="{{ old('name', $item['name']) }}" placeholder="e.g. Debt Recovery" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Slug</label>
                            <input class="admin-input" name="slug" value="{{ old('slug', $item['slug']) }}" placeholder="e.g. debt-recovery" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Description</label>
                            <textarea class="admin-input min-h-40" name="description" placeholder="Describe service scope, value and delivery model...">{{ old('description', $item['description']) }}</textarea>
                        </div>
                    </div>
                </article>

                <aside class="admin-card p-5 xl:col-span-4 space-y-4">
                    <h3 class="admin-card-title">Preview & Guidance</h3>
                    <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                        <p class="text-xs uppercase tracking-wide text-admin-muted">Current Slug</p>
                        <p class="mt-1 text-sm font-medium text-admin-ink">/{{ old('slug', $item['slug']) ?: 'service-slug' }}</p>
                    </div>
                    <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-admin-muted">Featured Image</p>
                        @php
                            $image = old('image', $item['image'] ?? '');
                            $imageUrl = $image ? (str_starts_with($image, 'http') ? $image : asset($image)) : null;
                        @endphp
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="Service image preview" class="h-36 w-full rounded border border-admin-border object-cover bg-white">
                        @else
                            <div class="grid h-24 w-full place-items-center rounded border border-dashed border-admin-border bg-white text-xs text-admin-muted">No image uploaded</div>
                        @endif
                        <input class="admin-input mt-3" type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp,.svg,image/*" />
                    </div>
                    <ul class="space-y-2 text-sm text-admin-muted">
                        <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Use concise names that align with website navigation.</li>
                        <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Keep description clear, benefit-focused, and client-friendly.</li>
                        <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">Avoid changing slug frequently to prevent broken links.</li>
                    </ul>
                </aside>
            </div>

            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="sticky bottom-3 z-10 flex justify-end gap-2">
                <div class="flex gap-2 rounded-xl border border-admin-border bg-white/95 p-2 shadow-lg backdrop-blur">
                    <a href="{{ route('admin.services.index') }}" class="admin-btn-soft">Cancel</a>
                    <button type="submit" class="admin-btn-primary">{{ $mode === 'create' ? 'Create' : 'Update' }} Service</button>
                </div>
            </div>
    </form>
</section>
@endsection
