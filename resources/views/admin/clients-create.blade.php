@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Add Client</h2>
            <p class="text-sm text-admin-muted">Create a full client profile for cases, billing, and correspondence.</p>
        </div>
        <a href="{{ route('admin.clients') }}" class="admin-btn-soft">Back to Clients</a>
    </div>

    <form method="POST" action="{{ route('admin.clients.store') }}" class="space-y-6">
        @csrf

        @include('admin.partials.client-form-fields', ['client' => $client])

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-admin-muted">
            An account number (e.g. AC-000240) is assigned automatically when you save.
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="admin-btn-primary">Save client</button>
            <a href="{{ route('admin.clients') }}" class="admin-btn-soft">Cancel</a>
        </div>
    </form>
</section>
@endsection
