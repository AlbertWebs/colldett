@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold">Change Password</h2>
        <p class="text-sm text-admin-muted">Update your password securely.</p>
    </div>

    <article class="admin-card p-6 space-y-4 max-w-2xl">
        <input class="admin-input" type="password" placeholder="Current Password" />
        <input class="admin-input" type="password" placeholder="New Password" />
        <input class="admin-input" type="password" placeholder="Confirm New Password" />
        <div class="flex justify-end">
            <button type="button" class="admin-btn-primary">Update Password</button>
        </div>
    </article>
</section>
@endsection
