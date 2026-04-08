@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Delete Client</h2>
            <p class="text-sm text-admin-muted">This removes the client from the directory.</p>
        </div>
        <a href="{{ route('admin.clients') }}" class="admin-btn-soft">Back to Clients</a>
    </div>

    <article class="admin-card max-w-2xl space-y-4 p-6">
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            You are about to delete <strong>{{ $client['name'] }}</strong> at <strong>{{ $client['company'] }}</strong>
            ({{ $client['account_number'] ?? '' }}). Billing and case dropdowns will no longer list this company unless it appears elsewhere.
        </div>
        <form method="POST" action="{{ route('admin.clients.destroy', $client['id']) }}" class="flex flex-wrap justify-end gap-2">
            @csrf
            @method('DELETE')
            <a href="{{ route('admin.clients.show', $client['id']) }}" class="admin-btn-soft">Cancel</a>
            <button type="submit" class="admin-btn-primary !bg-rose-600 hover:!bg-rose-700">Delete client</button>
        </form>
    </article>
</section>
@endsection
