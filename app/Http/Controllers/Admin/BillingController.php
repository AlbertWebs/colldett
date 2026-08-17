<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStoredSettings;
use App\Support\ClientDirectory;
use App\Support\CreditNoteLedger;
use App\Support\DocumentPlainText;
use App\Support\DocumentVat;
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
            $values['apply_vat'] = '1';
            $presetClient = trim((string) $request->query('client', ''));
            if ($presetClient !== '') {
                $values['client'] = $presetClient;
            }
        }
        if ($module === 'quotations') {
            $values['number'] = $this->peekNextQuotationNumber();
            $values['apply_vat'] = '1';
        }
        if ($module === 'fee-notes') {
            $values = array_merge(
                AdminStoredSettings::feeNoteRemittanceDefaults(),
                [
                    'number' => $this->peekNextFeeNoteNumber(),
                    'issued_date' => date('Y-m-d'),
                    'apply_vat' => '1',
                    'vat_rate' => (string) DocumentVat::normalizeRate((float) (AdminStoredSettings::invoice()['vat_rate'] ?? 0.16)),
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
        if ($module === 'credit-notes') {
            $values['number'] = $this->peekNextCreditNoteNumber();
            $values['issued_date'] = date('Y-m-d');
            $presetFeeNoteId = (int) $request->query('fee_note_id', 0);
            if ($presetFeeNoteId > 0) {
                $values['fee_note_id'] = (string) $presetFeeNoteId;
            }
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
            'feeNoteOptions' => $module === 'credit-notes' ? $this->issuedFeeNoteOptionsForCreditNotes() : [],
        ]);
    }

    public function moduleIndex(string $module): View
    {
        $meta = $this->moduleMeta($module);

        $rows = match ($module) {
            'fee-notes' => $this->readFeeNoteIndexSorted(),
            'invoices' => $this->readInvoiceIndexSorted(),
            'credit-notes' => $this->readCreditNoteIndexSorted(),
            default => [$this->sampleValues($module, 1)],
        };

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

        $data = $this->plainTextFieldsForModule($module, $data);
        $data = $this->normalizeApplyVatForModule($module, $data);

        if ($module === 'invoices') {
            $data['number'] = $this->generateNextInvoiceNumber();
            $newId = $this->appendInvoiceRecord($data);

            return redirect()
                ->route('admin.billing.module.preview', [$module, $newId])
                ->with('status', $meta['singular'].' created successfully.');
        }

        if ($module === 'quotations') {
            $data['number'] = $this->generateNextQuotationNumber();

            return redirect()
                ->route('admin.billing.module.preview', [$module, 1])
                ->with('status', $meta['singular'].' created successfully.')
                ->with('preview_values', $data);
        }

        if ($module === 'fee-notes') {
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

        if ($module === 'credit-notes') {
            $prepared = $this->prepareCreditNoteFromFeeNote($data);
            if ($prepared instanceof RedirectResponse) {
                return $prepared;
            }
            $prepared['number'] = $this->generateNextCreditNoteNumber();
            $newId = $this->appendCreditNoteRecord($prepared);

            return redirect()
                ->route('admin.billing.module.preview', [$module, $newId])
                ->with('status', $meta['singular'].' issued successfully.');
        }

        return redirect()
            ->route('admin.billing.module.preview', [$module, 1])
            ->with('status', $meta['singular'].' created successfully.')
            ->with('preview_values', $data);
    }

    public function preview(Request $request, string $module, int $id): View
    {
        $meta = $this->moduleMeta($module);
        $values = $this->documentValues($request, $module, $id);

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
        $values = $this->documentValues($request, $module, $id);

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
        $values = $this->documentValues($request, $module, $id);

        $docRef = '#'.($values['number'] ?? ($values['payment_id'] ?? ('REC-'.$id)));
        $docTitle = $module === 'invoices'
            ? 'Invoice'
            : ($module === 'demand' ? 'Demand Letter' : ($module === 'fee-notes' ? 'Fee Note' : ($module === 'credit-notes' ? 'Credit Note' : $meta['singular'].' preview')));
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
        } elseif ($module === 'credit-notes') {
            $pdf = Pdf::loadView('admin.billing-credit-note-pdf', [
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

        $values = match ($module) {
            'fee-notes' => $this->feeNoteValuesForForm($id),
            'invoices' => $this->invoiceValuesForForm($id),
            'credit-notes' => $this->creditNoteValuesForForm($id),
            default => $this->sampleValues($module, $id),
        };

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
            'feeNoteOptions' => $module === 'credit-notes' ? $this->issuedFeeNoteOptionsForCreditNotes($id) : [],
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
            $data = $this->plainTextFieldsForModule($module, $data);
            $data = $this->normalizeApplyVatForModule($module, $data);
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

        if ($module === 'invoices') {
            $existing = $this->findInvoiceById($id);
            abort_unless($existing, 404);
            $data = $request->validate($this->rules($module));
            $data = $this->plainTextFieldsForModule($module, $data);
            $data = $this->normalizeApplyVatForModule($module, $data);
            $merged = array_merge($existing, $data, [
                'id' => $id,
                'number' => (string) ($existing['number'] ?? ''),
            ]);
            $this->replaceInvoiceRecord($merged);

            return redirect()
                ->route('admin.billing.module.edit', [$module, $id])
                ->with('status', $meta['singular'].' updated successfully.');
        }

        if ($module === 'credit-notes') {
            $existing = $this->findCreditNoteById($id);
            abort_unless($existing, 404);
            $data = $request->validate($this->rules($module));
            $data = $this->plainTextFieldsForModule($module, $data);
            $data['fee_note_id'] = (string) ($existing['fee_note_id'] ?? '');
            $prepared = $this->prepareCreditNoteFromFeeNote($data, $id);
            if ($prepared instanceof RedirectResponse) {
                return $prepared;
            }
            $merged = array_merge($existing, $prepared, [
                'id' => $id,
                'number' => (string) ($existing['number'] ?? ''),
            ]);
            $this->replaceCreditNoteRecord($merged);

            return redirect()
                ->route('admin.billing.module.edit', [$module, $id])
                ->with('status', $meta['singular'].' updated successfully.');
        }

        $data = $request->validate($this->rules($module));
        $data = $this->plainTextFieldsForModule($module, $data);
        $data = $this->normalizeApplyVatForModule($module, $data);

        return redirect()
            ->route('admin.billing.module.edit', [$module, $id])
            ->with('status', $meta['singular'].' updated successfully.')
            ->with('preview_values', $data);
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
                    ['name' => 'apply_vat', 'label' => 'VAT', 'type' => 'select', 'options' => [
                        '1' => 'With VAT (16%)',
                        '0' => 'Without VAT',
                    ]],
                    ['name' => 'line_description', 'label' => 'Line item description', 'type' => 'textarea'],
                    ['name' => 'billing_address', 'label' => 'Billing address (Invoiced To)', 'type' => 'textarea'],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
                'list_fields' => [
                    ['name' => 'number', 'label' => 'Number', 'truncate' => true],
                    ['name' => 'client', 'label' => 'Client', 'truncate' => true],
                    ['name' => 'issued_date', 'label' => 'Issued'],
                    ['name' => 'due_date', 'label' => 'Due'],
                    ['name' => 'amount', 'label' => 'Amount'],
                    ['name' => 'apply_vat', 'label' => 'VAT'],
                    ['name' => 'line_description', 'label' => 'Description', 'truncate' => true],
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
                    ['name' => 'apply_vat', 'label' => 'VAT', 'type' => 'select', 'options' => [
                        '1' => 'With VAT (16%)',
                        '0' => 'Without VAT',
                    ]],
                    ['name' => 'scope', 'label' => 'Scope', 'type' => 'textarea'],
                ],
                'list_fields' => [
                    ['name' => 'number', 'label' => 'Quotation Number'],
                    ['name' => 'client', 'label' => 'Client', 'truncate' => true],
                    ['name' => 'valid_until', 'label' => 'Valid Until'],
                    ['name' => 'amount', 'label' => 'Amount'],
                    ['name' => 'apply_vat', 'label' => 'VAT'],
                    ['name' => 'scope', 'label' => 'Scope', 'truncate' => true],
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
                    ['name' => 'apply_vat', 'label' => 'VAT', 'type' => 'select', 'options' => [
                        '1' => 'With VAT (16%)',
                        '0' => 'Without VAT',
                    ]],
                    ['name' => 'notes', 'label' => 'Additional Notes', 'type' => 'textarea'],
                ],
                'list_fields' => [
                    ['name' => '__fee_note_status', 'label' => 'Status'],
                    ['name' => 'number', 'label' => 'Number', 'truncate' => true],
                    ['name' => 'client', 'label' => 'Client', 'truncate' => true],
                    ['name' => 'issued_date', 'label' => 'Issue date'],
                    ['name' => 'amount', 'label' => 'Fee (ex VAT)'],
                    ['name' => 'apply_vat', 'label' => 'VAT'],
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
            if (($field['type'] ?? 'text') === 'select' && $field['name'] === 'apply_vat') {
                $rules[$field['name']] = ['nullable', 'in:0,1'];
                continue;
            }
            $max = ($module === 'demand' && $field['name'] === 'body') ? 8000 : 2000;
            $rules[$field['name']] = ['nullable', 'string', 'max:'.$max];
        }
        if ($module === 'fee-notes') {
            $rules['service_id'] = ['nullable', 'integer', 'min:1'];
        }
        if ($module === 'credit-notes') {
            $rules['fee_note_id'] = ['required', 'integer', 'min:1'];
            $rules['amount'] = ['required', 'string', 'max:50'];
            $rules['line_description'] = ['required', 'string', 'max:4000'];
            $rules['issued_date'] = ['required', 'date'];
            $rules['number'] = ['nullable', 'string', 'max:50'];
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
                'apply_vat' => '1',
                'line_description' => 'Debt recovery services — monthly portfolio support and case reporting.',
                'billing_address' => "Prime Foods Ltd\nATTN: Accounts Payable\nIndustrial Area, Nairobi\nNairobi, Kenya\n00100",
                'notes' => 'Thank you for your business.',
            ],
            'quotations' => [
                'number' => 'QTN-2026-1001',
                'client' => 'Apex Motors',
                'valid_until' => '2026-04-30',
                'amount' => '410000',
                'apply_vat' => '1',
                'scope' => 'Debt tracing and legal demand support',
            ],
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
                'apply_vat' => '1',
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
            'credit-notes' => [
                'number' => 'CN-2026-1001',
                'fee_note_id' => '1',
                'fee_note_number' => 'FN-2026-1001',
                'fee_note_date' => '2026-03-12',
                'fee_note_amount' => '5321.60',
                'our_ref' => '1/001/2026',
                'your_ref' => '4523',
                'client' => 'MORANI LIMITED',
                'address' => "P.O BOX 3146-10400\nNYERI",
                'issued_date' => '2026-04-02',
                'line_description' => 'Credit of professional fees previously billed in error / overcharge.',
                'amount' => '5321.60',
                'apply_vat' => '1',
                'vat_rate' => '0.16',
                'notes' => 'When replying please quote our reference and this credit note number.',
            ],
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

    private const CREDIT_NOTE_SEQ_PATH = CreditNoteLedger::SEQ_PATH;

    private const CREDIT_NOTE_INDEX_PATH = CreditNoteLedger::INDEX_PATH;

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
    private function appendInvoiceRecord(array $data): int
    {
        $num = trim((string) ($data['number'] ?? ''));
        abort_unless($num !== '', 422);
        $rows = $this->readInvoiceIndex();
        $rows = array_values(array_filter($rows, static fn ($r): bool => is_array($r) && (string) ($r['number'] ?? '') !== $num));
        $nextId = $rows === [] ? 1 : max(array_map(static fn (array $r): int => (int) ($r['id'] ?? 0), $rows)) + 1;
        $rows[] = array_merge($data, ['id' => $nextId]);
        $this->writeInvoiceIndex($rows);

        return $nextId;
    }

    private function writeInvoiceIndex(array $rows): void
    {
        Storage::disk('local')->put(
            self::INVOICE_INDEX_PATH,
            json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /** @return list<array<string, mixed>> */
    private function readInvoiceIndexSorted(): array
    {
        $rows = $this->readInvoiceIndex();
        usort($rows, static fn (array $a, array $b): int => ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0)));

        return array_map(
            fn (array $row): array => $this->plainTextFieldsForModule('invoices', $row),
            $rows
        );
    }

    /** @return array<string, mixed>|null */
    private function findInvoiceById(int $id): ?array
    {
        foreach ($this->readInvoiceIndex() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function invoiceValuesForDocument(int $id): array
    {
        $row = $this->findInvoiceById($id);
        abort_unless($row, 404);
        $out = $row;
        unset($out['id']);

        return $this->plainTextFieldsForModule('invoices', $out);
    }

    /**
     * @param  array<string, mixed>  $row  Must include id
     */
    private function replaceInvoiceRecord(array $row): void
    {
        $id = (int) ($row['id'] ?? 0);
        abort_unless($id > 0, 404);
        $rows = $this->readInvoiceIndex();
        $found = false;
        foreach ($rows as $i => $existing) {
            if ((int) ($existing['id'] ?? 0) === $id) {
                $rows[$i] = $row;
                $found = true;
                break;
            }
        }
        abort_unless($found, 404);
        $this->writeInvoiceIndex($rows);
    }

    /** @return array<string, mixed> */
    private function invoiceValuesForForm(int $id): array
    {
        return DocumentVat::forForm($this->invoiceValuesForDocument($id));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readInvoiceIndex(): array
    {
        if (! Storage::disk('local')->exists(self::INVOICE_INDEX_PATH)) {
            $defaults = $this->defaultInvoiceIndex();
            $this->writeInvoiceIndex($defaults);

            return $defaults;
        }
        $decoded = json_decode(Storage::disk('local')->get(self::INVOICE_INDEX_PATH), true);
        if (! is_array($decoded) || $decoded === []) {
            return $this->defaultInvoiceIndex();
        }

        return $this->ensureInvoiceIds($decoded);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function ensureInvoiceIds(array $rows): array
    {
        $max = 0;
        foreach ($rows as $row) {
            if (is_array($row)) {
                $max = max($max, (int) ($row['id'] ?? 0));
            }
        }
        $changed = false;
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if ((int) ($row['id'] ?? 0) < 1) {
                $max++;
                $row['id'] = $max;
                $changed = true;
            }
            $out[] = $row;
        }
        if ($changed) {
            $this->writeInvoiceIndex($out);
        }

        return $out;
    }

    /**
     * @return list<array<string, string>>
     */
    private function defaultInvoiceIndex(): array
    {
        return [
            array_merge($this->sampleValues('invoices', 1), [
                'id' => 1,
                'number' => 'INV-2026-1002',
                'client' => 'Prime Foods Ltd',
                'amount' => '250000',
                'issued_date' => '2026-04-01',
            ]),
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

        return $this->plainTextFieldsForModule('fee-notes', $out);
    }

    /** @return array<string, mixed> */
    private function feeNoteValuesForForm(int $id): array
    {
        $row = $this->findFeeNoteById($id);
        abort_unless($row, 404);
        $out = AdminStoredSettings::feeNoteStripStoredRemittance($row);
        unset($out['id'], $out['is_draft']);

        $out = $this->plainTextFieldsForModule('fee-notes', $out);
        if (trim((string) ($out['our_ref'] ?? '')) === '') {
            $out['our_ref'] = FeeNoteReference::build(
                (int) ($out['service_id'] ?? 0),
                (string) ($out['client'] ?? ''),
                isset($out['issued_date']) ? (string) $out['issued_date'] : null
            );
        }

        return DocumentVat::forForm($out);
    }

    /**
     * Issued fee notes available to credit, with remaining professional fee (ex VAT).
     *
     * @return list<array<string, mixed>>
     */
    private function issuedFeeNoteOptionsForCreditNotes(?int $exceptCreditId = null): array
    {
        $credits = $this->readCreditNoteIndex();
        $currency = AdminStoredSettings::invoice()['currency'] ?? 'KES';
        $out = [];
        foreach ($this->readFeeNoteIndex() as $row) {
            if (! is_array($row) || (bool) ($row['is_draft'] ?? false)) {
                continue;
            }
            $number = trim((string) ($row['number'] ?? ''));
            if ($number === '') {
                continue;
            }
            $remaining = CreditNoteLedger::remainingExVat($row, $credits, $exceptCreditId);
            $client = trim((string) ($row['client'] ?? ''));
            $out[] = [
                'id' => (int) ($row['id'] ?? 0),
                'number' => $number,
                'client' => $client,
                'remaining' => $remaining,
                'apply_vat' => DocumentVat::applies($row) ? '1' : '0',
                'label' => $number
                    .($client !== '' ? ' — '.$client : '')
                    .' (remaining '.$currency.' '.number_format($remaining, 2, '.', ',').')',
            ];
        }
        usort($out, static fn (array $a, array $b): int => strcmp((string) $b['number'], (string) $a['number']));

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|\Illuminate\Http\RedirectResponse
     */
    private function prepareCreditNoteFromFeeNote(array $data, ?int $exceptCreditId = null): array|RedirectResponse
    {
        $feeNoteId = (int) ($data['fee_note_id'] ?? 0);
        $feeNote = $this->findFeeNoteById($feeNoteId);
        if ($feeNote === null || (bool) ($feeNote['is_draft'] ?? false) || trim((string) ($feeNote['number'] ?? '')) === '') {
            return back()->withErrors(['fee_note_id' => 'Select an issued fee note.'])->withInput();
        }

        $snapshot = CreditNoteLedger::snapshotFromFeeNote($feeNote);
        $merged = array_merge($data, $snapshot);
        $amount = CreditNoteLedger::parseAmount($merged['amount'] ?? 0);
        if ($amount <= 0) {
            return back()->withErrors(['amount' => 'Enter a credit amount greater than zero.'])->withInput();
        }
        $remaining = CreditNoteLedger::remainingExVat($feeNote, $this->readCreditNoteIndex(), $exceptCreditId);
        if ($amount - $remaining > 0.009) {
            return back()
                ->withErrors(['amount' => 'Credit cannot exceed the remaining fee-note amount of '.number_format($remaining, 2, '.', ',').'.'])
                ->withInput();
        }
        $merged['amount'] = (string) $amount;
        if (trim((string) ($merged['our_ref'] ?? '')) === '') {
            $merged['our_ref'] = FeeNoteReference::build(
                (int) ($merged['service_id'] ?? 0),
                (string) ($merged['client'] ?? ''),
                isset($feeNote['issued_date']) ? (string) $feeNote['issued_date'] : null
            );
        }

        return $this->plainTextFieldsForModule('credit-notes', $merged);
    }

    private function peekNextCreditNoteNumber(): string
    {
        $year = (int) date('Y');
        $last = $this->creditNoteLastIssued();

        return sprintf('CN-%d-%04d', $year, $last + 1);
    }

    private function generateNextCreditNoteNumber(): string
    {
        $year = (int) date('Y');
        $last = $this->creditNoteLastIssued();
        $next = $last + 1;
        Storage::disk('local')->put(self::CREDIT_NOTE_SEQ_PATH, json_encode([
            'year' => $year,
            'last' => $next,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return sprintf('CN-%d-%04d', $year, $next);
    }

    private function creditNoteLastIssued(): int
    {
        $year = (int) date('Y');
        if (! Storage::disk('local')->exists(self::CREDIT_NOTE_SEQ_PATH)) {
            return 1000;
        }
        $data = json_decode((string) Storage::disk('local')->get(self::CREDIT_NOTE_SEQ_PATH), true);
        if (! is_array($data) || (int) ($data['year'] ?? 0) !== $year) {
            return 1000;
        }

        return (int) ($data['last'] ?? 1000);
    }

    /** @return list<array<string, mixed>> */
    private function readCreditNoteIndex(): array
    {
        if (! Storage::disk('local')->exists(self::CREDIT_NOTE_INDEX_PATH)) {
            return [];
        }
        $decoded = json_decode((string) Storage::disk('local')->get(self::CREDIT_NOTE_INDEX_PATH), true);
        if (! is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach (array_values($decoded) as $row) {
            if (is_array($row)) {
                $out[] = $row;
            }
        }

        return $out;
    }

    /** @return list<array<string, mixed>> */
    private function readCreditNoteIndexSorted(): array
    {
        $rows = $this->readCreditNoteIndex();
        usort($rows, static fn (array $a, array $b): int => ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0)));

        return array_map(
            fn (array $row): array => $this->plainTextFieldsForModule('credit-notes', $row),
            $rows
        );
    }

    private function writeCreditNoteIndex(array $rows): void
    {
        Storage::disk('local')->put(
            self::CREDIT_NOTE_INDEX_PATH,
            json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /** @return array<string, mixed>|null */
    private function findCreditNoteById(int $id): ?array
    {
        foreach ($this->readCreditNoteIndex() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /** @param  array<string, mixed>  $data */
    private function appendCreditNoteRecord(array $data): int
    {
        $rows = $this->readCreditNoteIndex();
        $nextId = $rows === [] ? 1 : max(array_map(static fn (array $r): int => (int) ($r['id'] ?? 0), $rows)) + 1;
        $rows[] = array_merge($data, ['id' => $nextId]);
        $this->writeCreditNoteIndex($rows);

        return $nextId;
    }

    /** @param  array<string, mixed>  $row */
    private function replaceCreditNoteRecord(array $row): void
    {
        $id = (int) ($row['id'] ?? 0);
        abort_unless($id > 0, 404);
        $rows = $this->readCreditNoteIndex();
        $found = false;
        foreach ($rows as $i => $existing) {
            if ((int) ($existing['id'] ?? 0) === $id) {
                $rows[$i] = $row;
                $found = true;
                break;
            }
        }
        abort_unless($found, 404);
        $this->writeCreditNoteIndex($rows);
    }

    /** @return array<string, mixed> */
    private function creditNoteValuesForDocument(int $id): array
    {
        $row = $this->findCreditNoteById($id);
        abort_unless($row, 404);
        $out = $row;
        unset($out['id']);
        $out = $this->plainTextFieldsForModule('credit-notes', $out);
        $feeNote = $this->findFeeNoteById((int) ($row['fee_note_id'] ?? 0));
        if (is_array($feeNote)) {
            $remainingEx = CreditNoteLedger::remainingExVat($feeNote, $this->readCreditNoteIndex());
            $out['balance_remaining_total'] = CreditNoteLedger::totals(array_merge($out, ['amount' => $remainingEx]))['total'];
        }

        return $out;
    }

    /** @return array<string, mixed> */
    private function creditNoteValuesForForm(int $id): array
    {
        $row = $this->findCreditNoteById($id);
        abort_unless($row, 404);
        $out = $row;
        unset($out['id']);

        return DocumentVat::forForm($this->plainTextFieldsForModule('credit-notes', $out));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeApplyVatForModule(string $module, array $data): array
    {
        if (! in_array($module, ['invoices', 'quotations', 'fee-notes', 'credit-notes'], true)) {
            return $data;
        }

        return DocumentVat::normalizeInput($data, in_array($module, ['fee-notes', 'credit-notes'], true));
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
     * Resolve printable document values and strip any Quill/HTML leftovers from textarea fields.
     *
     * @return array<string, mixed>
     */
    private function documentValues(Request $request, string $module, int $id): array
    {
        if ($module === 'fee-notes') {
            return $this->feeNoteValuesForDocument($id);
        }
        if ($module === 'invoices') {
            return $this->invoiceValuesForDocument($id);
        }
        if ($module === 'credit-notes') {
            return $this->creditNoteValuesForDocument($id);
        }

        $values = $request->session()->get('preview_values');
        if (! is_array($values) || empty($values)) {
            $values = $this->sampleValues($module, $id);
        }

        return $this->plainTextFieldsForModule($module, $values);
    }

    /**
     * Convert textarea fields from Quill/HTML to plain text for storage and print.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function plainTextFieldsForModule(string $module, array $data): array
    {
        foreach ($this->moduleMeta($module)['fields'] as $field) {
            if (($field['type'] ?? 'text') !== 'textarea') {
                continue;
            }
            $key = $field['name'];
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
