@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Edit Client</h2>
            <p class="text-sm text-admin-muted">Update contact, company, location, and billing details.</p>
        </div>
        <a href="{{ route('admin.clients.show', $client['id']) }}" class="admin-btn-soft">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.clients.update', $client['id']) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        @include('admin.partials.client-form-fields', ['client' => $client])

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-admin-muted">
            Account number <strong class="text-admin-ink">{{ $client['account_number'] ?? '—' }}</strong> is fixed for this record.
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="admin-btn-primary">Save changes</button>
            <a href="{{ route('admin.clients.show', $client['id']) }}" class="admin-btn-soft">Cancel</a>
        </div>
    </form>
</section>
@endsection
