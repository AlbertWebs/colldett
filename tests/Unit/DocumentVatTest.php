<?php

namespace Tests\Unit;

use App\Support\DocumentVat;
use Tests\TestCase;

class DocumentVatTest extends TestCase
{
    public function test_defaults_to_with_vat_when_flag_missing(): void
    {
        $this->assertTrue(DocumentVat::applies([]));
        $this->assertTrue(DocumentVat::applies(['amount' => '100']));
    }

    public function test_respects_apply_vat_flag(): void
    {
        $this->assertTrue(DocumentVat::applies(['apply_vat' => '1']));
        $this->assertFalse(DocumentVat::applies(['apply_vat' => '0']));
        $this->assertFalse(DocumentVat::applies(['apply_vat' => 'no']));
    }

    public function test_legacy_zero_vat_rate_means_without_vat(): void
    {
        $this->assertFalse(DocumentVat::applies(['vat_rate' => '0']));
        $this->assertTrue(DocumentVat::applies(['vat_rate' => '0.16']));
    }

    public function test_rate_is_zero_when_without_vat(): void
    {
        $this->assertSame(0.0, DocumentVat::rate(['apply_vat' => '0']));
    }

    public function test_normalize_input_syncs_fee_note_rate(): void
    {
        $with = DocumentVat::normalizeInput(['apply_vat' => '1'], true);
        $this->assertSame('1', $with['apply_vat']);
        $this->assertNotSame('0', $with['vat_rate']);

        $without = DocumentVat::normalizeInput(['apply_vat' => '0'], true);
        $this->assertSame('0', $without['apply_vat']);
        $this->assertSame('0', $without['vat_rate']);
    }
}
