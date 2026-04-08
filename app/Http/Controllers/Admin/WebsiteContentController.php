<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WebsiteContentController extends Controller
{
    private const STORAGE_PATH = 'admin/website-content.json';

    public function index(Request $request): View
    {
        $items = collect($this->items());
        $q = trim((string) $request->query('q', ''));
        $module = trim((string) $request->query('module', ''));
        $status = trim((string) $request->query('status', ''));

        $filtered = $items
            ->when($q !== '', function ($rows) use ($q) {
                $needle = mb_strtolower($q);

                return $rows->filter(function (array $row) use ($needle) {
                    return str_contains(mb_strtolower($row['title']), $needle)
                        || str_contains(mb_strtolower($row['slug']), $needle);
                });
            })
            ->when($module !== '', fn ($rows) => $rows->where('module', $module))
            ->when($status !== '', fn ($rows) => $rows->where('status', $status))
            ->values()
            ->all();

        return view('admin.website-content', [
            'items' => $filtered,
            'total' => count($items),
            'filters' => compact('q', 'module', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.website-content-form', [
            'mode' => 'create',
            'item' => $this->emptyItem(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateItem($request);
        $items = $this->items();
        $nextId = empty($items) ? 1 : (max(array_column($items, 'id')) + 1);
        $data['id'] = $nextId;
        $items[] = $data;
        $this->saveItems($items);

        return redirect()->route('admin.website-content')->with('status', "Content '{$data['title']}' created.");
    }

    public function edit(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.website-content-form', [
            'mode' => 'edit',
            'item' => $item,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $this->validateItem($request);
        $items = $this->items();
        foreach ($items as &$row) {
            if ((int) $row['id'] === $id) {
                $row = array_merge($row, $data);
                break;
            }
        }
        unset($row);
        $this->saveItems($items);

        return redirect()->route('admin.website-content.edit', $id)->with('status', "Content '{$data['title']}' updated.");
    }

    public function deleteConfirm(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.website-content-delete', compact('item'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $items = $this->items();
        $item = collect($items)->firstWhere('id', $id);
        abort_unless($item, 404);

        $items = array_values(array_filter($items, fn (array $row) => (int) $row['id'] !== $id));
        $this->saveItems($items);

        return redirect()->route('admin.website-content')->with('status', "Content '{$item['title']}' deleted.");
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'module' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:8000'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:Published,Draft'],
            'image' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function emptyItem(): array
    {
        return [
            'module' => 'Home',
            'title' => '',
            'subtitle' => '',
            'slug' => '',
            'content' => '',
            'seo_title' => '',
            'seo_description' => '',
            'status' => 'Draft',
            'image' => '',
        ];
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
            ['id' => 1, 'module' => 'Home', 'title' => 'Enterprise Debt Recovery', 'subtitle' => '', 'slug' => '/', 'content' => 'Home page hero and highlights.', 'seo_title' => 'Home | Colldett', 'seo_description' => 'Home page', 'status' => 'Published', 'image' => ''],
            ['id' => 2, 'module' => 'Services', 'title' => 'Our Services', 'subtitle' => '', 'slug' => '/services', 'content' => 'Services overview page.', 'seo_title' => 'Services | Colldett', 'seo_description' => 'Services page', 'status' => 'Published', 'image' => ''],
            ['id' => 3, 'module' => 'Insights', 'title' => 'Insights & Resources', 'subtitle' => '', 'slug' => '/insights', 'content' => 'Insights listing page.', 'seo_title' => 'Insights | Colldett', 'seo_description' => 'Insights page', 'status' => 'Published', 'image' => ''],
            ['id' => 4, 'module' => 'FAQ', 'title' => 'Frequently Asked Questions', 'subtitle' => '', 'slug' => '/faq', 'content' => 'FAQ content.', 'seo_title' => 'FAQ | Colldett', 'seo_description' => 'FAQ page', 'status' => 'Draft', 'image' => ''],
        ];
    }

    private function saveItems(array $items): void
    {
        Storage::disk('local')->put(self::STORAGE_PATH, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
