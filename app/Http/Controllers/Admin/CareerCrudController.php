<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerCrudController extends Controller
{
    public function index(): View
    {
        $items = Career::query()
            ->withCount('applications')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('admin.careers.index', ['items' => $items]);
    }

    public function create(): View
    {
        return view('admin.careers.form', [
            'mode' => 'create',
            'item' => $this->emptyItem(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Career::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'location' => $data['location'] ?? null,
            'department' => $data['department'] ?? null,
            'employment_type' => $data['employment_type'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'closes_at' => $data['closes_at'] ?? null,
        ]);

        return redirect()->route('admin.careers.index')->with('status', "Position '{$data['title']}' created.");
    }

    public function edit(int $id): View
    {
        $item = Career::query()->findOrFail($id);

        return view('admin.careers.form', [
            'mode' => 'edit',
            'item' => $item,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $career = Career::query()->findOrFail($id);
        $data = $this->validated($request, $id);

        $career->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'location' => $data['location'] ?? null,
            'department' => $data['department'] ?? null,
            'employment_type' => $data['employment_type'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'closes_at' => $data['closes_at'] ?? null,
        ]);

        return redirect()->route('admin.careers.edit', $id)->with('status', "Position '{$data['title']}' updated.");
    }

    public function deleteConfirm(int $id): View
    {
        $item = Career::query()->findOrFail($id);

        return view('admin.careers.delete', ['item' => $item]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Career::query()->findOrFail($id);
        $title = $item->title;
        $item->delete();

        return redirect()->route('admin.careers.index')->with('status', "Position '{$title}' deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyItem(): array
    {
        return [
            'id' => null,
            'title' => '',
            'slug' => '',
            'location' => '',
            'department' => '',
            'employment_type' => '',
            'excerpt' => '',
            'description' => '',
            'is_active' => true,
            'sort_order' => 0,
            'closes_at' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:careers,slug';
        if ($ignoreId !== null) {
            $slugRule .= ','.$ignoreId;
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => $slugRule,
            'location' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:120'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:20000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'closes_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (trim((string) ($data['slug'] ?? '')) === '' && ! empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $data;
    }
}
