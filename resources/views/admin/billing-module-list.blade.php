@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">{{ $meta['title'] }}</h2>
            <p class="text-sm text-admin-muted">View all {{ strtolower($meta['title']) }} records.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.billing') }}" class="admin-btn-soft">Back to Management</a>
            <a href="{{ route('admin.billing.module.create', $module) }}" class="admin-btn-primary">Create {{ $meta['singular'] }}</a>
        </div>
    </div>

    <article class="admin-card p-0">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        @foreach($meta['fields'] as $field)
                            <th>{{ $field['label'] }}</th>
                        @endforeach
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $index => $row)
                        <tr>
                            @foreach($meta['fields'] as $field)
                                <td>{{ $row[$field['name']] ?? '—' }}</td>
                            @endforeach
                            <td>
                                <div class="admin-row-actions">
                                    <a class="admin-link-btn" href="{{ route('admin.billing.module.edit', [$module, $index + 1]) }}">Edit</a>
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
