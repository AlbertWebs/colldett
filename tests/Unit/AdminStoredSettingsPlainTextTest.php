<?php

namespace Tests\Unit;

use App\Support\AdminStoredSettings;
use Tests\TestCase;

class AdminStoredSettingsPlainTextTest extends TestCase
{
    public function test_plain_text_lines_splits_quill_paragraphs(): void
    {
        $lines = AdminStoredSettings::plainTextLines(
            '<p>PAYBILL : 522533</p><p>ACCOUNT : 8080678</p>'
        );

        $this->assertSame([
            'PAYBILL : 522533',
            'ACCOUNT : 8080678',
        ], $lines);
    }

    public function test_plain_text_lines_ignores_blank_html(): void
    {
        $this->assertSame([], AdminStoredSettings::plainTextLines('<p><br></p>'));
        $this->assertSame([], AdminStoredSettings::plainTextLines(null));
    }
}
