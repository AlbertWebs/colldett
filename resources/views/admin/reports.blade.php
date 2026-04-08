@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="admin-card bg-gradient-to-r from-slate-50 to-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Reports</h2>
                <p class="text-sm text-admin-muted">Revenue, payments, outstanding balances, collections performance and client analytics.</p>
            </div>
            <div class="flex gap-2">
                <a class="admin-btn-soft" href="{{ route('admin.reports.export', ['type' => $filters['type'] ?? 'all']) }}">Export CSV</a>
                <button class="admin-btn-soft" type="button">Export PDF</button>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports') }}" class="admin-card p-4">
        <div class="grid gap-3 md:grid-cols-4">
            <input class="admin-input" type="date" name="from" value="{{ $filters['from'] ?? '' }}" />
            <input class="admin-input" type="date" name="to" value="{{ $filters['to'] ?? '' }}" />
            <select class="admin-select" name="type">
                <option value="all" @selected(($filters['type'] ?? 'all') === 'all')>All Reports</option>
                <option value="collections" @selected(($filters['type'] ?? '') === 'collections')>Collections</option>
                <option value="billing" @selected(($filters['type'] ?? '') === 'billing')>Billing</option>
                <option value="cases" @selected(($filters['type'] ?? '') === 'cases')>Cases</option>
                <option value="team" @selected(($filters['type'] ?? '') === 'team')>Team Productivity</option>
            </select>
            <button class="admin-btn-primary" type="submit">Run Report</button>
        </div>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($kpis as $kpi)
            <article class="admin-stat">
                <p class="text-xs uppercase tracking-wide text-admin-muted">{{ $kpi['label'] }}</p>
                <p class="mt-2 text-xl font-semibold">{{ $kpi['value'] }}</p>
            </article>
        @endforeach
    </div>

    @if(($filters['type'] ?? 'all') === 'all' || ($filters['type'] ?? 'all') === 'collections')
        <article class="admin-card p-0">
            <div class="flex items-center justify-between p-4">
                <h3 class="admin-card-title">Collections Performance</h3>
                <a class="admin-link-btn" href="{{ route('admin.reports.export', ['type' => 'collections']) }}">Export</a>
            </div>
            <div class="admin-table-wrap !rounded-none !border-x-0 !border-b-0">
                <table class="admin-table">
                    <thead><tr><th>Client</th><th>Invoiced</th><th>Collected</th><th>Outstanding</th><th>Collection Rate</th></tr></thead>
                    <tbody>
                        @foreach($collections as $row)
                            <tr>
                                <td>{{ $row['client'] }}</td>
                                <td>KES {{ number_format($row['invoiced']) }}</td>
                                <td>KES {{ number_format($row['collected']) }}</td>
                                <td>KES {{ number_format($row['outstanding']) }}</td>
                                <td><span class="admin-status-chip {{ $row['rate'] >= 80 ? 'admin-status-chip-active' : 'admin-status-chip-pending' }}">{{ $row['rate'] }}%</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    @endif

    @if(($filters['type'] ?? 'all') === 'all' || ($filters['type'] ?? 'all') === 'billing')
        <article class="admin-card p-0">
            <div class="flex items-center justify-between p-4">
                <h3 class="admin-card-title">Billing Summary</h3>
                <a class="admin-link-btn" href="{{ route('admin.reports.export', ['type' => 'billing']) }}">Export</a>
            </div>
            <div class="admin-table-wrap !rounded-none !border-x-0 !border-b-0">
                <table class="admin-table">
                    <thead><tr><th>Invoice</th><th>Client</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($billing as $row)
                            <tr>
                                <td>{{ $row['invoice'] }}</td>
                                <td>{{ $row['client'] }}</td>
                                <td>KES {{ number_format($row['amount']) }}</td>
                                <td>
                                    <span class="admin-status-chip {{
                                        $row['status'] === 'Paid' ? 'admin-status-chip-active' :
                                        ($row['status'] === 'Overdue' ? 'admin-status-chip-overdue' : 'admin-status-chip-pending')
                                    }}">{{ $row['status'] }}</span>
                                </td>
                                <td>{{ $row['date'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        @if(($filters['type'] ?? 'all') === 'all' || ($filters['type'] ?? 'all') === 'cases')
            <article class="admin-card p-0">
                <div class="flex items-center justify-between p-4">
                    <h3 class="admin-card-title">Case Pipeline Report</h3>
                    <a class="admin-link-btn" href="{{ route('admin.reports.export', ['type' => 'cases']) }}">Export</a>
                </div>
                <div class="admin-table-wrap !rounded-none !border-x-0 !border-b-0">
                    <table class="admin-table">
                        <thead><tr><th>Case</th><th>Officer</th><th>Status</th><th>Amount</th><th>Next Action</th></tr></thead>
                        <tbody>
                            @foreach($cases as $row)
                                <tr>
                                    <td>{{ $row['case'] }}</td>
                                    <td>{{ $row['officer'] }}</td>
                                    <td><span class="admin-status-chip {{
                                        $row['status'] === 'Closed' ? 'admin-status-chip-active' :
                                        ($row['status'] === 'In Progress' ? 'admin-status-chip-progress' : 'admin-status-chip-pending')
                                    }}">{{ $row['status'] }}</span></td>
                                    <td>KES {{ number_format($row['amount']) }}</td>
                                    <td>{{ $row['next_action'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @endif

        @if(($filters['type'] ?? 'all') === 'all' || ($filters['type'] ?? 'all') === 'team')
            <article class="admin-card p-0">
                <div class="flex items-center justify-between p-4">
                    <h3 class="admin-card-title">Team Productivity</h3>
                    <a class="admin-link-btn" href="{{ route('admin.reports.export', ['type' => 'team']) }}">Export</a>
                </div>
                <div class="admin-table-wrap !rounded-none !border-x-0 !border-b-0">
                    <table class="admin-table">
                        <thead><tr><th>Officer</th><th>Assigned Cases</th><th>Closed Cases</th><th>Recovery Value</th></tr></thead>
                        <tbody>
                            @foreach($teamProductivity as $row)
                                <tr>
                                    <td>{{ $row['officer'] }}</td>
                                    <td>{{ $row['assigned'] }}</td>
                                    <td>{{ $row['closed'] }}</td>
                                    <td>KES {{ number_format($row['recovery']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @endif
    </div>

    <article class="admin-card p-4 text-sm text-admin-muted">
        Tip: Run a specific report type, then use Export to download a focused dataset for monthly reviews.
    </article>
</section>
@endsection
