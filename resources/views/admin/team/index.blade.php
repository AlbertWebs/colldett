@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Team Management</h2>
            <p class="text-sm text-admin-muted">Edit profiles for <span class="font-medium text-admin-ink">/team/{slug}</span> — same sections as the public team page (bio, focus areas, credentials, industries, principles).</p>
        </div>
        <a href="{{ route('admin.team.create') }}" class="admin-btn-primary">Add team member</a>
    </div>

    <article class="admin-card p-0">
        <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Member</th><th>Role</th><th>Department</th><th>Email</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($members as $member)
                    @php
                        $image = $member['image'] ?? null;
                        $imageUrl = $image ? (str_starts_with($image, 'http') ? $image : asset($image)) : null;
                        $active = (bool) ($member['is_active'] ?? true);
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $member['name'] }} photo" class="h-10 w-10 rounded-full object-cover border border-admin-border">
                                @else
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-admin-primary text-white text-xs font-semibold">
                                        {{ strtoupper(substr($member['name'] ?? 'TM', 0, 2)) }}
                                    </span>
                                @endif
                                <div>
                                    <div class="font-medium text-admin-ink">{{ $member['name'] }}</div>
                                    <div class="text-xs text-admin-muted">{{ $member['slug'] ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $member['role'] ?? '—' }}</td>
                        <td>{{ $member['department'] ?? '—' }}</td>
                        <td>{{ $member['email'] ?? '—' }}</td>
                        <td>{{ $member['location'] ?? '—' }}</td>
                        <td>
                            @if($active)
                                <span class="admin-status-chip admin-status-chip-active">Active</span>
                            @else
                                <span class="admin-status-chip bg-slate-100 text-slate-600 border-slate-200">Hidden</span>
                            @endif
                        </td>
                        <td>
                            <div class="admin-row-actions flex flex-wrap gap-1">
                                <a href="{{ route('team.show', $member['slug']) }}" class="admin-link-btn" target="_blank" rel="noopener noreferrer">View</a>
                                <a href="{{ route('admin.team.edit', $member['slug']) }}" class="admin-link-btn">Edit</a>
                                <a href="{{ route('admin.team.delete-confirm', $member['slug']) }}" class="admin-link-btn admin-link-btn-danger">Delete</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-sm text-admin-muted py-8">No team members. Add one or ensure config includes defaults.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </article>
</section>
@endsection
