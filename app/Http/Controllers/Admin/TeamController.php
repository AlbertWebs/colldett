<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TeamDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('admin.team.index', [
            'members' => TeamDirectory::all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.team.form', [
            'mode' => 'create',
            'member' => TeamDirectory::normalizeMember([
                'slug' => '',
                'name' => '',
                'role' => '',
                'department' => '',
                'image' => '',
                'bio' => '',
                'experience_years' => null,
                'location' => '',
                'email' => '',
                'seo_description' => '',
                'specialties' => [],
                'credentials' => [],
                'industries' => [],
                'principles' => [],
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $members = TeamDirectory::all();
        if (collect($members)->contains('slug', $data['slug'])) {
            return back()->withErrors(['slug' => 'This URL slug is already used.'])->withInput();
        }

        $member = TeamDirectory::normalizeMember($data);
        $member['image'] = $this->resolveImageAfterUpload($request, $member['slug'], $member['image']);

        $members[] = $member;
        TeamDirectory::saveMembers($members);

        return redirect()->route('admin.team')->with('status', 'Team member created.');
    }

    public function edit(string $slug): View
    {
        $member = collect(TeamDirectory::all())->firstWhere('slug', $slug);
        abort_unless($member, 404);

        return view('admin.team.form', [
            'mode' => 'edit',
            'member' => $member,
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $members = TeamDirectory::all();
        $index = collect($members)->search(fn (array $m) => ($m['slug'] ?? '') === $slug);
        abort_if($index === false, 404);

        $data = $this->validated($request, $slug);
        $merged = array_merge($members[$index], $data);
        $merged['slug'] = $slug;
        $member = TeamDirectory::normalizeMember($merged);
        $member['image'] = $this->resolveImageAfterUpload($request, $slug, $member['image']);

        $members[$index] = $member;
        TeamDirectory::saveMembers(array_values($members));

        return redirect()->route('admin.team')->with('status', 'Team member updated.');
    }

    public function deleteConfirm(string $slug): View
    {
        $member = collect(TeamDirectory::all())->firstWhere('slug', $slug);
        abort_unless($member, 404);

        return view('admin.team.delete', ['member' => $member]);
    }

    public function destroy(string $slug): RedirectResponse
    {
        $members = array_values(array_filter(
            TeamDirectory::all(),
            static fn (array $m): bool => ($m['slug'] ?? '') !== $slug
        ));
        TeamDirectory::saveMembers($members);

        return redirect()->route('admin.team')->with('status', 'Team member removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?string $existingSlug): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'bio' => ['required', 'string', 'max:5000'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'location' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'specialties_text' => ['nullable', 'string', 'max:5000'],
            'credentials_text' => ['nullable', 'string', 'max:5000'],
            'industries_text' => ['nullable', 'string', 'max:5000'],
            'principles_text' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($existingSlug === null) {
            $rules['slug'] = ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
        }

        $validated = $request->validate($rules);
        $isActive = $request->boolean('is_active');

        return [
            'slug' => $existingSlug ?? (string) $validated['slug'],
            'name' => $validated['name'],
            'role' => $validated['role'],
            'department' => (string) ($validated['department'] ?? ''),
            'image' => (string) ($validated['image'] ?? ''),
            'bio' => $validated['bio'],
            'experience_years' => $validated['experience_years'] ?? null,
            'location' => (string) ($validated['location'] ?? ''),
            'email' => (string) ($validated['email'] ?? ''),
            'seo_description' => (string) ($validated['seo_description'] ?? ''),
            'specialties' => $this->splitLines($validated['specialties_text'] ?? ''),
            'credentials' => $this->splitLines($validated['credentials_text'] ?? ''),
            'industries' => $this->splitLines($validated['industries_text'] ?? ''),
            'principles' => $this->splitLines($validated['principles_text'] ?? ''),
            'is_active' => $isActive,
        ];
    }

    private function splitLines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", (string) $text))));
    }

    private function resolveImageAfterUpload(Request $request, string $slug, string $currentImage): string
    {
        /** @var UploadedFile|null $file */
        $file = $request->file('image_file');
        if (! $file instanceof UploadedFile) {
            return $currentImage;
        }

        $uploadDir = public_path('uploads/team');
        if (! File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $slug.'-'.now()->format('YmdHis').'-'.Str::random(6).'.'.$extension;
        $file->move($uploadDir, $filename);

        return 'uploads/team/'.$filename;
    }
}
