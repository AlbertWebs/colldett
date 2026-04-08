@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <article class="admin-card p-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Delete Confirmation</p>
        <h2 class="mt-2 text-2xl font-bold text-admin-text">Delete Website Content</h2>
        <p class="mt-3 text-sm text-admin-muted">
            You are about to delete <strong>{{ $item['title'] }}</strong> ({{ $item['slug'] }}). This action cannot be undone.
        </p>

        <dl class="mt-5 grid gap-3 rounded-xl border border-admin-panel-border bg-slate-50 p-4 text-sm text-admin-muted md:grid-cols-2">
            <div><dt class="font-semibold text-admin-text">Module</dt><dd>{{ $item['module'] }}</dd></div>
            <div><dt class="font-semibold text-admin-text">Status</dt><dd>{{ $item['status'] }}</dd></div>
            <div class="md:col-span-2"><dt class="font-semibold text-admin-text">Title</dt><dd>{{ $item['title'] }}</dd></div>
            <div class="md:col-span-2"><dt class="font-semibold text-admin-text">Slug</dt><dd>{{ $item['slug'] }}</dd></div>
        </dl>

        <form method="POST" action="{{ route('admin.website-content.destroy', $item['id']) }}" class="mt-6 flex items-center justify-end gap-3">
            @csrf
            @method('DELETE')
            <a href="{{ route('admin.website-content') }}" class="admin-btn-soft">Cancel</a>
            <button type="submit" class="admin-btn-soft text-rose-700">Delete Content</button>
        </form>
    </article>
</section>
@endsection
