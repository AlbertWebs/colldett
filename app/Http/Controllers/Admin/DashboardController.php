<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Support\ClientDirectory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $clientsCount = count(ClientDirectory::all());
        $cases = $this->readJsonList('admin/cases.json');
        $casesTotal = count($cases);
        $pendingCases = count(array_filter($cases, static fn (array $row): bool => ($row['status'] ?? '') !== 'Closed'));

        // Billing is currently stored as JSON sequences + an invoice index.
        $invoiceRows = $this->readJsonList('admin/billing_invoices.json');
        $invoicesTotal = count($invoiceRows);

        // Payment receipts are sequence-only (no index), so we show "issued to date" from the sequence counter.
        $paymentsTotal = $this->readSeqCounter('admin/billing_payment_seq.json');

        $inquiriesTotal = (Schema::hasTable('inquiries')) ? (int) Inquiry::query()->count() : 0;

        return view('admin.dashboard', [
            'kpis' => [
                ['Total Clients', number_format($clientsCount)],
                ['Total Cases', number_format($casesTotal)],
                ['Pending Cases', number_format($pendingCases)],
                ['Total Invoices', number_format($invoicesTotal)],
                ['Payment receipts issued', number_format($paymentsTotal)],
                ['Inbound inquiries', number_format($inquiriesTotal)],
            ],
        ]);
    }

    /** @return list<array<string, mixed>> */
    private function readJsonList(string $path): array
    {
        if (! Storage::disk('local')->exists($path)) {
            return [];
        }

        $decoded = json_decode((string) Storage::disk('local')->get($path), true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function readSeqCounter(string $path): int
    {
        if (! Storage::disk('local')->exists($path)) {
            return 0;
        }

        $decoded = json_decode((string) Storage::disk('local')->get($path), true);
        if (! is_array($decoded)) {
            return 0;
        }

        return (int) ($decoded['next'] ?? 0);
    }
}

