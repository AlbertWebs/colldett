<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ClientDirectory;
use App\Support\TeamDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CaseController extends Controller
{
    private const STORAGE_PATH = 'admin/cases.json';

    public function index(Request $request): View
    {
        $items = collect($this->items());
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $officer = trim((string) $request->query('officer', ''));

        $filtered = $items
            ->when($q !== '', function ($rows) use ($q) {
                $needle = mb_strtolower($q);

                return $rows->filter(function (array $row) use ($needle) {
                    return str_contains(mb_strtolower($row['case_number']), $needle)
                        || str_contains(mb_strtolower($row['client']), $needle)
                        || str_contains(mb_strtolower($row['debtor']), $needle);
                });
            })
            ->when($status !== '', fn ($rows) => $rows->where('status', $status))
            ->when($officer !== '', fn ($rows) => $rows->where('officer', $officer))
            ->values()
            ->all();

        $officers = $items->pluck('officer')->unique()->sort()->values()->all();

        return view('admin.cases', [
            'items' => $filtered,
            'total' => $items->count(),
            'officers' => $officers,
            'filters' => compact('q', 'status', 'officer'),
        ]);
    }

    public function create(): View
    {
        return view('admin.case-form', [
            'mode' => 'create',
            'item' => $this->emptyItem(),
            'clients' => $this->clients(),
            'officers' => $this->officerOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCase($request);
        $items = $this->items();
        $nextId = empty($items) ? 1 : (max(array_column($items, 'id')) + 1);
        $data['id'] = $nextId;
        $data['case_number'] = $this->nextCaseNumber($items);
        $data['notes'] = [];
        $items[] = $data;
        $this->saveItems($items);
        $this->sendAssignmentEmail($data['officer'], $data['case_number'], $data['client'], $data['next_action_date']);

        return redirect()->route('admin.cases')->with('status', "Case {$data['case_number']} created.");
    }

    public function show(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.case-show', ['item' => $item]);
    }

    public function edit(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.case-form', [
            'mode' => 'edit',
            'item' => $item,
            'clients' => $this->clients(),
            'officers' => $this->officerOptions(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $this->validateCase($request);
        $items = $this->items();

        $previousOfficer = null;
        foreach ($items as &$row) {
            if ((int) $row['id'] === $id) {
                $previousOfficer = $row['officer'] ?? null;
                $row = array_merge($row, $data);
                break;
            }
        }
        unset($row);
        $this->saveItems($items);
        if ($previousOfficer !== $data['officer']) {
            $caseNumber = (string) (collect($items)->firstWhere('id', $id)['case_number'] ?? ('CASE-'.$id));
            $this->sendAssignmentEmail($data['officer'], $caseNumber, $data['client'], $data['next_action_date']);
        }

        return redirect()->route('admin.cases.edit', $id)->with('status', 'Case updated.');
    }

    public function addNote(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);
        $items = $this->items();
        foreach ($items as &$row) {
            if ((int) $row['id'] === $id) {
                $row['notes'] = $row['notes'] ?? [];
                $row['notes'][] = [
                    'body' => $data['note'],
                    'created_at' => now()->toDateTimeString(),
                ];
                break;
            }
        }
        unset($row);
        $this->saveItems($items);

        return redirect()->route('admin.cases.show', $id)->with('status', 'Note added.');
    }

    public function close(int $id): RedirectResponse
    {
        $items = $this->items();
        foreach ($items as &$row) {
            if ((int) $row['id'] === $id) {
                $row['status'] = 'Closed';
                break;
            }
        }
        unset($row);
        $this->saveItems($items);

        return redirect()->route('admin.cases')->with('status', 'Case closed.');
    }

    public function deleteConfirm(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.case-delete', ['item' => $item]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $items = $this->items();
        $item = collect($items)->firstWhere('id', $id);
        abort_unless($item, 404);
        $items = array_values(array_filter($items, fn (array $row) => (int) $row['id'] !== $id));
        $this->saveItems($items);

        return redirect()->route('admin.cases')->with('status', "Case {$item['case_number']} deleted.");
    }

    private function validateCase(Request $request): array
    {
        return $request->validate([
            'client' => ['required', 'string', 'max:255'],
            'debtor' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:Pending,In Progress,Closed'],
            'officer' => ['required', 'string', 'max:255'],
            'next_action_date' => ['required', 'date'],
        ]);
    }

    private function items(): array
    {
        if (Storage::disk('local')->exists(self::STORAGE_PATH)) {
            $decoded = json_decode(Storage::disk('local')->get(self::STORAGE_PATH), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [
            ['id' => 1, 'case_number' => 'CASE-004281', 'client' => 'Prime Foods Ltd', 'debtor' => 'Kibera Traders', 'amount' => 'KES 740,000', 'status' => 'Pending', 'officer' => 'Daglas', 'next_action_date' => '2026-04-12', 'notes' => []],
            ['id' => 2, 'case_number' => 'CASE-004282', 'client' => 'Apex Motors', 'debtor' => 'City Freight Ltd', 'amount' => 'KES 2,100,000', 'status' => 'In Progress', 'officer' => 'Phoebe', 'next_action_date' => '2026-04-10', 'notes' => []],
        ];
    }

    private function saveItems(array $items): void
    {
        Storage::disk('local')->put(self::STORAGE_PATH, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function emptyItem(): array
    {
        return [
            'client' => '',
            'debtor' => '',
            'amount' => '',
            'status' => 'Pending',
            'officer' => '',
            'next_action_date' => now()->toDateString(),
        ];
    }

    private function nextCaseNumber(array $items): string
    {
        $max = 4280;
        foreach ($items as $item) {
            if (preg_match('/CASE-(\d+)/', (string) ($item['case_number'] ?? ''), $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return 'CASE-'.str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
    }

    private function clients(): array
    {
        $fromCases = collect($this->items())->pluck('client')->filter();
        $fromDirectory = ClientDirectory::companyNamesForSelect();

        return collect($fromDirectory)
            ->merge($fromCases)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function officerOptions(): array
    {
        return collect(array_keys($this->officerDirectory()))->sort()->values()->all();
    }

    private function officerDirectory(): array
    {
        $directory = [];
        foreach (TeamDirectory::all() as $member) {
            $name = trim((string) ($member['name'] ?? ''));
            $email = trim((string) ($member['email'] ?? ''));
            if ($name !== '' && $email !== '') {
                $directory[$name] = $email;
            }
        }

        // Keep compatibility for historical misspelling in sample data.
        if (isset($directory['Daglaus Omondi']) && ! isset($directory['Daglas'])) {
            $directory['Daglas'] = $directory['Daglaus Omondi'];
        }

        return $directory;
    }

    private function sendAssignmentEmail(string $officerName, string $caseNumber, string $client, string $nextActionDate): void
    {
        $email = $this->officerDirectory()[$officerName] ?? null;
        if (! $email) {
            return;
        }

        try {
            Mail::raw(
                "You have been assigned case {$caseNumber} for {$client}. Next action date: {$nextActionDate}.",
                function ($message) use ($email, $caseNumber): void {
                    $message->to($email)->subject("New Case Assignment: {$caseNumber}");
                }
            );
        } catch (\Throwable $exception) {
            Log::warning('Case assignment email failed.', [
                'officer' => $officerName,
                'email' => $email,
                'case' => $caseNumber,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
