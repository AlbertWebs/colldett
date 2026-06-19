<?php

namespace App\Support;

/**
 * Converts pasted rich-text / HTML snippets into plain text for PDFs and print previews.
 */
final class DocumentPlainText
{
    public static function fromHtml(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $s = $value;
        $s = preg_replace('#<\s*br\s*/?>#i', "\n", $s) ?? $s;
        $s = preg_replace('#</\s*(p|div|li|tr|h[1-6])\s*>#i', "\n", $s) ?? $s;
        $s = strip_tags($s);
        $s = RichContentHtml::decodeEntities($s);
        $s = preg_replace("/[\t\x0B\f]/u", ' ', $s) ?? $s;
        $s = preg_replace("/\n{3,}/", "\n\n", $s) ?? $s;

        return trim($s);
    }
}
