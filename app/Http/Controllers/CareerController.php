<?php

namespace App\Http\Controllers;

use App\Models\Career;
use App\Models\CareerApplication;
use App\Support\DocumentPlainText;
use App\Support\RichContentHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class CareerController extends SiteController
{
    public function index(): View
    {
        $data = $this->viewData('Careers');
        $data['metaDescription'] = 'Explore career opportunities at Colldett Trace Limited and join a team delivering ethical debt recovery, tracing, and investigation services.';
        $data['metaKeywords'] = 'careers Colldett Trace, jobs Kenya, debt recovery careers';
        $data['canonicalUrl'] = route('careers', absolute: true);
        $data['ogImageAlt'] = 'Careers — '.$data['site']['company']['name'];
        $data['careers'] = Career::query()
            ->open()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('pages.careers', $data);
    }

    public function show(string $slug): View
    {
        $career = Career::query()->open()->where('slug', $slug)->firstOrFail();

        $data = $this->viewData(DocumentPlainText::fromHtml($career->title));
        $data['metaDescription'] = RichContentHtml::plainExcerpt($career->excerpt, 160)
            ?: 'Apply for '.$career->title.' at Colldett Trace Limited.';
        $data['canonicalUrl'] = route('careers.show', $career->slug, absolute: true);
        $data['ogImageAlt'] = $career->title.' — '.$data['site']['company']['name'];
        $data['career'] = $career;

        return view('pages.career-show', $data);
    }

    public function apply(Request $request, string $slug): RedirectResponse
    {
        $career = Career::query()->open()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'documents' => ['required', 'array', 'min:1', 'max:5'],
            'documents.*' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $storedDocuments = [];
        /** @var array<int, UploadedFile> $files */
        $files = $request->file('documents', []);

        foreach ($files as $file) {
            $storedDocuments[] = [
                'path' => $file->store('career-applications/'.$career->id, 'local'),
                'original_name' => $file->getClientOriginalName(),
            ];
        }

        $primary = $storedDocuments[0];

        CareerApplication::query()->create([
            'career_id' => $career->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'resume_path' => $primary['path'],
            'resume_original_name' => $primary['original_name'],
            'documents' => $storedDocuments,
            'status' => 'new',
        ]);

        return redirect()
            ->route('careers.show', $career->slug)
            ->with('status', 'Thank you. Your application has been received and our team will review it shortly.');
    }
}
