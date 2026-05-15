<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStoredSettings;
use App\Support\ClientDirectory;
use App\Support\DocumentPlainText;
use App\Support\FeeNoteReference;
use App\Support\ServiceCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(): View
    {
        return view('admin.billing', [
            'modules' => $this->modules(),
        ]);
    }

    public function create(Request $request, string $module): View
    {
        $meta = $this->moduleMeta($module);
        $values = [];
        if ($module === 'invoices') {
            $values['number'] = $this->peekNextInvoiceNumber();
        }
        if ($module === 'quotations') {
            $values['number'] = $this->peekNextQuotationNumber();
        }
        if ($module === 'fee-notes') {
            $values = array_merge(
                AdminStoredSettings::feeNoteRemittanceDefaults(),
                [
                    'number' => $this->peekNextFeeNoteNumber(),
                    'issued_date' => date('Y-m-d'),
                ]
            );
            $presetClient = trim((string) $request->query('client', ''));
            if ($presetClient !== '') {
                $values['client'] = $presetClient;
            }
        }
        if ($module === 'payments') {
            $values['payment_id'] = $this->peekNextPaymentId();
        }

        return view('admin.billing-form', [
            'meta' => $meta,
            'module' => $module,
            'mode' => 'create',
            'recordId' => null,
            'values' => $values,
            'clients' => $this->clients(),
            'caseReferences' => $module === 'demand' ? $this->caseReferences() : [],
            'invoiceOptions' => $module === 'payments' ? $this->invoiceOptionsForPayments() : [],
            'feeNoteIsDraft' => false,
            'feeNoteAddressByClient' => $module === 'fee-notes' ? ClientDirectory::feeNoteAddressesForForm() : [],
            'feeNoteServices' => $module === 'fee-notes' ? ServiceCatalog::optionsForSelect() : [],
            'feeNoteClientAccountRefs' => $module === 'fee-notes' ? ClientDirectory::accountRefByCompany() : [],
        ]);
    }

    public function moduleIndex(string $module): View
    {
        $meta = $this->moduleMeta($module);

        $rows = $module === 'fee-notes'
            ? $this->readFeeNoteIndexSorted()
            : [$this->sampleValues($module, 1)];

        return view('admin.billing-module-list', [
            'module' => $module,
            'meta' => $meta,
            'rows' => $rows,
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $meta = $this->moduleMeta($module);
        $data = $request->validate($this->rules($module));

        if ($module === 'invoices') {
            $data['number'] = $this->generateNextInvoiceNumber();
            $this->appendInvoiceRecord($data);

            return redirect()
                ->route('admin.billing.module.preview', [$module, 1])
                ->with('status', $meta['singular'].' created successfully.')
                ->with('preview_values', $data);
        }

        if ($module === 'quotations') {
            $data['number'] = $this->generateNextQuotationNumber();

            return redirect()
                ->route('admin.billing.module.preview', [$module, 1])
                ->with('status', $meta['singular'].' created successfully.')
                ->with('preview_values', $data);
        }

        if ($module === 'fee-notes') {
            $data = $this->feeNotePlainTextFields($data);
            $data = $this->feeNoteNormalizeInput($data);
            $data = FeeNoteReference::apply($data);
            $data = AdminStoredSettings::feeNoteFillRemittance($data);
            ClientDirectory::rememberFeeNoteAddress((string) ($data['client'] ?? ''), (string) ($data['address'] ?? ''));
            if ($request->input('fee_note_action') === 'draft') {
                $data['number'] = '';
                $newId = $this->appendFeeNoteDraft($data);

                return redirect()
                    ->route('admin.billing.module.edit', [$module, $newId])
                    ->with('status', 'Fee note saved as draft. You can issue an official number when you are ready.');
            }

            $data['number'] = $this->generateNextFeeNoteNumber();
            $data['is_draft'] = false;
            $newId = $this->appendFeeNoteRecord($data);

            return redirect()
                ->route('admin.billing.module.preview', [$module, $newId])
                ->with('status', $meta['singular'].' created successfully.');
        }

        if ($module === 'payments') {
            $data['payment_id'] = $this->generateNextPaymentId();

            return redirect()
                ->route('admin.billing.module.preview', [$module, 1])
                ->with('status', $meta['singular'].' recorded successfully.')
                ->with('preview_values', $data);
        }

        return redirect()
            ->route('admin.billing.module.edit', [$module, 1])
            ->with('status', $meta['singular'].' created successfully.');
    }

    public function preview(Request $request, string $module, int $id): View
    {
        $meta = $this->moduleMeta($module);
        if ($module === 'fee-notes') {
            $values = $this->feeNoteValuesForDocument($id);
        } else {
            $values = $request->session()->get('preview_values');
            if (! is_array($values) || empty($values)) {
                $values = $this->sampleValues($module, $id);
            }
        }

        return view('admin.billing-preview', [
            'meta' => $meta,
            'module' => $module,
            'recordId' => $id,
            'values' => $values,
        ]);
    }

    /**
     * Standalone page with the same document markup/CSS as the in-app preview (no admin chrome).
     * Use this for printing so output matches the preview; optional ?autoprint=1 opens the print dialog.
     */
    public function printPreview(Request $request, string $module, int $id): View
    {
        $meta = $this->moduleMeta($module);
        if ($module === 'fee-notes') {
            $values = $this->feeNoteValuesForDocument($id);
        } else {
            $values = $request->session()->get('preview_values');
            if (! is_array($values) || empty($values)) {
                $values = $this->sampleValues($module, $id);
            }
        }

        return view('admin.billing-print', [
            'meta' => $meta,
            'module' => $module,
            'recordId' => $id,
            'values' => $values,
        ]);
    }

    public function downloadPreviewPdf(Request $request, string $module, int $id)
    {
        $meta = $this->moduleMeta($module);
        if ($module === 'fee-notes') {
            $values = $this->feeNoteValuesForDocument($id);
        } else {
            $values = $request->session()->get('preview_values');
            if (! is_array($values) || empty($values)) {
                $values = $this->sampleValues($module, $id);
            }
        }

        $docRef = '#'.($values['number'] ?? ($values['payment_id'] ?? ('REC-'.$id)));
        $docTitle = $module === 'invoices'
            ? 'Invoice'
            : ($module === 'demand' ? 'Demand Letter' : ($module === 'fee-notes' ? 'Fee Note' : $meta['singular'].' preview'));
        $slugBase = $module === 'payments'
            ? ($values['payment_id'] ?? 'payment-'.$id)
            : ($values['number'] ?? ($meta['singular'].'-'.$id));
        $filename = Str::slug((string) $slugBase, '-').'.pdf';

        $logoDataUri = $this->publicImageDataUri(AdminStoredSettings::companyLogoRelativePath());

        $pdfOptions = $this->pdfDompdfOptions();

        if ($module === 'invoices') {
            $pdf = Pdf::loadView('admin.billing-invoice-pdf', [
                'values' => $values,
                'documentChromeCss' => $this->documentChromeStylesheet(),
                'invoiceBodyCss' => $this->invoiceBodyStylesheet(),
                'logoUrl' => $logoDataUri,
            ])->setPaper('a4', 'portrait')->setOptions($pdfOptions);
        } elseif ($module === 'quotations') {
            $pdf = Pdf::loadView('admin.billing-quotation-pdf', [
                'values' => $values,
                'documentChromeCss' => $this->documentChromeStylesheet(),
                'invoiceBodyCss' => $this->invoiceBodyStylesheet(),
                'logoUrl' => $logoDataUri,
            ])->setPaper('a4', 'portrait')->setOptions($pdfOptions);
        } elseif ($module === 'payments') {
            $pdf = Pdf::loadView('admin.billing-payment-receipt-pdf', [
                'values' => $values,
                'documentChromeCss' => $this->documentChromeStylesheet(),
                'invoiceBodyCss' => $this->invoiceBodyStylesheet(),
                'logoUrl' => $logoDataUri,
            ])->setPaper('a4', 'portrait')->setOptions($pdfOptions);
        } elseif ($module === 'fee-notes') {
            $pdf = Pdf::loadView('admin.billing-fee-note-pdf', [
                'values' => $values,
                'documentChromeCss' => $this->documentChromeStylesheet(),
                'invoiceBodyCss' => $this->invoiceBodyStylesheet(),
                'logoUrl' => $logoDataUri,
            ])->setPaper('a4', 'portrait')->setOptions($pdfOptions);
        } else {
            $pdf = Pdf::loadView('admin.billing-document-pdf', [
                'meta' => $meta,
                'module' => $module,
                'values' => $values,
                'docRef' => $docRef,
                'docTitle' => $docTitle,
                'logoDataUri' => $logoDataUri,
            ])->setPaper('a4', 'portrait')->setOptions($pdfOptions);
        }

        return $pdf->download($filename);
    }

    public function edit(string $module, int $id): View
    {
        $meta = $this->moduleMeta($module);

        $values = $module === 'fee-notes'
            ? $this->feeNoteValuesForForm($id)
            : $this->sampleValues($module, $id);

        $feeNoteIsDraft = false;
        if ($module === 'fee-notes') {
            $feeRow = $this->findFeeNoteById($id);
            $feeNoteIsDraft = (bool) ($feeRow['is_draft'] ?? false);
        }

        return view('admin.billing-form', [
            'meta' => $meta,
            'module' => $module,
            'mode' => 'edit',
            'recordId' => $id,
            'values' => $values,
            'clients' => $this->clients(),
            'caseReferences' => $module === 'demand' ? $this->caseReferences() : [],
            'invoiceOptions' => $module === 'payments' ? $this->invoiceOptionsForPayments() : [],
            'feeNoteIsDraft' => $feeNoteIsDraft,
            'feeNoteAddressByClient' => $module === 'fee-notes' ? ClientDirectory::feeNoteAddressesForForm() : [],
            'feeNoteServices' => $module === 'fee-notes' ? ServiceCatalog::optionsForSelect() : [],
            'feeNoteClientAccountRefs' => $module === 'fee-notes' ? ClientDirectory::accountRefByCompany() : [],
        ]);
    }

    public function finalizeFeeNote(int $id): RedirectResponse
    {
        $row = $this->findFeeNoteById($id);
        abort_unless($row, 404);
        abort_unless((bool) ($row['is_draft'] ?? false), 403, 'This fee note is already issued.');

        $row['number'] = $this->generateNextFeeNoteNumber();
        $row['is_draft'] = false;
        $row = FeeNoteReference::apply($row);
        $row = AdminStoredSettings::feeNoteFillRemittance($row);
        ClientDirectory::rememberFeeNoteAddress((string) ($row['client'] ?? ''), (string) ($row['address'] ?? ''));
        $this->replaceFeeNoteRecord($row);

        return redirect()
            ->route('admin.billing.module.preview', ['fee-notes', $id])
            ->with('status', 'Fee note issued. Official number assigned — you can print or download the PDF from here.');
    }

    public function update(Request $request, string $module, int $id): RedirectResponse
    {
        $meta = $this->moduleMeta($module);

        if ($module === 'fee-notes') {
            $existing = $this->findFeeNoteById($id);
            abort_unless($existing, 404);
            $data = $request->validate($this->rules($module));
            $data = $this->feeNotePlainTextFields($data);
            $data = $this->feeNoteNormalizeInput($data);
            $merged = array_merge($existing, $data, ['id' => $id]);
            $merged = FeeNoteReference::apply($merged);
            if (($existing['is_draft'] ?? false) === true) {
                $merged['is_draft'] = true;
                $merged['number'] = '';
            } else {
                $merged['is_draft'] = false;
                $merged['number'] = (string) ($existing['number'] ?? '');
            }
            $merged = AdminStoredSettings::feeNoteFillRemittance($merged);
            ClientDirectory::rememberFeeNoteAddress((string) ($merged['client'] ?? ''), (string) ($merged['address'] ?? ''));
            $this->replaceFeeNoteRecord($merged);

            return redirect()
                ->route('admin.billing.module.edit', [$module, $id])
                ->with('status', $meta['singular'].' updated successfully.');
        }

        $request->validate($this->rules($module));

        return redirect()
            ->route('admin.billing.module.edit', [$module, $id])
            ->with('status', $meta['singular'].' updated successfully.');
    }

    private function moduleMeta(string $module): array
    {
        $modules = $this->modules();
        abort_unless(isset($modules[$module]), 404);

        return $modules[$module];
    }

    private function modules(): array
    {
        return [
            'invoices' => [
                'title' => 'Invoices',
                'singular' => 'Invoice',
                'description' => 'Create and edit invoices for client billing.',
                'fields' => [
                    ['name' => 'number', 'label' => 'Invoice Number'],
                    ['name' => 'client', 'label' => 'Client'],
                    ['name' => 'issued_date', 'label' => 'Issued Date', 'type' => 'date'],
                    ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'date'],
                    ['name' => 'amount', 'label' => 'Amount (before VAT)'],
                    ['name' => 'line_description', 'label' => 'Line item description', 'type' => 'textarea'],
                    ['name' => 'billing_address', 'label' => 'Billing address (Invoiced To)', 'type' => 'textarea'],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'quotations' => [
                'title' => 'Quotations',
                'singular' => 'Quotation',
                'description' => 'Create and edit client quotations.',
                'fields' => [
                    ['name' => 'number', 'label' => 'Quotation Number'],
                    ['name' => 'client', 'label' => 'Client'],
                    ['name' => 'valid_until', 'label' => 'Valid Until', 'type' => 'date'],
                    ['name' => 'amount', 'label' => 'Quoted Amount'],
                    ['name' => 'scope', 'label' => 'Scope', 'type' => 'textarea'],
                ],
            ],
            'fee-notes' => [
                'title' => 'Fee Notes',
                'singular' => 'Fee Note',
                'description' => 'Create structured fee notes using the advocate-style format. Bank remittance lines always follow Admin → Settings (Invoices & printable documents → bank remittance fields), same as invoices — they are not frozen per fee note.',
                'fields' => [
                    ['name' => 'number', 'label' => 'Fee Note Number'],
                    ['name' => 'service_id', 'label' => 'Service'],
                    ['name' => 'our_ref', 'label' => 'Our Reference'],
                    ['name' => 'your_ref', 'label' => 'Your Reference'],
                    ['name' => 'client', 'label' => 'Client'],
                    ['name' => 'address', 'label' => 'Client Address', 'type' => 'textarea'],
                    ['name' => 'issued_date', 'label' => 'Issue Date', 'type' => 'date'],
                    ['name' => 'payment_terms', 'label' => 'Payment Terms'],
                    ['name' => 'line_description', 'label' => 'Particulars of Service Rendered', 'type' => 'textarea'],
                    ['name' => 'amount', 'label' => 'Professional Fee (before VAT)'],
                    ['name' => 'vat_rate', 'label' => 'VAT Rate (e.g. 0.16)'],
                    ['name' => 'notes', 'label' => 'Additional Notes', 'type' => 'textarea'],
                ],
                'list_fields' => [
                    ['name' => '__fee_note_status', 'label' => 'Status'],
                    ['name' => 'number', 'label' => 'Number', 'truncate' => true],
                    ['name' => 'client', 'label' => 'Client', 'truncate' => true],
                    ['name' => 'issued_date', 'label' => 'Issue date'],
                    ['name' => 'amount', 'label' => 'Fee (ex VAT)'],
                    ['name' => 'payment_terms', 'label' => 'Terms', 'truncate' => true],
                ],
            ],
            'sla' => [
                'title' => 'SLA / Engagement Letters',
                'singular' => 'SLA / Engagement Letter',
                'description' => 'Create and edit service agreements.',
                'fields' => [
                    ['name' => 'client', 'label' => 'Client'],
                    ['name' => 'scope', 'label' => 'Scope of Work'],
                    ['name' => 'fees', 'label' => 'Fees'],
                    ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
                    ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
                    ['name' => 'terms', 'label' => 'Terms', 'type' => 'textarea'],
                ],
            ],
            'demand' => [
                'title' => 'Demand Letters',
                'singular' => 'Demand Letter',
                'description' => 'Letters are addressed to your client’s debtor (the addressee), not to your engaging client.',
                'fields' => [
                    ['name' => 'client', 'label' => 'Engaging client (instructing party)'],
                    ['name' => 'case_ref', 'label' => 'Case Reference'],
                    ['name' => 'amount', 'label' => 'Demand Amount'],
                    ['name' => 'deadline', 'label' => 'Deadline', 'type' => 'date'],
                    ['name' => 'subject', 'label' => 'Subject'],
                    ['name' => 'body', 'label' => 'Letter (body)', 'type' => 'textarea'],
                ],
            ],
            'payments' => [
                'title' => 'Payment receipts',
                'singular' => 'Payment receipt',
                'description' => 'Record money received against an invoice — this is your official receipt (print/PDF), not a separate document type.',
                'fields' => [
                    ['name' => 'payment_id', 'label' => 'Payment ID'],
                    ['name' => 'client', 'label' => 'Client'],
                    ['name' => 'invoice', 'label' => 'Invoice number'],
                    ['name' => 'amount', 'label' => 'Amount'],
                    ['name' => 'method', 'label' => 'Payment Method'],
                    ['name' => 'date', 'label' => 'Payment Date', 'type' => 'date'],
                    ['name' => 'reference', 'label' => 'Reference'],
                ],
            ],
        ];
    }

    private function rules(string $module): array
    {
        $rules = [];
        foreach ($this->moduleMeta($module)['fields'] as $field) {
            $max = ($module === 'demand' && $field['name'] === 'body') ? 8000 : 2000;
            $rules[$field['name']] = ['nullable', 'string', 'max:'.$max];
        }
        if ($module === 'fee-notes') {
            $rules['service_id'] = ['nullable', 'integer', 'min:1'];
        }

        return $rules;
    }

    private function sampleValues(string $module, int $id): array
    {
        $samples = [
            'invoices' => [
                'number' => 'INV-2026-1002',
                'client' => 'Prime Foods Ltd',
                'issued_date' => '2026-04-01',
                'due_date' => '2026-04-15',
                'amount' => '250000',
                'line_description' => 'Debt recovery services — monthly portfolio support and case reporting.',
                'billing_address' => "Prime Foods Ltd\nATTN: Accounts Payable\nIndustrial Area, Nairobi\nNairobi, Kenya\n00100",
                'notes' => 'Thank you for your business.',
            ],
            'quotations' => ['number' => 'QTN-2026-1001', 'client' => 'Apex Motors', 'valid_until' => '2026-04-30', 'amount' => '410000', 'scope' => 'Debt tracing and legal demand support'],
            'fee-notes' => AdminStoredSettings::feeNoteFillRemittance([
                'number' => 'FN-2026-1001',
                'service_id' => '1',
                'our_ref' => '1/001/2026',
                'your_ref' => '4523',
                'client' => 'MORANI LIMITED',
                'address' => "P.O BOX 3146-10400\nNYERI\nKENYA\nTel No: +254 721 385 891\nEmail: accounts@sirimon.co.ke",
                'issued_date' => '2026-03-12',
                'payment_terms' => 'IMMEDIATE',
                'line_description' => 'Professional fees for debt collection KES 53,216 at a commission rate of 10%.',
                'amount' => '5321.60',
                'vat_rate' => '0.16',
                'notes' => 'When replying please quote our reference.',
            ]),
            'sla' => ['client' => 'Metro Health', 'scope' => 'Portfolio recovery support', 'fees' => '8% success fee', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'terms' => 'Monthly reporting and weekly case updates'],
            'demand' => [
                'client' => 'Apex Motors',
                'case_ref' => 'CASE-004282',
                'amount' => '2100000',
                'deadline' => '2026-04-20',
                'subject' => 'Formal demand for payment — outstanding balance KES 2,100,000',
                'body' => "Dear Sir/Madam,\n\nWe act on behalf of our client Apex Motors regarding the above-referenced matter. Despite prior correspondence, the sum of KES 2,100,000 remains due and payable.\n\nTake notice that unless payment is received in full on or before the deadline below, our client reserves the right to pursue recovery without further reference to you.\n\nYours faithfully,",
            ],
            'payments' => ['payment_id' => 'PM-2026-1001', 'client' => 'Prime Foods Ltd', 'invoice' => 'INV-2026-1002', 'amount' => '250000', 'method' => 'Bank', 'date' => '2026-04-08', 'reference' => 'TRX-98310'],
        ];

        return $samples[$module] ?? ['id' => $id];
    }

    private function clients(): array
    {
        return ClientDirectory::companyNamesForSelect();
    }

    /**
     * Case numbers from the same store as {@see CaseController} (admin/cases.json).
     */
    private function caseReferences(): array
    {
        $path = 'admin/cases.json';
        $items = [];
        if (Storage::disk('local')->exists($path)) {
            $decoded = json_decode(Storage::disk('local')->get($path), true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }
        if ($items === []) {
            $items = [
                ['case_number' => 'CASE-004281'],
                ['case_number' => 'CASE-004282'],
            ];
        }

        return collect($items)
            ->pluck('case_number')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private const INVOICE_SEQ_PATH = 'admin/billing_invoice_seq.json';

    /** @var string Persisted invoice rows for payment allocation dropdown (number, client, amount, …). */
    private const INVOICE_INDEX_PATH = 'admin/billing_invoices.json';

    private const QUOTATION_SEQ_PATH = 'admin/billing_quotation_seq.json';

    private const FEE_NOTE_SEQ_PATH = 'admin/billing_fee_note_seq.json';

    /** @var string Persisted fee note rows (full field set + id). */
    private const FEE_NOTE_INDEX_PATH = 'admin/billing_fee_notes.json';

    private const PAYMENT_SEQ_PATH = 'admin/billing_payment_seq.json';

    private function peekNextInvoiceNumber(): string
    {
        $year = (int) date('Y');
        $last = $this->invoiceLastIssued();

        return sprintf('INV-%d-%04d', $year, $last + 1);
    }

    private function generateNextInvoiceNumber(): string
    {
        $year = (int) date('Y');
        $last = $this->invoiceLastIssued();
        $next = $last + 1;
        Storage::disk('local')->put(self::INVOICE_SEQ_PATH, json_encode([
            'year' => $year,
            'last' => $next,
        ], JSON_PRETTY_PRINT));

        return sprintf('INV-%d-%04d', $year, $next);
    }

    /**
     * Last issued sequence segment for the current year (e.g. 1002 for INV-2026-1002).
     * If missing or new year, starts from 1000 so the first number is …1001.
     */
    private function invoiceLastIssued(): int
    {
        $year = (int) date('Y');
        if (! Storage::disk('local')->exists(self::INVOICE_SEQ_PATH)) {
            return 1000;
        }
        $data = json_decode(Storage::disk('local')->get(self::INVOICE_SEQ_PATH), true);
        if (! is_array($data)) {
            return 1000;
        }
        if ((int) ($data['year'] ?? 0) !== $year) {
            return 1000;
        }

        return (int) ($data['last'] ?? 1000);
    }

    /**
     * Invoices for payment dropdown: number + label with client & amount.
     *
     * @return list<array{number: string, client: string, amount: string, label: string}>
     */
    private function invoiceOptionsForPayments(): array
    {
        $currency = AdminStoredSettings::invoice()['currency'] ?? 'Ksh';
        $rows = $this->readInvoiceIndex();
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $num = trim((string) ($row['number'] ?? ''));
            if ($num === '') {
                continue;
            }
            $out[] = [
                'number' => $num,
                'client' => trim((string) ($row['client'] ?? '')),
                'amount' => trim((string) ($row['amount'] ?? '')),
                'label' => $this->formatInvoiceDropdownLabel($row, $currency),
            ];
        }
        usort($out, fn (array $a, array $b): int => strcmp($b['number'], $a['number']));

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function appendInvoiceRecord(array $data): void
    {
        $num = trim((string) ($data['number'] ?? ''));
        if ($num === '') {
            return;
        }
        $rows = $this->readInvoiceIndex();
        $rows = array_values(array_filter($rows, fn ($r) => is_array($r) && (string) ($r['number'] ?? '') !== $num));
        $rows[] = [
            'number' => $num,
            'client' => trim((string) ($data['client'] ?? '')),
            'amount' => trim((string) ($data['amount'] ?? '')),
            'issued_date' => trim((string) ($data['issued_date'] ?? '')),
        ];
        usort($rows, fn ($a, $b): int => strcmp((string) ($a['number'] ?? ''), (string) ($b['number'] ?? '')));
        Storage::disk('local')->put(self::INVOICE_INDEX_PATH, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readInvoiceIndex(): array
    {
        if (! Storage::disk('local')->exists(self::INVOICE_INDEX_PATH)) {
            $defaults = $this->defaultInvoiceIndex();
            Storage::disk('local')->put(self::INVOICE_INDEX_PATH, json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $defaults;
        }
        $decoded = json_decode(Storage::disk('local')->get(self::INVOICE_INDEX_PATH), true);
        if (! is_array($decoded) || $decoded === []) {
            return $this->defaultInvoiceIndex();
        }

        return $decoded;
    }

    /**
     * @return list<array<string, string>>
     */
    private function defaultInvoiceIndex(): array
    {
        return [
            [
                'number' => 'INV-2026-1002',
                'client' => 'Prime Foods Ltd',
                'amount' => '250000',
                'issued_date' => '2026-04-01',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function formatInvoiceDropdownLabel(array $row, string $currency): string
    {
        $num = trim((string) ($row['number'] ?? ''));
        $client = trim((string) ($row['client'] ?? '')) ?: '—';
        $rawAmt = (string) ($row['amount'] ?? '');
        $amt = $rawAmt !== '' && is_numeric($rawAmt)
            ? $currency.' '.number_format((float) $rawAmt, 2, '.', ',')
            : ($rawAmt !== '' ? $rawAmt : '—');

        return $num.' — '.$client.' — '.$amt;
    }

    private function peekNextQuotationNumber(): string
    {
        $year = (int) date('Y');
        $last = $this->quotationLastIssued();

        return sprintf('QTN-%d-%04d', $year, $last + 1);
    }

    private function generateNextQuotationNumber(): string
    {
        $year = (int) date('Y');
        $last = $this->quotationLastIssued();
        $next = $last + 1;
        Storage::disk('local')->put(self::QUOTATION_SEQ_PATH, json_encode([
            'year' => $year,
            'last' => $next,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return sprintf('QTN-%d-%04d', $year, $next);
    }

    /**
     * Last issued quotation sequence segment for the current year (e.g. 1019 for QTN-2026-1019).
     * New year resets; first number of a year is …1001 (starts from last = 1000).
     */
    private function quotationLastIssued(): int
    {
        $year = (int) date('Y');
        if (! Storage::disk('local')->exists(self::QUOTATION_SEQ_PATH)) {
            return 1000;
        }
        $data = json_decode(Storage::disk('local')->get(self::QUOTATION_SEQ_PATH), true);
        if (! is_array($data)) {
            return 1000;
        }
        if ((int) ($data['year'] ?? 0) !== $year) {
            return 1000;
        }

        return (int) ($data['last'] ?? 1000);
    }

    private function peekNextFeeNoteNumber(): string
    {
        $year = (int) date('Y');
        $last = $this->feeNoteLastIssued();

        return sprintf('FN-%d-%04d', $year, $last + 1);
    }

    private function generateNextFeeNoteNumber(): string
    {
        $year = (int) date('Y');
        $last = $this->feeNoteLastIssued();
        $next = $last + 1;
        Storage::disk('local')->put(self::FEE_NOTE_SEQ_PATH, json_encode([
            'year' => $year,
            'last' => $next,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return sprintf('FN-%d-%04d', $year, $next);
    }

    private function feeNoteLastIssued(): int
    {
        $year = (int) date('Y');
        if (! Storage::disk('local')->exists(self::FEE_NOTE_SEQ_PATH)) {
            return 1000;
        }
        $data = json_decode(Storage::disk('local')->get(self::FEE_NOTE_SEQ_PATH), true);
        if (! is_array($data)) {
            return 1000;
        }
        if ((int) ($data['year'] ?? 0) !== $year) {
            return 1000;
        }

        return (int) ($data['last'] ?? 1000);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readFeeNoteIndex(): array
    {
        if (! Storage::disk('local')->exists(self::FEE_NOTE_INDEX_PATH)) {
            return [];
        }
        $decoded = json_decode(Storage::disk('local')->get(self::FEE_NOTE_INDEX_PATH), true);
        if (! is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach (array_values($decoded) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $row['is_draft'] = (bool) ($row['is_draft'] ?? false);
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readFeeNoteIndexSorted(): array
    {
        $rows = $this->readFeeNoteIndex();
        usort($rows, static function (array $a, array $b): int {
            return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
        });

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function writeFeeNoteIndex(array $rows): void
    {
        Storage::disk('local')->put(
            self::FEE_NOTE_INDEX_PATH,
            json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /** @return array<string, mixed>|null */
    private function findFeeNoteById(int $id): ?array
    {
        foreach ($this->readFeeNoteIndex() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function appendFeeNoteRecord(array $data): int
    {
        $data = AdminStoredSettings::feeNoteStripStoredRemittance($data);
        $rows = $this->readFeeNoteIndex();
        $nextId = $rows === [] ? 1 : max(array_map(static fn (array $r): int => (int) ($r['id'] ?? 0), $rows)) + 1;
        $rows[] = array_merge($data, ['id' => $nextId, 'is_draft' => false]);
        $this->writeFeeNoteIndex($rows);

        return $nextId;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function appendFeeNoteDraft(array $data): int
    {
        $data = AdminStoredSettings::feeNoteStripStoredRemittance($data);
        $data['number'] = '';
        $data['is_draft'] = true;
        $rows = $this->readFeeNoteIndex();
        $nextId = $rows === [] ? 1 : max(array_map(static fn (array $r): int => (int) ($r['id'] ?? 0), $rows)) + 1;
        $rows[] = array_merge($data, ['id' => $nextId, 'number' => '', 'is_draft' => true]);
        $this->writeFeeNoteIndex($rows);

        return $nextId;
    }

    /**
     * @param  array<string, mixed>  $row  Must include id
     */
    private function replaceFeeNoteRecord(array $row): void
    {
        $row = AdminStoredSettings::feeNoteStripStoredRemittance($row);
        $id = (int) ($row['id'] ?? 0);
        abort_unless($id > 0, 404);
        $rows = $this->readFeeNoteIndex();
        $found = false;
        foreach ($rows as $i => $existing) {
            if ((int) ($existing['id'] ?? 0) === $id) {
                $rows[$i] = $row;
                $found = true;
                break;
            }
        }
        abort_unless($found, 404);
        $this->writeFeeNoteIndex($rows);
    }

    /** @return array<string, mixed> */
    private function feeNoteValuesForDocument(int $id): array
    {
        $row = $this->findFeeNoteById($id);
        abort_unless($row, 404);
        $out = AdminStoredSettings::feeNoteStripStoredRemittance($row);
        unset($out['id'], $out['is_draft']);
        if (($row['is_draft'] ?? false) === true && trim((string) ($out['number'] ?? '')) === '') {
            $out['number'] = 'Draft';
        }

        $out = AdminStoredSettings::feeNoteFillRemittance($out);
        if (trim((string) ($out['our_ref'] ?? '')) === '') {
            $out['our_ref'] = FeeNoteReference::build(
                (int) ($out['service_id'] ?? 0),
                (string) ($out['client'] ?? ''),
                isset($out['issued_date']) ? (string) $out['issued_date'] : null
            );
        }

        return $out;
    }

    /** @return array<string, mixed> */
    private function feeNoteValuesForForm(int $id): array
    {
        $row = $this->findFeeNoteById($id);
        abort_unless($row, 404);
        $out = AdminStoredSettings::feeNoteStripStoredRemittance($row);
        unset($out['id'], $out['is_draft']);

        $out = $this->feeNotePlainTextFields($out);
        if (trim((string) ($out['our_ref'] ?? '')) === '') {
            $out['our_ref'] = FeeNoteReference::build(
                (int) ($out['service_id'] ?? 0),
                (string) ($out['client'] ?? ''),
                isset($out['issued_date']) ? (string) $out['issued_date'] : null
            );
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function feeNoteNormalizeInput(array $data): array
    {
        if (array_key_exists('service_id', $data)) {
            $data['service_id'] = (string) max(0, (int) $data['service_id']);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function feeNotePlainTextFields(array $data): array
    {
        foreach (['address', 'line_description', 'notes'] as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $data[$key] = DocumentPlainText::fromHtml((string) ($data[$key] ?? ''));
        }

        return $data;
    }

    private function peekNextPaymentId(): string
    {
        $year = (int) date('Y');
        $last = $this->paymentLastIssued();

        return sprintf('PM-%d-%04d', $year, $last + 1);
    }

    private function generateNextPaymentId(): string
    {
        $year = (int) date('Y');
        $last = $this->paymentLastIssued();
        $next = $last + 1;
        Storage::disk('local')->put(self::PAYMENT_SEQ_PATH, json_encode([
            'year' => $year,
            'last' => $next,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return sprintf('PM-%d-%04d', $year, $next);
    }

    private function paymentLastIssued(): int
    {
        $year = (int) date('Y');
        if (! Storage::disk('local')->exists(self::PAYMENT_SEQ_PATH)) {
            return 1000;
        }
        $data = json_decode(Storage::disk('local')->get(self::PAYMENT_SEQ_PATH), true);
        if (! is_array($data)) {
            return 1000;
        }
        if ((int) ($data['year'] ?? 0) !== $year) {
            return 1000;
        }

        return (int) ($data['last'] ?? 1000);
    }

    /**
     * DomPDF options: A4 is set via setPaper; @page in views defines margins.
     */
    private function pdfDompdfOptions(): array
    {
        return [
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
        ];
    }

    /**
     * Document shell CSS for DomPDF — mirrors preview colldett-document chrome.
     */
    private function documentChromeStylesheet(): string
    {
        $path = resource_path('css/document-chrome-pdf.css');
        if (! is_file($path)) {
            return '';
        }

        return (string) file_get_contents($path);
    }

    /**
     * Same source as preview (resources/css/invoice-body.css), embedded for DomPDF.
     */
    private function invoiceBodyStylesheet(): string
    {
        $path = resource_path('css/invoice-body.css');
        if (! is_file($path)) {
            return '';
        }

        return (string) file_get_contents($path);
    }

    private function publicImageDataUri(string $relativePath): ?string
    {
        $path = public_path($relativePath);
        if (! is_file($path)) {
            return null;
        }
        $mime = @mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }
}
