@extends('admin.layouts.app')

@section('content')
@push('styles')
    @vite(['resources/css/document-theme.css'])
@endpush
<section class="space-y-6">
    <div class="no-print space-y-6">
        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold">{{ $meta['singular'] }} Preview</h2>
                    <p class="text-sm text-admin-muted">Preview generated document details before sharing or exporting.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="admin-chip">{{ strtoupper($module) }}</span>
                    <a href="{{ route('admin.billing.module.edit', [$module, $recordId]) }}" class="admin-btn-soft">Edit</a>
                    <a href="{{ route('admin.billing.module.index', $module) }}" class="admin-btn-soft">View All</a>
                </div>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap justify-end gap-2">
            <a
                href="{{ route('admin.billing.module.preview.print', [$module, $recordId]) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="admin-btn-soft"
            >Print</a>
        </div>
    </div>

    @include('admin.partials.billing-preview-document', [
        'meta' => $meta,
        'module' => $module,
        'recordId' => $recordId,
        'values' => $values,
        'showPreviewFooterActions' => true,
    ])
</section>
@endsection
