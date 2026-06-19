<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\CareerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CareerApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $careerId = $request->integer('career_id') ?: null;

        $applications = CareerApplication::query()
            ->with('career:id,title,slug')
            ->when($careerId, fn ($q) => $q->where('career_id', $careerId))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $careers = Career::query()
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.career-applications.index', [
            'applications' => $applications,
            'careers' => $careers,
            'careerId' => $careerId,
        ]);
    }

    public function show(int $id): View
    {
        $application = CareerApplication::query()
            ->with('career')
            ->findOrFail($id);

        return view('admin.career-applications.show', [
            'application' => $application,
            'statuses' => CareerApplication::STATUSES,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $application = CareerApplication::query()->findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', CareerApplication::STATUSES)],
        ]);

        $application->update(['status' => $data['status']]);

        return redirect()
            ->route('admin.career-applications.show', $id)
            ->with('status', 'Application status updated.');
    }

    public function downloadResume(int $id): BinaryFileResponse
    {
        return $this->downloadDocument($id, 0);
    }

    public function downloadDocument(int $id, int $index): BinaryFileResponse
    {
        $application = CareerApplication::query()->findOrFail($id);
        $documents = $application->documentEntries();
        $document = $documents[$index] ?? null;

        abort_unless(is_array($document) && ! empty($document['path']), 404);

        $path = Storage::disk('local')->path($document['path']);
        abort_unless(is_file($path), 404);

        return response()->download($path, $document['original_name']);
    }
}
