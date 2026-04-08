@php
    $v = fn (string $key): string => trim((string) ($values[$key] ?? ''));
    $fmtMoney = function (string $raw): string {
        if ($raw === '') {
            return '—';
        }
        if (is_numeric($raw)) {
            return 'Ksh ' . number_format((float) $raw, 2);
        }

        return $raw;
    };
@endphp
<div class="colldett-demand-letter">
    <div class="colldett-demand-letter__meta">
        <div class="colldett-demand-letter__meta-row">
            <span class="colldett-demand-letter__meta-label">Engaging client</span>
            <span class="colldett-demand-letter__meta-value">{{ $v('client') !== '' ? $v('client') : '—' }}</span>
        </div>
        <div class="colldett-demand-letter__meta-row colldett-demand-letter__meta-row--grid">
            <div>
                <span class="colldett-demand-letter__meta-label">Case reference</span>
                <span class="colldett-demand-letter__meta-value">{{ $v('case_ref') !== '' ? $v('case_ref') : '—' }}</span>
            </div>
            <div>
                <span class="colldett-demand-letter__meta-label">Amount</span>
                <span class="colldett-demand-letter__meta-value">{{ $v('amount') !== '' ? $fmtMoney($v('amount')) : '—' }}</span>
            </div>
            <div>
                <span class="colldett-demand-letter__meta-label">Deadline</span>
                <span class="colldett-demand-letter__meta-value">{{ $v('deadline') !== '' ? $v('deadline') : '—' }}</span>
            </div>
        </div>
    </div>

    <div class="colldett-demand-letter__subject-block">
        <p class="colldett-demand-letter__subject-label">Subject</p>
        <p class="colldett-demand-letter__subject-text">{{ $v('subject') !== '' ? $v('subject') : '—' }}</p>
    </div>

    <div class="colldett-demand-letter__body">
        {!! nl2br(e($v('body'))) !!}
    </div>
</div>
