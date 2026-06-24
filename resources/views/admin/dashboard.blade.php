@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    <div class="admin-dashboard-hero admin-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-admin-muted">Executive Snapshot</p>
                <h2 class="mt-1 text-3xl font-bold tracking-tight">Dashboard</h2>
                <p class="mt-1 text-sm text-admin-muted">Real-time visibility into recovery performance, billing operations, and team execution.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button class="admin-btn-soft">This Month</button>
                <button class="admin-btn-soft">Export Snapshot</button>
                <button class="admin-btn-primary">New Case</button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach(($kpis ?? []) as $stat)
            <article class="admin-stat admin-stat-premium">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs uppercase tracking-wide text-admin-muted">{{ $stat[0] }}</p>
                </div>
                <p class="mt-2 text-xl font-semibold">{{ $stat[1] }}</p>
            </article>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-12">
        <article class="admin-card p-5 xl:col-span-8">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="admin-card-title">Recent Activity</h3>
                <span class="admin-chip">{{ count($recentActivities ?? []) }} events</span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Event</th><th>Entity</th><th>Source</th><th>Time</th></tr></thead>
                    <tbody>
                        @forelse($recentActivities ?? [] as $activity)
                            <tr>
                                <td>{{ $activity['event'] }}</td>
                                <td>
                                    @if(!empty($activity['url']))
                                        <a href="{{ $activity['url'] }}" class="font-medium text-admin-ink underline decoration-slate-300 underline-offset-2 hover:text-admin-primary">{{ $activity['entity'] }}</a>
                                    @else
                                        {{ $activity['entity'] }}
                                    @endif
                                </td>
                                <td>{{ $activity['user'] }}</td>
                                <td title="{{ $activity['occurred_at']->format('j M Y, H:i') }}">{{ $activity['occurred_at']->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-admin-muted">No recent activity yet. New inquiries, applications, cases, and billing documents will appear here.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="admin-card p-5 xl:col-span-4">
            <h3 class="admin-card-title">Quick Actions</h3>
            <div class="mt-4 grid gap-2">
                <a class="admin-btn-soft justify-start" href="{{ route('admin.billing.module.create', 'invoices') }}">Create Invoice</a>
                <a class="admin-btn-soft justify-start" href="{{ route('admin.billing.module.create', 'demand') }}">Create Demand Letter</a>
                <a class="admin-btn-soft justify-start" href="{{ route('admin.billing.module.create', 'payments') }}">Record payment receipt</a>
            </div>
            <div class="mt-4 rounded-lg border border-admin-border bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-admin-muted">Priority Queue</p>
                <ul class="mt-2 space-y-2 text-sm text-admin-muted">
                    <li class="flex items-center justify-between"><span>Overdue invoices</span><strong class="text-admin-ink">12</strong></li>
                    <li class="flex items-center justify-between"><span>Cases due today</span><strong class="text-admin-ink">8</strong></li>
                    <li class="flex items-center justify-between"><span>Unassigned leads</span><strong class="text-admin-ink">5</strong></li>
                </ul>
            </div>
        </article>

        <article class="admin-card p-5 xl:col-span-12">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="admin-card-title">Performance Notes</h3>
                <a href="{{ route('admin.reports') }}" class="admin-link-btn">Open Full Reports</a>
            </div>
            <div class="grid gap-3 md:grid-cols-3">
                <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-admin-muted">Collections Efficiency</p>
                    <p class="mt-1 text-sm text-admin-ink">Recovery cycle improved by 11% compared to last month.</p>
                </div>
                <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-admin-muted">Billing Throughput</p>
                    <p class="mt-1 text-sm text-admin-ink">Invoice processing is stable with reduced pending approvals.</p>
                </div>
                <div class="rounded-lg border border-admin-border bg-slate-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-admin-muted">Operational Risk</p>
                    <p class="mt-1 text-sm text-admin-ink">High-value pending files require immediate assignment follow-up.</p>
                </div>
            </div>
        </article>
    </div>
</section>
@endsection
