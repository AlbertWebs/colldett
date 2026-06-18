<?php

namespace App\Support;

/**
 * Safe subset of HTML for public rich-text from admin editors.
 */
final class RichContentHtml
{
    private const ALLOWED = '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4><a>';

    public static function sanitize(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $html = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = strip_tags($html, self::ALLOWED);

        // Normalize empty paragraphs from rich editors.
        $html = preg_replace('/<p>\s*(?:<br\s*\/?>)?\s*<\/p>/i', '', $html) ?? $html;

        return trim($html);
    }

    public static function toParagraphHtml(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $sanitized = self::sanitize($value);
        if (str_contains($sanitized, '<p')) {
            return $sanitized;
        }

        $plain = DocumentPlainText::fromHtml($value);
        if ($plain === '') {
            return '';
        }

        $chunks = preg_split("/\r\n\r\n|\n\n+/", $plain) ?: [];
        if (count($chunks) <= 1) {
            $chunks = preg_split("/\r\n|\n/", $plain) ?: [];
        }

        $html = [];
        foreach ($chunks as $chunk) {
            $chunk = trim((string) $chunk);
            if ($chunk !== '') {
                $html[] = '<p>'.e($chunk).'</p>';
            }
        }

        return implode("\n", $html);
    }

    public static function hasVisibleContent(?string $value): bool
    {
        return trim(strip_tags(self::sanitize($value))) !== '';
    }

    public static function plainExcerpt(?string $value, int $limit = 220): string
    {
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags(self::sanitize($value))) ?? '');

        if ($plain === '') {
            return '';
        }

        return mb_strlen($plain) > $limit
            ? mb_substr($plain, 0, $limit - 1).'…'
            : $plain;
    }
}
