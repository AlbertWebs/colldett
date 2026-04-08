<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InsightCrudController extends Controller
{
    public function index(): View
    {
        $this->bootstrapFromConfigIfNeeded();

        return view('admin.insights.index', ['items' => $this->items()]);
    }

    public function create(): View
    {
        return view('admin.insights.form', [
            'mode' => 'create',
            'item' => ['title' => '', 'slug' => '', 'excerpt' => '', 'date' => now()->toDateString(), 'content' => ''],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
            'content' => ['nullable', 'string', 'max:5000'],
        ]);

        Insight::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'excerpt' => $data['excerpt'] ?? '',
            'date' => $data['date'] ?? '',
            'content' => $this->contentToArray($data['content'] ?? ''),
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return redirect()->route('admin.insights.index')->with('status', "Insight '{$data['title']}' created.");
    }

    public function edit(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        $item['content'] = is_array($item['content'] ?? null) ? implode("\n\n", $item['content']) : ($item['content'] ?? '');

        return view('admin.insights.form', ['mode' => 'edit', 'item' => $item]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
            'content' => ['nullable', 'string', 'max:5000'],
        ]);

        Insight::query()->whereKey($id)->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'excerpt' => $data['excerpt'] ?? '',
            'date' => $data['date'] ?? '',
            'content' => $this->contentToArray($data['content'] ?? ''),
        ]);

        return redirect()->route('admin.insights.edit', $id)->with('status', "Insight '{$data['title']}' updated.");
    }

    public function deleteConfirm(int $id): View
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('admin.insights.delete', ['item' => $item]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = collect($this->items())->firstWhere('id', $id);
        abort_unless($item, 404);

        Insight::query()->whereKey($id)->delete();

        return redirect()->route('admin.insights.index')->with('status', "Insight '{$item['title']}' deleted.");
    }

    private function items(): array
    {
        return Insight::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get(['id', 'title', 'slug', 'excerpt', 'date', 'content'])
            ->map(fn (Insight $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'excerpt' => $item->excerpt,
                'date' => $item->date,
                'content' => $item->content,
            ])->all();
    }

    private function contentToArray(string $content): array
    {
        return collect(preg_split('/\R{2,}/', trim($content)) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    private function bootstrapFromConfigIfNeeded(): void
    {
        if (! Schema::hasTable('insights')) {
            return;
        }

        if (Insight::query()->exists()) {
            return;
        }

        foreach (config('colldett.insights', []) as $i => $row) {
            Insight::query()->create([
                'title' => $row['title'] ?? 'Untitled Insight',
                'slug' => $row['slug'] ?? ('insight-'.($i + 1)),
                'excerpt' => $row['excerpt'] ?? '',
                'date' => $row['date'] ?? '',
                'content' => $row['content'] ?? [],
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
