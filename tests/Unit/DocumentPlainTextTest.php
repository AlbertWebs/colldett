<?php

namespace Tests\Unit;

use App\Support\DocumentPlainText;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DocumentPlainTextTest extends TestCase
{
    public function test_strips_quill_paragraph_tags(): void
    {
        $this->assertSame(
            'Professional services for debt recovery.',
            DocumentPlainText::fromHtml('<p>Professional services for debt recovery.</p>')
        );
    }

    public function test_preserves_paragraph_breaks_as_newlines(): void
    {
        $html = '<p>Dear Sir/Madam,</p><p>Please settle the outstanding balance.</p>';

        $this->assertSame(
            "Dear Sir/Madam,\nPlease settle the outstanding balance.",
            DocumentPlainText::fromHtml($html)
        );
    }

    public function test_converts_br_and_decodes_entities(): void
    {
        $html = '<p>Line one<br>Line two &amp; more</p>';

        $this->assertSame(
            "Line one\nLine two & more",
            DocumentPlainText::fromHtml($html)
        );
    }

    public function test_leaves_plain_text_unchanged(): void
    {
        $plain = "Prime Foods Ltd\nATTN: Accounts Payable\nNairobi";

        $this->assertSame($plain, DocumentPlainText::fromHtml($plain));
    }

    public function test_returns_empty_for_null_or_blank(): void
    {
        $this->assertSame('', DocumentPlainText::fromHtml(null));
        $this->assertSame('', DocumentPlainText::fromHtml(''));
        $this->assertSame('', DocumentPlainText::fromHtml('<p><br></p>'));
    }

    #[DataProvider('quillSamples')]
    public function test_never_leaves_angle_bracket_tags(string $html): void
    {
        $plain = DocumentPlainText::fromHtml($html);

        $this->assertStringNotContainsString('<p>', $plain);
        $this->assertStringNotContainsString('</p>', $plain);
        $this->assertStringNotContainsString('<br>', $plain);
        $this->assertStringNotContainsString('<div>', $plain);
    }

    /** @return array<string, array{0: string}> */
    public static function quillSamples(): array
    {
        return [
            'invoice notes' => ['<p>Thank you for your business.</p>'],
            'billing address' => ["<p>Prime Foods Ltd</p><p>Industrial Area</p>"],
            'demand body' => ['<p>Dear Sir/Madam,</p><p><br></p><p>We act on behalf of our client.</p>'],
            'quotation scope' => ['<p>Debt tracing and legal demand support</p>'],
            'escaped entities' => ['&lt;p&gt;Already escaped&lt;/p&gt;'],
        ];
    }
}
