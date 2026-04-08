@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">Industries CRUD</h2>
            <p class="text-sm text-admin-muted">Create, edit and delete industry entries.</p>
        </div>
        <a href="{{ route('admin.industries.create') }}" class="admin-btn-primary">Create Industry</a>
    </div>

    <article class="admin-card p-0">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Industry</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if(!empty($item['image']))
                                        <img src="{{ str_starts_with($item['image'], 'http') ? $item['image'] : asset($item['image']) }}" alt="{{ $item['name'] }} image" class="h-10 w-10 rounded-lg object-cover border border-admin-border">
                                    @else
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-admin-primary text-white text-xs font-semibold">{{ strtoupper(substr($item['name'], 0, 2)) }}</span>
                                    @endif
                                    <span>{{ $item['name'] }}</span>
                                </div>
                            </td>
                            <td>{{ $item['description'] }}</td>
                            <td>
                                <div class="admin-row-actions">
                                    <a href="{{ route('admin.industries.edit', $item['id']) }}" class="admin-link-btn">Edit</a>
                                    <a href="{{ route('admin.industries.delete-confirm', $item['id']) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
