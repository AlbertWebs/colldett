<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        return view('admin.gallery.index', [
            'items' => GalleryItem::query()
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.gallery.form', [
            'mode' => 'create',
            'item' => new GalleryItem(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'image_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $path = $this->storeUploadedImage($request->file('image_file'));

        $item = GalleryItem::query()->create([
            'image_path' => $path,
            'caption' => $data['caption'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.gallery.edit', $item)
            ->with('status', 'Gallery item created.');
    }

    public function edit(GalleryItem $gallery): View
    {
        return view('admin.gallery.form', [
            'mode' => 'edit',
            'item' => $gallery,
        ]);
    }

    public function update(Request $request, GalleryItem $gallery): RedirectResponse
    {
        $data = $request->validate([
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image_file')) {
            $path = $this->storeUploadedImage($request->file('image_file'));
            $gallery->image_path = $path;
        }

        $gallery->caption = $data['caption'] ?? null;
        $gallery->sort_order = (int) ($data['sort_order'] ?? 0);
        $gallery->is_active = $request->boolean('is_active');
        $gallery->save();

        return redirect()
            ->route('admin.gallery.edit', $gallery)
            ->with('status', 'Gallery item updated.');
    }

    public function destroy(GalleryItem $gallery): RedirectResponse
    {
        $gallery->delete();

        return redirect()
            ->route('admin.gallery.index')
            ->with('status', 'Gallery item deleted.');
    }

    private function storeUploadedImage(UploadedFile $file): string
    {
        $uploadDir = public_path('uploads/gallery');
        if (! File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = 'gallery-'.now()->format('YmdHis').'-'.Str::random(8).'.'.$extension;
        $file->move($uploadDir, $filename);

        return 'uploads/gallery/'.$filename;
    }
}

