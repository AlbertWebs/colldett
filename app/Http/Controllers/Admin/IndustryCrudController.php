<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IndustryCrudController extends Controller
{
    private const STORAGE_PATH = 'admin/industries.json';

    public function index(): View
    {
        return view('admin.industries.index', ['items' => $this->items()]);
    }

    public function create(): View
    {
        return view('admin.industries.form', ['mode' => 'create', 'item' => ['name' => '', 'description' => '', 'image' => '']]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        $items = $this->items();
        $nextId = empty($items) ? 1 : (max(array_column($items, 'id')) + 1);
        $record = [
            'id' => $nextId,
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'image' => '',
        ];

        if ($request->hasFile('image_file')) {
            $record['image'] = $this->storeUploadedImage($request->file('image_file'));
        }

        $items[] = $record;
        $this->saveItems($items);

        return redirect()->route('admin.industries.index')->with('status', "Industry '{$data['name']}' created.");
    }

    public function edit(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.industries.form', ['mode' => 'edit', 'item' => $item]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        $items = $this->items();
        foreach ($items as &$record) {
            if ((int) $record['id'] !== $id) {
                continue;
            }

            $record['name'] = $data['name'];
            $record['description'] = $data['description'] ?? '';
            if ($request->hasFile('image_file')) {
                $record['image'] = $this->storeUploadedImage($request->file('image_file'));
            }
            break;
        }
        unset($record);

        $this->saveItems($items);

        return redirect()->route('admin.industries.edit', $id)->with('status', "Industry '{$data['name']}' updated.");
    }

    public function deleteConfirm(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.industries.delete', ['item' => $item]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $items = $this->items();
        $item = collect($items)->firstWhere('id', $id);
        abort_unless($item, 404);

        $items = array_values(array_filter($items, fn (array $row) => (int) $row['id'] !== $id));
        $this->saveItems($items);

        return redirect()->route('admin.industries.index')->with('status', "Industry '{$item['name']}' deleted.");
    }

    private function items(): array
    {
        $stored = $this->readStoredItems();
        if (! empty($stored)) {
            return $stored;
        }

        return [
            ['id' => 1, 'name' => 'Banks', 'description' => 'Recovery and tracing support for banks.'],
            ['id' => 2, 'name' => 'Microfinance Institutions', 'description' => 'Collections and portfolio support for MFIs.'],
            ['id' => 3, 'name' => 'SACCOs', 'description' => 'Debt recovery and tracing support tailored for SACCO portfolios.'],
            ['id' => 4, 'name' => 'Insurance Companies', 'description' => 'Tracing and claims-related investigations.'],
            ['id' => 5, 'name' => 'Corporates', 'description' => 'Commercial debt recovery, compliance support and investigative services.'],
            ['id' => 6, 'name' => 'Law Firms', 'description' => 'Investigative and enforcement-ready tracing support for legal teams.'],
        ];
    }

    private function readStoredItems(): array
    {
        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return [];
        }

        $decoded = json_decode(Storage::disk('local')->get(self::STORAGE_PATH), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function saveItems(array $items): void
    {
        Storage::disk('local')->put(self::STORAGE_PATH, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function storeUploadedImage(UploadedFile $file): string
    {
        $uploadDir = public_path('uploads/industries');
        if (! File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = 'industry-'.now()->format('YmdHis').'-'.Str::random(6).'.'.$extension;
        $file->move($uploadDir, $filename);

        return 'uploads/industries/'.$filename;
    }
}
