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
            return self::normalizeParagraphBlocks($sanitized);
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
                $html[] = '<p class="capability-detail-para">'.e($chunk).'</p>';
            }
        }

        return implode("\n", $html);
    }

    /**
     * Ensure stored HTML paragraphs carry spacing class and line breaks between blocks.
     */
    public static function normalizeParagraphBlocks(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<\/p>\s*<p>/i', "</p>\n<p>", $html) ?? $html;
        $html = preg_replace('/<p>/i', '<p class="capability-detail-para">', $html) ?? $html;
        $html = preg_replace('/<p class="capability-detail-para" class="capability-detail-para">/i', '<p class="capability-detail-para">', $html) ?? $html;

        return trim($html);
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

    public static function containsMarkup(?string $value): bool
    {
        return $value !== null && str_contains($value, '<');
    }

    /**
     * @param  array<int, string>|string|null  $value
     */
    public static function joinField(array|string|null $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        return implode('', array_map('strval', $value));
    }

    /**
     * Normalize admin list fields that may be plain lines or Quill HTML blobs.
     *
     * @param  array<int, string>|string|null  $value
     * @return array<int, string>
     */
    public static function expandListItems(array|string|null $value): array
    {
        if ($value === null) {
            return [];
        }

        $entries = is_array($value) ? $value : [(string) $value];
        $items = [];

        foreach ($entries as $entry) {
            $entry = trim((string) $entry);
            if ($entry === '') {
                continue;
            }

            if (! self::containsMarkup($entry)) {
                foreach (preg_split("/\r\n|\r|\n/", $entry) ?: [] as $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        $items[] = $line;
                    }
                }

                continue;
            }

            $html = self::sanitize($entry);
            if ($html === '') {
                continue;
            }

            if (preg_match_all('#<li[^>]*>(.*?)</li>#is', $html, $matches) && $matches[1] !== []) {
                foreach ($matches[1] as $li) {
                    $piece = self::sanitize((string) $li);
                    if (trim(strip_tags($piece)) !== '') {
                        $items[] = $piece;
                    }
                }

                continue;
            }

            if (preg_match_all('#<p[^>]*>(.*?)</p>#is', $html, $paras) && count($paras[1]) > 1) {
                foreach ($paras[1] as $para) {
                    $para = trim((string) $para);
                    if ($para === '' || preg_match('/^\s*(?:<br\s*\/?>)?\s*$/i', $para)) {
                        continue;
                    }
                    if (trim(strip_tags($para)) !== '') {
                        $items[] = self::sanitize('<p>'.$para.'</p>');
                    }
                }

                continue;
            }

            $items[] = $html;
        }

        return $items;
    }

    public static function renderListItem(?string $item): string
    {
        if ($item === null || trim($item) === '') {
            return '';
        }

        return self::containsMarkup($item)
            ? self::sanitize($item)
            : e(trim($item));
    }

    /** Inline-safe list item output (unwraps a single paragraph wrapper). */
    public static function renderInlineListItem(?string $item): string
    {
        if ($item === null || trim($item) === '') {
            return '';
        }

        if (! self::containsMarkup($item)) {
            return e(trim($item));
        }

        $html = self::sanitize($item);
        if (preg_match('#^<p[^>]*>(.*)</p>$#is', trim($html), $match)) {
            $html = trim((string) $match[1]);
        }

        return $html;
    }

    /**
     * Split Quill HTML into titled blocks (e.g. core values: bold title + description).
     *
     * @return array<int, array{title: string, body: string}>
     */
    public static function expandTitledParagraphs(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $html = self::sanitize($value);
        if ($html === '') {
            return [];
        }

        if (! preg_match_all('#<p[^>]*>(.*?)</p>#is', $html, $paras) || $paras[1] === []) {
            $plain = DocumentPlainText::fromHtml($value);

            return $plain !== '' ? [['title' => '', 'body' => e($plain)]] : [];
        }

        $blocks = [];
        $current = null;

        foreach ($paras[1] as $para) {
            $para = trim((string) $para);
            if ($para === '' || preg_match('/^\s*(?:<br\s*\/?>)?\s*$/i', $para)) {
                continue;
            }

            $isTitle = (bool) preg_match('#^<(?:strong|b)[^>]*>(.+?)</(?:strong|b)>\s*$#is', $para, $titleMatch);

            if ($isTitle) {
                if ($current !== null) {
                    $blocks[] = $current;
                }
                $current = [
                    'title' => trim(strip_tags($titleMatch[1])),
                    'body' => '',
                ];

                continue;
            }

            $piece = self::sanitize('<p>'.$para.'</p>');

            if ($current === null) {
                $blocks[] = ['title' => '', 'body' => $piece];

                continue;
            }

            $current['body'] .= ($current['body'] !== '' ? "\n" : '').$piece;
        }

        if ($current !== null) {
            $blocks[] = $current;
        }

        return $blocks;
    }
}
