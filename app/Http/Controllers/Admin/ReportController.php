<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'from' => $request->query('from', now()->subDays(30)->toDateString()),
            'to' => $request->query('to', now()->toDateString()),
            'type' => $request->query('type', 'all'),
        ];

        $collections = [
            ['client' => 'Prime Foods Ltd', 'invoiced' => 1200000, 'collected' => 980000, 'outstanding' => 220000, 'rate' => 82],
            ['client' => 'Apex Motors', 'invoiced' => 2400000, 'collected' => 1600000, 'outstanding' => 800000, 'rate' => 67],
            ['client' => 'Metro Health', 'invoiced' => 900000, 'collected' => 760000, 'outstanding' => 140000, 'rate' => 84],
        ];

        $billing = [
            ['invoice' => 'INV-2026-1002', 'client' => 'Prime Foods Ltd', 'amount' => 250000, 'status' => 'Paid', 'date' => '2026-04-08'],
            ['invoice' => 'INV-2026-1003', 'client' => 'Apex Motors', 'amount' => 410000, 'status' => 'Overdue', 'date' => '2026-04-05'],
            ['invoice' => 'INV-2026-1004', 'client' => 'Metro Health', 'amount' => 185000, 'status' => 'Pending', 'date' => '2026-04-03'],
        ];

        $cases = [
            ['case' => 'CASE-004281', 'officer' => 'Daglas', 'status' => 'Pending', 'amount' => 740000, 'next_action' => '2026-04-12'],
            ['case' => 'CASE-004282', 'officer' => 'Phoebe', 'status' => 'In Progress', 'amount' => 2100000, 'next_action' => '2026-04-10'],
            ['case' => 'CASE-004283', 'officer' => 'Julius', 'status' => 'Closed', 'amount' => 680000, 'next_action' => 'Completed'],
        ];

        $teamProductivity = [
            ['officer' => 'Daglas', 'assigned' => 34, 'closed' => 12, 'recovery' => 2100000],
            ['officer' => 'Phoebe', 'assigned' => 28, 'closed' => 10, 'recovery' => 1840000],
            ['officer' => 'Julius', 'assigned' => 22, 'closed' => 14, 'recovery' => 2360000],
        ];

        $totalInvoiced = collect($collections)->sum('invoiced');
        $totalCollected = collect($collections)->sum('collected');
        $totalOutstanding = collect($collections)->sum('outstanding');
        $collectionRate = $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 1) : 0;

        $kpis = [
            ['label' => 'Total Invoiced', 'value' => $this->money($totalInvoiced)],
            ['label' => 'Total Collected', 'value' => $this->money($totalCollected)],
            ['label' => 'Outstanding Balance', 'value' => $this->money($totalOutstanding)],
            ['label' => 'Collection Rate', 'value' => $collectionRate.'%'],
            ['label' => 'Open Cases', 'value' => (string) collect($cases)->whereIn('status', ['Pending', 'In Progress'])->count()],
            ['label' => 'Closed Cases', 'value' => (string) collect($cases)->where('status', 'Closed')->count()],
        ];

        return view('admin.reports', compact('filters', 'kpis', 'collections', 'billing', 'cases', 'teamProductivity'));
    }

    public function export(Request $request): StreamedResponse
    {
        $type = $request->query('type', 'all');
        $rows = $this->exportRows($type);
        $filename = 'report-'.$type.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function exportRows(string $type): array
    {
        return match ($type) {
            'collections' => [
                ['Client', 'Invoiced', 'Collected', 'Outstanding', 'Rate %'],
                ['Prime Foods Ltd', 1200000, 980000, 220000, 82],
                ['Apex Motors', 2400000, 1600000, 800000, 67],
                ['Metro Health', 900000, 760000, 140000, 84],
            ],
            'billing' => [
                ['Invoice', 'Client', 'Amount', 'Status', 'Date'],
                ['INV-2026-1002', 'Prime Foods Ltd', 250000, 'Paid', '2026-04-08'],
                ['INV-2026-1003', 'Apex Motors', 410000, 'Overdue', '2026-04-05'],
                ['INV-2026-1004', 'Metro Health', 185000, 'Pending', '2026-04-03'],
            ],
            'cases' => [
                ['Case', 'Officer', 'Status', 'Amount', 'Next Action'],
                ['CASE-004281', 'Daglas', 'Pending', 740000, '2026-04-12'],
                ['CASE-004282', 'Phoebe', 'In Progress', 2100000, '2026-04-10'],
                ['CASE-004283', 'Julius', 'Closed', 680000, 'Completed'],
            ],
            'team' => [
                ['Officer', 'Assigned', 'Closed', 'Recovery'],
                ['Daglas', 34, 12, 2100000],
                ['Phoebe', 28, 10, 1840000],
                ['Julius', 22, 14, 2360000],
            ],
            default => [
                ['Report', 'Value'],
                ['Total Invoiced', 4500000],
                ['Total Collected', 3340000],
                ['Outstanding', 1160000],
                ['Collection Rate', '74.2%'],
            ],
        };
    }

    private function money(int $amount): string
    {
        return 'KES '.number_format($amount);
    }
}
