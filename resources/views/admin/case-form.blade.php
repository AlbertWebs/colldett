@extends('admin.layouts.app')

@section('content')
@php
    $isEdit = $mode === 'edit';
@endphp
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">{{ $isEdit ? 'Update Case' : 'Create Case' }}</h2>
            <p class="text-sm text-admin-muted">Capture case details and assign action ownership.</p>
        </div>
        <a href="{{ route('admin.cases') }}" class="admin-btn-soft">Back to Cases</a>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.cases.update', $item['id']) : route('admin.cases.store') }}" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PATCH')
        @endif

        <article class="admin-card p-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-label">Client</label>
                    <select name="client" class="admin-select @error('client') border-rose-300 @enderror" data-add-client-url="{{ route('admin.clients.create') }}">
                        <option value="">Select client</option>
                        @foreach(($clients ?? []) as $client)
                            <option value="{{ $client }}" @selected(old('client', $item['client']) === $client)>{{ $client }}</option>
                        @endforeach
                        <option value="__add_client__">+ Add Client</option>
                    </select>
                    @error('client') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">Debtor</label>
                    <input name="debtor" value="{{ old('debtor', $item['debtor']) }}" class="admin-input @error('debtor') border-rose-300 @enderror" />
                    @error('debtor') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">Amount</label>
                    <input name="amount" value="{{ old('amount', $item['amount']) }}" class="admin-input @error('amount') border-rose-300 @enderror" placeholder="KES 0" />
                    @error('amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">Assigned Officer</label>
                    <select name="officer" class="admin-select @error('officer') border-rose-300 @enderror">
                        <option value="">Select officer</option>
                        @foreach(($officers ?? []) as $officer)
                            <option value="{{ $officer }}" @selected(old('officer', $item['officer']) === $officer)>{{ $officer }}</option>
                        @endforeach
                    </select>
                    @error('officer') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">Status</label>
                    <select name="status" class="admin-select @error('status') border-rose-300 @enderror">
                        @foreach(['Pending','In Progress','Closed'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $item['status']) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="admin-label">Next Action Date</label>
                    <input type="date" name="next_action_date" value="{{ old('next_action_date', $item['next_action_date']) }}" class="admin-input @error('next_action_date') border-rose-300 @enderror" />
                    @error('next_action_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </article>

        <div class="sticky bottom-3 z-10 flex justify-end gap-2">
            <div class="flex gap-2 rounded-xl border border-admin-border bg-white/95 p-2 shadow-lg backdrop-blur">
                <a href="{{ route('admin.cases') }}" class="admin-btn-soft">Cancel</a>
                @if($isEdit)
                    <a href="{{ route('admin.cases.show', $item['id']) }}" class="admin-btn-soft">View</a>
                @endif
                <button type="submit" class="admin-btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Case' }}</button>
            </div>
        </div>
    </form>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clientSelect = document.querySelector('select[name="client"]');
        if (!clientSelect) return;

        clientSelect.addEventListener('change', function () {
            if (this.value !== '__add_client__') return;
            const addClientUrl = this.getAttribute('data-add-client-url');
            if (addClientUrl) window.location.href = addClientUrl;
        });
    });
</script>
@endsection
