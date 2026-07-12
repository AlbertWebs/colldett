<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class BillingDocumentPlainTextTest extends TestCase
{
    public function test_invoice_document_does_not_show_html_tags(): void
    {
        $html = View::make('admin.partials.invoice-document-content', [
            'values' => [
                'number' => 'INV-2026-1002',
                'client' => 'Prime Foods Ltd',
                'issued_date' => '2026-04-01',
                'due_date' => '2026-04-15',
                'amount' => '250000',
                'line_description' => '<p>Debt recovery services — monthly portfolio support.</p>',
                'billing_address' => "<p>Prime Foods Ltd</p><p>Industrial Area, Nairobi</p>",
                'notes' => '<p>Thank you for your business.</p>',
            ],
        ])->render();

        $this->assertStringContainsString('Debt recovery services — monthly portfolio support.', $html);
        $this->assertStringContainsString('Thank you for your business.', $html);
        $this->assertStringContainsString('Prime Foods Ltd', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
        $this->assertStringNotContainsString('<p>Debt recovery', $html);
        $this->assertStringNotContainsString('<p>Thank you', $html);
    }

    public function test_quotation_document_does_not_show_html_tags(): void
    {
        $html = View::make('admin.partials.quotation-document-content', [
            'values' => [
                'number' => 'QTN-2026-1001',
                'client' => 'Apex Motors',
                'valid_until' => '2026-04-30',
                'amount' => '410000',
                'scope' => '<p>Debt tracing and legal demand support</p>',
            ],
        ])->render();

        $this->assertStringContainsString('Debt tracing and legal demand support', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
        $this->assertStringNotContainsString('<p>Debt tracing', $html);
    }

    public function test_demand_letter_does_not_show_html_tags(): void
    {
        $html = View::make('admin.partials.demand-letter-preview', [
            'values' => [
                'client' => 'Apex Motors',
                'case_ref' => 'CASE-004282',
                'amount' => '2100000',
                'deadline' => '2026-04-20',
                'subject' => '<p>Formal demand for payment</p>',
                'body' => '<p>Dear Sir/Madam,</p><p>Please settle the outstanding balance.</p>',
            ],
        ])->render();

        $this->assertStringContainsString('Formal demand for payment', $html);
        $this->assertStringContainsString('Dear Sir/Madam,', $html);
        $this->assertStringContainsString('Please settle the outstanding balance.', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
        $this->assertStringNotContainsString('<p>Dear Sir/Madam', $html);
        $this->assertStringNotContainsString('<p>Formal demand', $html);
    }

    public function test_fee_note_document_does_not_show_html_tags(): void
    {
        $html = View::make('admin.partials.fee-note-document-content', [
            'values' => [
                'number' => 'FN-2026-1001',
                'service_id' => '1',
                'our_ref' => '1/001/2026',
                'your_ref' => '4523',
                'client' => 'MORANI LIMITED',
                'address' => '<p>P.O BOX 3146-10400</p><p>NYERI</p>',
                'issued_date' => '2026-03-12',
                'payment_terms' => 'IMMEDIATE',
                'line_description' => '<p>Professional fees for debt collection.</p>',
                'amount' => '5321.60',
                'vat_rate' => '0.16',
                'notes' => '<p>When replying please quote our reference.</p>',
            ],
        ])->render();

        $this->assertStringContainsString('Professional fees for debt collection.', $html);
        $this->assertStringContainsString('When replying please quote our reference.', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
        $this->assertStringNotContainsString('<p>Professional fees', $html);
    }
}
