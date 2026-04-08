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

class ServiceCrudController extends Controller
{
    private const STORAGE_PATH = 'admin/services.json';

    public function index(Request $request): View
    {
        $items = collect($this->items());
        $q = trim((string) $request->query('q', ''));

        $filtered = $items->when($q !== '', function ($rows) use ($q) {
            $needle = mb_strtolower($q);

            return $rows->filter(function (array $row) use ($needle) {
                return str_contains(mb_strtolower($row['name']), $needle)
                    || str_contains(mb_strtolower($row['slug']), $needle)
                    || str_contains(mb_strtolower($row['description']), $needle);
            });
        })->values();

        return view('admin.services.index', [
            'items' => $filtered->all(),
            'total' => $items->count(),
            'filters' => ['q' => $q],
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', ['mode' => 'create', 'item' => ['name' => '', 'slug' => '', 'description' => '', 'image' => '']]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        $items = $this->items();
        $nextId = empty($items) ? 1 : (max(array_column($items, 'id')) + 1);
        $record = [
            'id' => $nextId,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'image' => '',
        ];
        if ($request->hasFile('image_file')) {
            $record['image'] = $this->storeUploadedImage($request->file('image_file'));
        }

        $items[] = $record;
        $this->saveItems($items);

        return redirect()->route('admin.services.index')->with('status', "Service '{$data['name']}' created.");
    }

    public function edit(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.services.form', ['mode' => 'edit', 'item' => $item]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        $items = $this->items();
        foreach ($items as &$row) {
            if ((int) $row['id'] === $id) {
                $row['name'] = $data['name'];
                $row['slug'] = $data['slug'];
                $row['description'] = $data['description'] ?? '';
                if ($request->hasFile('image_file')) {
                    $row['image'] = $this->storeUploadedImage($request->file('image_file'));
                }
                break;
            }
        }
        unset($row);
        $this->saveItems($items);

        return redirect()->route('admin.services.edit', $id)->with('status', "Service '{$data['name']}' updated.");
    }

    public function deleteConfirm(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.services.delete', ['item' => $item]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $items = $this->items();
        $item = collect($items)->firstWhere('id', $id);
        abort_unless($item, 404);

        $items = array_values(array_filter($items, fn (array $row) => (int) $row['id'] !== $id));
        $this->saveItems($items);

        return redirect()->route('admin.services.index')->with('status', "Service '{$item['name']}' deleted.");
    }

    private function items(): array
    {
        if (Storage::disk('local')->exists(self::STORAGE_PATH)) {
            $decoded = json_decode(Storage::disk('local')->get(self::STORAGE_PATH), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return array_map(static function (array $service, int $index): array {
            return [
                'id' => $index + 1,
                'name' => $service['name'],
                'slug' => $service['slug'],
                'description' => $service['description'] ?? '',
                'image' => $service['image'] ?? '',
            ];
        }, config('colldett.services', [
            ['id' => 1, 'name' => 'Debt Recovery', 'slug' => 'debt-recovery', 'description' => 'End-to-end commercial and consumer debt recovery with structured escalation and compliance-focused execution.'],
            ['id' => 2, 'name' => 'Asset Tracing', 'slug' => 'asset-tracing', 'description' => 'Professional tracing of movable and immovable assets to support enforcement, negotiations, and legal action.'],
            ['id' => 3, 'name' => 'Insurance Tracing', 'slug' => 'insurance-tracing', 'description' => 'Evidence-led tracing services for insurers and legal teams handling claims, fraud signals, and recoveries.'],
            ['id' => 4, 'name' => 'Investigations & Field Services', 'slug' => 'investigations', 'description' => 'On-ground investigations, verification, and field intelligence to de-risk recovery and litigation decisions.'],
            ['id' => 5, 'name' => 'Skip Tracing', 'slug' => 'skip-tracing', 'description' => 'Targeted debtor location services using lawful data points and professional field follow-through.'],
            ['id' => 6, 'name' => 'Debt Portfolio Management', 'slug' => 'debt-portfolio-management', 'description' => 'Portfolio triage, segmentation, and action plans that improve liquidation rates across aging accounts.'],
            ['id' => 7, 'name' => 'Car Tracking', 'slug' => 'car-tracking', 'description' => 'Vehicle tracking device fitting, real-time monitoring, remote engine immobilization, and fleet oversight for stronger asset control.'],
            ['id' => 8, 'name' => 'Colldett Microfinance', 'slug' => 'colldett-microfinance', 'description' => 'A future financial services division focused on accessible and structured microfinance solutions for individuals and businesses.'],
        ]), array_keys(config('colldett.services', [])));
    }

    private function saveItems(array $items): void
    {
        Storage::disk('local')->put(self::STORAGE_PATH, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function storeUploadedImage(UploadedFile $file): string
    {
        $uploadDir = public_path('uploads/services');
        if (! File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = 'service-'.now()->format('YmdHis').'-'.Str::random(6).'.'.$extension;
        $file->move($uploadDir, $filename);

        return 'uploads/services/'.$filename;
    }
}
