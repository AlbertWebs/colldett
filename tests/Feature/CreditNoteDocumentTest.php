<?php

namespace Tests\Feature;

use App\Support\CreditNoteLedger;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class CreditNoteDocumentTest extends TestCase
{
    public function test_credit_note_matches_fee_note_layout_and_strips_html(): void
    {
        $html = View::make('admin.partials.credit-note-document-content', [
            'values' => [
                'number' => 'CN-2026-1001',
                'fee_note_number' => 'FN-2026-1001',
                'fee_note_date' => '2026-03-12',
                'fee_note_amount' => '1000',
                'our_ref' => '1/001/2026',
                'your_ref' => '4523',
                'client' => 'MORANI LIMITED',
                'address' => '<p>P.O BOX 3146-10400</p><p>NYERI</p>',
                'issued_date' => '2026-04-02',
                'line_description' => '<p>Credit of professional fees previously billed.</p>',
                'amount' => '1000',
                'apply_vat' => '1',
                'vat_rate' => '0.16',
                'notes' => '<p>When replying please quote our reference.</p>',
            ],
        ])->render();

        $this->assertStringContainsString('Credit Note', $html);
        $this->assertStringContainsString('Against Fee Note', $html);
        $this->assertStringContainsString('FN-2026-1001', $html);
        $this->assertStringContainsString('Particulars of Credit', $html);
        $this->assertStringContainsString('Credit of professional fees previously billed.', $html);
        $this->assertStringContainsString('V.A.T', $html);
        $this->assertStringContainsString('Total Credit', $html);
        $this->assertStringContainsString('1,160.00', $html);
        $this->assertStringContainsString('not a request for payment', $html);
        $this->assertStringNotContainsString('Please direct remittance', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
    }

    public function test_credit_note_without_vat_hides_vat_row(): void
    {
        $html = View::make('admin.partials.credit-note-document-content', [
            'values' => [
                'number' => 'CN-2026-1002',
                'fee_note_number' => 'FN-2026-1002',
                'fee_note_amount' => '1000',
                'client' => 'MORANI LIMITED',
                'address' => 'NYERI',
                'issued_date' => '2026-04-02',
                'line_description' => 'Credit of professional fees',
                'amount' => '1000',
                'apply_vat' => '0',
                'vat_rate' => '0',
            ],
        ])->render();

        $this->assertStringContainsString('1,000.00', $html);
        $this->assertStringNotContainsString('V.A.T', $html);
        $this->assertStringNotContainsString('160.00', $html);
    }

    public function test_remaining_credit_excludes_other_notes(): void
    {
        $feeNote = ['id' => 1, 'number' => 'FN-2026-1001', 'amount' => '1000'];
        $credits = [
            ['id' => 10, 'fee_note_id' => 1, 'fee_note_number' => 'FN-2026-1001', 'amount' => '400'],
            ['id' => 11, 'fee_note_id' => 1, 'fee_note_number' => 'FN-2026-1001', 'amount' => '250'],
        ];

        $this->assertSame(350.0, CreditNoteLedger::remainingExVat($feeNote, $credits));
        $this->assertSame(600.0, CreditNoteLedger::remainingExVat($feeNote, $credits, 11));
    }
}
