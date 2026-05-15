<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ClientBillingDocuments;
use App\Support\ClientDirectory;
use App\Support\ClientFiles;
use App\Support\DocumentPlainText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $qRaw = trim((string) $request->query('q', ''));
        $q = mb_strtolower($qRaw);
        $status = trim((string) $request->query('status', ''));

        $items = collect(ClientDirectory::all())
            ->when($q !== '', function ($rows) use ($q) {
                return $rows->filter(function (array $row) use ($q) {
                    $hay = mb_strtolower(
                        ($row['name'] ?? '').' '.($row['company'] ?? '').' '.($row['email'] ?? '')
                        .' '.($row['phone'] ?? '').' '.($row['phone_alt'] ?? '').' '.($row['account_number'] ?? '')
                        .' '.($row['contact_title'] ?? '').' '.($row['address'] ?? '').' '.($row['city'] ?? '')
                        .' '.($row['country'] ?? '').' '.($row['tax_pin'] ?? '').' '.($row['industry'] ?? '')
                        .' '.($row['website'] ?? '').' '.($row['notes'] ?? '')
                    );

                    return str_contains($hay, $q);
                });
            })
            ->when($status !== '', fn ($rows) => $rows->where('status', $status))
            ->values()
            ->all();

        return view('admin.clients', [
            'clients' => $items,
            'filters' => [
                'q' => $qRaw,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.clients-create', [
            'client' => $this->clientFormDefaults(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->trimClientField($this->validateClientFields($request));

        $emailLower = mb_strtolower($data['email']);
        $duplicate = collect(ClientDirectory::all())->contains(
            fn (array $row) => mb_strtolower((string) ($row['email'] ?? '')) === $emailLower
        );
        if ($duplicate) {
            return redirect()->back()->withInput()->withErrors(['email' => 'Another client already uses this email.']);
        }

        $items = ClientDirectory::all();
        $nextId = $items === [] ? 1 : max(array_column($items, 'id')) + 1;

        $items[] = array_merge($this->clientPayloadFromInput($data), [
            'id' => $nextId,
            'account_number' => ClientDirectory::nextAccountNumber($items),
        ]);

        ClientDirectory::save($items);
        ClientFiles::ensureDirectory($nextId);

        return redirect()->route('admin.clients')->with('status', 'Client added successfully.');
    }

    public function show(int $id): View
    {
        $client = ClientDirectory::find($id);
        abort_unless($client, 404);

        ClientFiles::ensureDirectory($id);

        $company = trim((string) ($client['company'] ?? ''));
        $clientDocuments = ClientBillingDocuments::forCompany($company);

        return view('admin.clients-show', [
            'client' => $this->clientForDisplay($client),
            'clientFiles' => ClientFiles::list($id),
            'clientDocuments' => $clientDocuments,
            'hasClientDocuments' => ClientBillingDocuments::hasAny($clientDocuments),
        ]);
    }

    public function uploadFile(Request $request, int $id): RedirectResponse
    {
        abort_unless(ClientDirectory::find($id), 404);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp,zip'],
        ]);

        $filename = ClientFiles::storeUpload($id, $data['file']);

        return redirect()
            ->route('admin.clients.show', $id)
            ->with('status', "File “{$filename}” uploaded.");
    }

    public function downloadFile(int $id, string $filename): BinaryFileResponse
    {
        abort_unless(ClientDirectory::find($id), 404);

        $path = ClientFiles::absolutePath($id, $filename);
        abort_unless($path && is_file($path), 404);

        return response()->download($path, basename($path));
    }

    public function destroyFile(int $id, string $filename): RedirectResponse
    {
        abort_unless(ClientDirectory::find($id), 404);
        abort_unless(ClientFiles::delete($id, $filename), 404);

        return redirect()
            ->route('admin.clients.show', $id)
            ->with('status', 'File removed.');
    }

    public function edit(int $id): View
    {
        $client = ClientDirectory::find($id);
        abort_unless($client, 404);

        return view('admin.clients-edit', ['client' => $this->clientForDisplay($this->mergeClientDefaults($client))]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $existing = ClientDirectory::find($id);
        abort_unless($existing, 404);

        $data = $this->trimClientField($this->validateClientFields($request));

        $emailLower = mb_strtolower($data['email']);
        $duplicate = collect(ClientDirectory::all())->contains(function (array $row) use ($id, $emailLower) {
            return (int) ($row['id'] ?? 0) !== $id
                && mb_strtolower((string) ($row['email'] ?? '')) === $emailLower;
        });
        if ($duplicate) {
            return redirect()->back()->withInput()->withErrors(['email' => 'Another client already uses this email.']);
        }

        $items = [];
        foreach (ClientDirectory::all() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                $items[] = array_merge($row, $this->clientPayloadFromInput($data), [
                    'id' => $id,
                    'account_number' => $row['account_number'] ?? '',
                ]);
            } else {
                $items[] = $row;
            }
        }
        ClientDirectory::save($items);

        return redirect()->route('admin.clients.show', $id)->with('status', 'Client updated.');
    }

    public function deleteConfirm(int $id): View
    {
        $client = ClientDirectory::find($id);
        abort_unless($client, 404);

        return view('admin.clients-delete', ['client' => $client]);
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(ClientDirectory::find($id), 404);

        $items = array_values(array_filter(
            ClientDirectory::all(),
            fn (array $row): bool => (int) ($row['id'] ?? 0) !== $id
        ));
        ClientDirectory::save($items);

        return redirect()->route('admin.clients')->with('status', 'Client removed.');
    }

    /** @return array<string, string> */
    private function clientFormDefaults(): array
    {
        return [
            'name' => '',
            'company' => '',
            'email' => '',
            'phone' => '',
            'phone_alt' => '',
            'contact_title' => '',
            'address' => '',
            'city' => '',
            'country' => '',
            'tax_pin' => '',
            'industry' => '',
            'website' => '',
            'notes' => '',
            'status' => 'active',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string>
     */
    private function mergeClientDefaults(array $row): array
    {
        return array_merge($this->clientFormDefaults(), $row);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function clientPayloadFromInput(array $data): array
    {
        return [
            'name' => $data['name'],
            'company' => $data['company'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'phone_alt' => $data['phone_alt'] ?? '',
            'contact_title' => $this->plainClientField($data['contact_title'] ?? ''),
            'address' => $this->plainClientField($data['address'] ?? ''),
            'city' => $this->plainClientField($data['city'] ?? ''),
            'country' => $this->plainClientField($data['country'] ?? ''),
            'tax_pin' => $data['tax_pin'] ?? '',
            'industry' => $this->plainClientField($data['industry'] ?? ''),
            'website' => $data['website'] ?? '',
            'notes' => $this->plainClientField($data['notes'] ?? ''),
            'status' => $data['status'],
        ];
    }

    private function plainClientField(string $value): string
    {
        return DocumentPlainText::fromHtml($value);
    }

    /**
     * @param  array<string, mixed>  $client
     * @return array<string, mixed>
     */
    private function clientForDisplay(array $client): array
    {
        foreach (['name', 'company', 'contact_title', 'address', 'city', 'country', 'industry', 'notes'] as $key) {
            if (array_key_exists($key, $client)) {
                $client[$key] = $this->plainClientField((string) $client[$key]);
            }
        }

        return $client;
    }

    /** @return array<string, mixed> */
    private function validateClientFields(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'phone_alt' => ['nullable', 'string', 'max:80'],
            'contact_title' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'tax_pin' => ['nullable', 'string', 'max:80'],
            'industry' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function trimClientField(array $data): array
    {
        foreach ([
            'phone', 'phone_alt', 'contact_title', 'address', 'city', 'country', 'tax_pin', 'industry', 'website', 'notes',
        ] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = trim((string) $data[$key]);
            }
        }

        return $data;
    }
}
