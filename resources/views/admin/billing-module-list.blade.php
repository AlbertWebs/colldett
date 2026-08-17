@extends('admin.layouts.app')

@section('content')
@php
    use App\Support\DocumentPlainText;
    $cols = $meta['list_fields'] ?? $meta['fields'];
    $plainCell = static function (mixed $value): string {
        $text = DocumentPlainText::fromHtml(is_string($value) ? $value : (string) ($value ?? ''));

        return $text !== '' ? $text : '—';
    };
@endphp
<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">{{ $meta['title'] }}</h2>
            <p class="text-sm text-admin-muted">
                @if($module === 'fee-notes')
                    Issued fee notes show their FN number; drafts stay editable until you issue an official number.
                @elseif($module === 'credit-notes')
                    Credit notes are issued against an existing fee note and reduce the amount payable on that fee note.
                @else
                    View all {{ strtolower($meta['title']) }} records.
                @endif
            </p>
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
                        @foreach($cols as $field)
                            <th>{{ $field['label'] }}</th>
                        @endforeach
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($module === 'fee-notes' && ($rows ?? []) === [])
                        <tr>
                            <td colspan="{{ count($cols) + 1 }}" class="py-10 text-center text-sm text-admin-muted">
                                No fee notes yet. Use <strong>Create Fee Note</strong> to add your first record (save a draft or issue immediately).
                            </td>
                        </tr>
                    @elseif($module === 'credit-notes' && ($rows ?? []) === [])
                        <tr>
                            <td colspan="{{ count($cols) + 1 }}" class="py-10 text-center text-sm text-admin-muted">
                                No credit notes yet. Issue one against an existing fee note.
                            </td>
                        </tr>
                    @else
                    @foreach($rows as $index => $row)
                        @php
                            $recordId = (int) ($row['id'] ?? ($index + 1));
                        @endphp
                        <tr>
                            @foreach($cols as $field)
                                @php
                                    $rawCell = ($field['name'] ?? '') !== '__fee_note_status'
                                        ? $plainCell($row[$field['name']] ?? '')
                                        : '';
                                @endphp
                                <td class="align-top text-sm @if(! empty($field['truncate'])) max-w-[11rem] truncate @endif" title="{{ $rawCell }}">
                                    @if(($field['name'] ?? '') === '__fee_note_status')
                                        @if(! empty($row['is_draft']))
                                            <span class="admin-status-chip admin-status-chip-pending">Draft</span>
                                        @else
                                            <span class="admin-status-chip admin-status-chip-active">Issued</span>
                                        @endif
                                    @elseif(($field['name'] ?? '') === 'apply_vat')
                                        {{ \App\Support\DocumentVat::applies($row) ? 'With VAT' : 'Without VAT' }}
                                    @elseif(($field['name'] ?? '') === 'number')
                                        @if(! empty($row['is_draft']) && trim((string) ($row['number'] ?? '')) === '')
                                            <span class="text-admin-muted">—</span>
                                        @else
                                            {{ $plainCell($row['number'] ?? '') }}
                                        @endif
                                    @else
                                        {{ $plainCell($row[$field['name']] ?? '') }}
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <div class="admin-row-actions">
                                    @if(in_array($module, ['fee-notes', 'invoices', 'credit-notes'], true))
                                        <a class="admin-link-btn" href="{{ route('admin.billing.module.preview', [$module, $recordId]) }}">View</a>
                                    @endif
                                    <a class="admin-link-btn" href="{{ route('admin.billing.module.edit', [$module, $recordId]) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
