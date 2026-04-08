@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">Delete Service</h2>
            <p class="text-sm text-admin-muted">Confirm removal of this service.</p>
        </div>
        <a href="{{ route('admin.services.index') }}" class="admin-btn-soft">Back to Services</a>
    </div>

    <article class="admin-card max-w-2xl p-6 space-y-4">
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            You are about to delete <strong>{{ $item['name'] }}</strong>.
        </div>
        <form method="POST" action="{{ route('admin.services.destroy', $item['id']) }}" class="flex justify-end gap-2">
            @csrf
            @method('DELETE')
            <a href="{{ route('admin.services.index') }}" class="admin-btn-soft">Cancel</a>
            <button type="submit" class="admin-btn-primary !bg-rose-600 hover:!bg-rose-700">Delete Service</button>
        </form>
    </article>
</section>
@endsection
