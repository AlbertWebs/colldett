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
                <h2 class="text-2xl font-bold">{{ $mode === 'create' ? 'Add' : 'Edit' }} team member</h2>
                <p class="text-sm text-admin-muted">Fields map to the public profile: hero, bio, key focus areas, credentials, industries, principles.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($mode === 'edit')
                    <a href="{{ route('team.show', $member['slug']) }}" class="admin-btn-soft" target="_blank" rel="noopener noreferrer">Open public profile</a>
                @endif
                <a href="{{ route('admin.team') }}" class="admin-btn-soft">Back to team</a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $mode === 'create' ? route('admin.team.store') : route('admin.team.update', $member['slug']) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($mode === 'edit')
            @method('PATCH')
        @endif

        <div class="grid gap-6 xl:grid-cols-12">
            <article class="admin-card p-6 xl:col-span-8 space-y-5">
                <div>
                    <h3 class="admin-card-title text-base">Identity &amp; role</h3>
                    <p class="mt-1 text-xs text-admin-muted">Shown in the page hero and profile column.</p>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    @if($mode === 'create')
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">URL slug</label>
                            <input class="admin-input" name="slug" value="{{ old('slug', $member['slug']) }}" placeholder="e.g. evance-odhiambo" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" />
                            <p class="mt-1 text-xs text-admin-muted">Lowercase, hyphens only. Used in /team/<strong>slug</strong>.</p>
                        </div>
                    @else
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">URL slug</label>
                            <input class="admin-input bg-slate-50" type="text" value="{{ $member['slug'] }}" readonly disabled />
                            <p class="mt-1 text-xs text-admin-muted">Slug cannot be changed here (stable public URL).</p>
                        </div>
                    @endif
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Full name</label>
                        <input class="admin-input" name="name" value="{{ old('name', $member['name']) }}" required />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Role / title</label>
                        <input class="admin-input" name="role" value="{{ old('role', $member['role']) }}" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Department</label>
                        <input class="admin-input" name="department" value="{{ old('department', $member['department']) }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Years experience</label>
                        <input class="admin-input" type="number" name="experience_years" min="0" max="80" value="{{ old('experience_years', $member['experience_years']) }}" placeholder="Optional" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Location</label>
                        <input class="admin-input" name="location" value="{{ old('location', $member['location']) }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Email</label>
                        <input class="admin-input" type="email" name="email" value="{{ old('email', $member['email']) }}" />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Bio</label>
                    <textarea class="admin-input min-h-32" name="bio" required>{{ old('bio', $member['bio']) }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">SEO description</label>
                    <textarea class="admin-input min-h-20" name="seo_description" placeholder="Short summary for search &amp; meta">{{ old('seo_description', $member['seo_description']) }}</textarea>
                </div>

                <div class="border-t border-admin-border pt-5 space-y-4">
                    <h4 class="text-sm font-semibold text-admin-ink">Lists (one item per line)</h4>
                    <p class="text-xs text-admin-muted">Matches “Key Focus Areas”, “Professional Credentials”, “Industries Served”, and “Professional Principles” on the live page.</p>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Key focus areas (specialties)</label>
                        <textarea class="admin-input min-h-24 font-mono text-sm" name="specialties_text" placeholder="One specialty per line">{{ old('specialties_text', implode("\n", $member['specialties'] ?? [])) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Professional credentials</label>
                        <textarea class="admin-input min-h-24 font-mono text-sm" name="credentials_text">{{ old('credentials_text', implode("\n", $member['credentials'] ?? [])) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Industries served</label>
                        <textarea class="admin-input min-h-24 font-mono text-sm" name="industries_text">{{ old('industries_text', implode("\n", $member['industries'] ?? [])) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Professional principles</label>
                        <textarea class="admin-input min-h-24 font-mono text-sm" name="principles_text">{{ old('principles_text', implode("\n", $member['principles'] ?? [])) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-lg border border-admin-border bg-slate-50 px-4 py-3">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="h-4 w-4 rounded border-admin-border" @checked(old('is_active', ($member['is_active'] ?? true) ? '1' : '0') === '1') />
                    <label for="is_active" class="text-sm text-admin-ink">Show on website (About page &amp; profile URL)</label>
                </div>
            </article>

            <aside class="admin-card p-5 xl:col-span-4 space-y-4">
                <h3 class="admin-card-title">Photo</h3>
                @php
                    $img = old('image', $member['image'] ?? '');
                    $imgUrl = $img ? (str_starts_with($img, 'http') ? $img : asset($img)) : null;
                @endphp
                @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="Portrait preview" class="h-44 w-full rounded-lg border border-admin-border object-cover bg-white" />
                @else
                    <div class="grid h-32 w-full place-items-center rounded-lg border border-dashed border-admin-border bg-slate-50 text-xs text-admin-muted">No image</div>
                @endif
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Image URL (optional)</label>
                    <input class="admin-input text-sm" name="image" value="{{ $img }}" placeholder="https://… or uploads/…" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-admin-muted">Or upload file</label>
                    <input class="admin-input" type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp,image/*" />
                    <p class="mt-1 text-xs text-admin-muted">Upload replaces URL. Stored under public/uploads/team/.</p>
                </div>
            </aside>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-wrap justify-end gap-2">
            <a href="{{ route('admin.team') }}" class="admin-btn-soft">Cancel</a>
            <button type="submit" class="admin-btn-primary">{{ $mode === 'create' ? 'Create member' : 'Save changes' }}</button>
        </div>
    </form>
</section>
@endsection
