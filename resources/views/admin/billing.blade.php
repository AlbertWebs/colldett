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
                <h2 class="text-2xl font-bold">Billing & Documents</h2>
                <p class="text-sm text-admin-muted">Create and edit all billing and legal document records from dedicated pages.</p>
            </div>
            <a class="admin-btn-soft" href="{{ route('admin.reports') }}">Open Reports</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($modules as $key => $module)
            <article class="admin-card p-5 space-y-3">
                <h3 class="admin-card-title">{{ $module['title'] }}</h3>
                <p class="text-sm text-admin-muted">{{ $module['description'] }}</p>
                <div class="flex flex-wrap gap-2">
                    <a class="admin-btn-primary" href="{{ route('admin.billing.module.create', $key) }}">Create {{ $module['singular'] }}</a>
                    <a class="admin-btn-soft" href="{{ route('admin.billing.module.edit', [$key, 1]) }}">Edit Sample</a>
                    <a class="admin-btn-soft" href="{{ route('admin.billing.module.index', $key) }}">View All</a>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
