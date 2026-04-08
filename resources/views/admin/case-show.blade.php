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
            <h2 class="text-2xl font-bold">Case {{ $item['case_number'] }}</h2>
            <p class="text-sm text-admin-muted">Review case details, log notes and perform actions.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.cases') }}" class="admin-btn-soft">Back</a>
            <a href="{{ route('admin.cases.edit', $item['id']) }}" class="admin-btn-primary">Edit Case</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <article class="admin-card p-6 xl:col-span-2">
            <div class="grid gap-4 sm:grid-cols-2">
                <div><p class="text-xs uppercase tracking-wide text-admin-muted">Client</p><p class="mt-1 text-sm font-medium text-admin-ink">{{ $item['client'] }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-admin-muted">Debtor</p><p class="mt-1 text-sm font-medium text-admin-ink">{{ $item['debtor'] }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-admin-muted">Amount</p><p class="mt-1 text-sm font-medium text-admin-ink">{{ $item['amount'] }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-admin-muted">Assigned Officer</p><p class="mt-1 text-sm font-medium text-admin-ink">{{ $item['officer'] }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-admin-muted">Status</p><p class="mt-1 text-sm font-medium text-admin-ink">{{ $item['status'] }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-admin-muted">Next Action</p><p class="mt-1 text-sm font-medium text-admin-ink">{{ $item['next_action_date'] }}</p></div>
            </div>

            <div class="mt-6 border-t border-admin-border pt-4">
                <p class="mb-2 text-xs uppercase tracking-wide text-admin-muted">Case Notes</p>
                <ul class="space-y-2 text-sm text-admin-muted">
                    @forelse(($item['notes'] ?? []) as $note)
                        <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">
                            {{ $note['body'] }} <span class="text-xs">({{ $note['created_at'] }})</span>
                        </li>
                    @empty
                        <li class="rounded-lg border border-admin-border bg-slate-50 px-3 py-2">No notes added yet.</li>
                    @endforelse
                </ul>
            </div>
        </article>

        <aside class="admin-card space-y-3 p-5">
            <h3 class="admin-card-title">Actions</h3>
            <form method="POST" action="{{ route('admin.cases.add-note', $item['id']) }}" class="space-y-2">
                @csrf
                <textarea name="note" class="admin-input" rows="4" placeholder="Add case progress note..."></textarea>
                <button type="submit" class="admin-btn-soft w-full justify-center">Add Note</button>
            </form>
            @if($item['status'] !== 'Closed')
                <form method="POST" action="{{ route('admin.cases.close', $item['id']) }}">
                    @csrf
                    <button type="submit" class="admin-btn-soft w-full justify-center !border-rose-200 !text-rose-700 hover:!bg-rose-50">Close Case</button>
                </form>
            @endif
            <a href="{{ route('admin.cases.delete-confirm', $item['id']) }}" class="admin-btn-soft w-full justify-center !border-rose-200 !text-rose-700 hover:!bg-rose-50">Delete Case</a>
        </aside>
    </div>
</section>
@endsection
