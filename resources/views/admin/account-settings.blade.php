@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold">Account Settings</h2>
        <p class="text-sm text-admin-muted">Manage personal preferences for your admin account.</p>
    </div>

    <article class="admin-card p-6 space-y-4 max-w-2xl">
        <div class="grid gap-3 sm:grid-cols-2">
            <input class="admin-input" value="Admin User" placeholder="Display Name" />
            <input class="admin-input" value="admin@colldett.local" placeholder="Email Address" />
        </div>
        <div class="flex justify-end">
            <button type="button" class="admin-btn-primary">Save Account Settings</button>
        </div>
    </article>
</section>
@endsection
