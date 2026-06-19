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
            <h2 class="text-2xl font-bold">Application Details</h2>
            <p class="text-sm text-admin-muted">{{ $application->name }} — {{ $application->career?->title }}</p>
        </div>
        <a href="{{ route('admin.career-applications.index', ['career_id' => $application->career_id]) }}" class="admin-btn-soft">Back to Applications</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <article class="admin-card space-y-4 p-6 lg:col-span-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Applicant</p>
                <p class="mt-1 text-lg font-semibold">{{ $application->name }}</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Email</p>
                    <p class="mt-1"><a href="mailto:{{ $application->email }}" class="text-admin-ink underline">{{ $application->email }}</a></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Phone</p>
                    <p class="mt-1">{{ $application->phone ?: '—' }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Position</p>
                <p class="mt-1">{{ $application->career?->title ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Submitted</p>
                <p class="mt-1">{{ $application->created_at?->format('j M Y, H:i') }}</p>
            </div>
            @if($application->cover_letter)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Cover letter</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-admin-ink">{{ $application->cover_letter }}</p>
                </div>
            @endif
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-admin-muted">Documents</p>
                @forelse($application->documentEntries() as $index => $document)
                    <a href="{{ route('admin.career-applications.document', [$application->id, $index]) }}" class="admin-btn-soft inline-flex">
                        Download {{ $document['original_name'] }}
                    </a>
                @empty
                    <p class="text-sm text-admin-muted">No documents uploaded.</p>
                @endforelse
            </div>
        </article>

        <article class="admin-card p-6">
            <h3 class="text-base font-semibold">Update status</h3>
            <form method="POST" action="{{ route('admin.career-applications.update', $application->id) }}" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <select class="admin-select w-full" name="status">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($application->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn-soft w-full">Save status</button>
            </form>
        </article>
    </div>
</section>
@endsection
