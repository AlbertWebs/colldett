<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class BillingDocumentVatTest extends TestCase
{
    public function test_invoice_with_vat_shows_vat_line_and_includes_tax_in_total(): void
    {
        $html = View::make('admin.partials.invoice-document-content', [
            'values' => [
                'number' => 'INV-2026-2001',
                'client' => 'Prime Foods Ltd',
                'issued_date' => '2026-04-01',
                'due_date' => '2026-04-15',
                'amount' => '100000',
                'apply_vat' => '1',
                'line_description' => 'Debt recovery services',
                'billing_address' => 'Prime Foods Ltd',
                'notes' => '',
            ],
        ])->render();

        $this->assertStringContainsString('Sub Total', $html);
        $this->assertMatchesRegularExpression('/VAT|V\.A\.T/i', $html);
        $this->assertStringContainsString('Ksh 16,000.00', $html);
        $this->assertStringContainsString('Ksh 116,000.00', $html);
    }

    public function test_invoice_without_vat_hides_vat_and_total_equals_amount(): void
    {
        $html = View::make('admin.partials.invoice-document-content', [
            'values' => [
                'number' => 'INV-2026-2002',
                'client' => 'Prime Foods Ltd',
                'issued_date' => '2026-04-01',
                'due_date' => '2026-04-15',
                'amount' => '100000',
                'apply_vat' => '0',
                'line_description' => 'Debt recovery services',
                'billing_address' => 'Prime Foods Ltd',
                'notes' => '',
            ],
        ])->render();

        $this->assertStringContainsString('Ksh 100,000.00', $html);
        $this->assertStringNotContainsString('Ksh 16,000.00', $html);
        $this->assertStringNotContainsString('Ksh 116,000.00', $html);
        $this->assertDoesNotMatchRegularExpression('/>\s*16\.00%\s*Kenyan\s*VAT\s*</i', $html);
    }

    public function test_quotation_without_vat_hides_vat_row(): void
    {
        $html = View::make('admin.partials.quotation-document-content', [
            'values' => [
                'number' => 'QTN-2026-2001',
                'client' => 'Apex Motors',
                'valid_until' => '2026-04-30',
                'amount' => '50000',
                'apply_vat' => '0',
                'scope' => 'Debt tracing',
            ],
        ])->render();

        $this->assertStringContainsString('Ksh 50,000.00', $html);
        $this->assertStringNotContainsString('Ksh 8,000.00', $html);
        $this->assertStringNotContainsString('Ksh 58,000.00', $html);
    }

    public function test_quotation_with_vat_includes_tax(): void
    {
        $html = View::make('admin.partials.quotation-document-content', [
            'values' => [
                'number' => 'QTN-2026-2002',
                'client' => 'Apex Motors',
                'valid_until' => '2026-04-30',
                'amount' => '50000',
                'apply_vat' => '1',
                'scope' => 'Debt tracing',
            ],
        ])->render();

        $this->assertStringContainsString('Ksh 8,000.00', $html);
        $this->assertStringContainsString('Ksh 58,000.00', $html);
    }

    public function test_fee_note_without_vat_hides_vat_row(): void
    {
        $html = View::make('admin.partials.fee-note-document-content', [
            'values' => [
                'number' => 'FN-2026-2001',
                'service_id' => '1',
                'our_ref' => '1/001/2026',
                'your_ref' => '4523',
                'client' => 'MORANI LIMITED',
                'address' => 'NYERI',
                'issued_date' => '2026-03-12',
                'payment_terms' => 'IMMEDIATE',
                'line_description' => 'Professional fees',
                'amount' => '1000',
                'apply_vat' => '0',
                'vat_rate' => '0',
                'notes' => '',
            ],
        ])->render();

        $this->assertStringContainsString('1,000.00', $html);
        $this->assertStringNotContainsString('V.A.T', $html);
        $this->assertStringNotContainsString('160.00', $html);
    }

    public function test_fee_note_with_vat_shows_vat_row(): void
    {
        $html = View::make('admin.partials.fee-note-document-content', [
            'values' => [
                'number' => 'FN-2026-2002',
                'service_id' => '1',
                'our_ref' => '1/001/2026',
                'your_ref' => '4523',
                'client' => 'MORANI LIMITED',
                'address' => 'NYERI',
                'issued_date' => '2026-03-12',
                'payment_terms' => 'IMMEDIATE',
                'line_description' => 'Professional fees',
                'amount' => '1000',
                'apply_vat' => '1',
                'vat_rate' => '0.16',
                'notes' => '',
            ],
        ])->render();

        $this->assertStringContainsString('V.A.T', $html);
        $this->assertStringContainsString('160.00', $html);
        $this->assertStringContainsString('1,160.00', $html);
    }
}
