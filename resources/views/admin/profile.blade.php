@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold">Profile</h2>
        <p class="text-sm text-admin-muted">View your admin profile information.</p>
    </div>

    <article class="admin-card p-6 space-y-4 max-w-2xl">
        <div class="flex items-center gap-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-admin-primary text-white text-lg font-semibold">AD</span>
            <div>
                <p class="font-semibold text-admin-ink">Admin User</p>
                <p class="text-sm text-admin-muted">Administrator</p>
            </div>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-admin-muted">Name</p>
                <p class="text-sm text-admin-ink">Admin User</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-admin-muted">Email</p>
                <p class="text-sm text-admin-ink">admin@colldett.local</p>
            </div>
        </div>
    </article>
</section>
@endsection
