<?php

namespace App\Support;

use App\Models\CareerApplication;
use App\Models\Inquiry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class AdminActivityFeed
{
    /**
     * @return list<array{event: string, entity: string, user: string, occurred_at: Carbon, url: string|null}>
     */
    public static function recent(int $limit = 12): array
    {
        $items = array_merge(
            self::fromInquiries(),
            self::fromCareerApplications(),
            self::fromInvoices(),
            self::fromFeeNotes(),
            self::fromCreditNotes(),
            self::fromCases(),
        );

        usort($items, static fn (array $a, array $b): int => $b['occurred_at']->timestamp <=> $a['occurred_at']->timestamp);

        return array_slice($items, 0, max(1, $limit));
    }

    /**
     * @return list<array{event: string, entity: string, user: string, occurred_at: Carbon, url: string|null}>
     */
    private static function fromInquiries(): array
    {
        if (! Schema::hasTable('inquiries')) {
            return [];
        }

        return Inquiry::query()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'name', 'email', 'service_interest', 'created_at'])
            ->map(static function (Inquiry $inquiry): array {
                $name = trim($inquiry->name) ?: trim($inquiry->email);

                return [
                    'event' => 'Inbound inquiry',
                    'entity' => $name.' — '.trim((string) $inquiry->service_interest),
                    'user' => 'Website',
                    'occurred_at' => $inquiry->created_at ?? now(),
                    'url' => route('contact'),
                ];
            })
            ->all();
    }

    /**
     * @return list<array{event: string, entity: string, user: string, occurred_at: Carbon, url: string|null}>
     */
    private static function fromCareerApplications(): array
    {
        if (! Schema::hasTable('career_applications')) {
            return [];
        }

        return CareerApplication::query()
            ->with('career:id,title')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(static function (CareerApplication $application): array {
                $role = trim((string) ($application->career?->title ?? 'Open role'));

                return [
                    'event' => 'Career application',
                    'entity' => trim($application->name).' — '.$role,
                    'user' => 'Website',
                    'occurred_at' => $application->created_at ?? now(),
                    'url' => route('admin.career-applications.show', $application->id),
                ];
            })
            ->all();
    }

    /**
     * @return list<array{event: string, entity: string, user: string, occurred_at: Carbon, url: string|null}>
     */
    private static function fromInvoices(): array
    {
        $items = [];
        foreach (self::readJsonList('admin/billing_invoices.json') as $row) {
            if (! is_array($row)) {
                continue;
            }
            $number = trim((string) ($row['number'] ?? ''));
            if ($number === '') {
                continue;
            }
            $occurredAt = self::parseDate($row['issued_date'] ?? null);
            if ($occurredAt === null) {
                continue;
            }
            $client = trim((string) ($row['client'] ?? ''));
            $items[] = [
                'event' => 'Invoice issued',
                'entity' => $number.($client !== '' ? ' — '.$client : ''),
                'user' => 'Billing',
                'occurred_at' => $occurredAt,
                'url' => isset($row['id']) ? route('admin.billing.module.preview', ['invoices', (int) $row['id']]) : null,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{event: string, entity: string, user: string, occurred_at: Carbon, url: string|null}>
     */
    private static function fromFeeNotes(): array
    {
        $items = [];
        foreach (self::readJsonList('admin/billing_fee_notes.json') as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (($row['is_draft'] ?? false) === true) {
                continue;
            }
            $number = trim((string) ($row['number'] ?? ''));
            if ($number === '') {
                continue;
            }
            $occurredAt = self::parseDate($row['issued_date'] ?? null);
            if ($occurredAt === null) {
                continue;
            }
            $client = trim((string) ($row['client'] ?? ''));
            $items[] = [
                'event' => 'Fee note issued',
                'entity' => $number.($client !== '' ? ' — '.$client : ''),
                'user' => 'Billing',
                'occurred_at' => $occurredAt,
                'url' => isset($row['id']) ? route('admin.billing.module.preview', ['fee-notes', (int) $row['id']]) : null,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{event: string, entity: string, user: string, occurred_at: Carbon, url: string|null}>
     */
    private static function fromCreditNotes(): array
    {
        $items = [];
        foreach (self::readJsonList('admin/billing_credit_notes.json') as $row) {
            if (! is_array($row)) {
                continue;
            }
            $number = trim((string) ($row['number'] ?? ''));
            if ($number === '') {
                continue;
            }
            $occurredAt = self::parseDate($row['issued_date'] ?? null);
            if ($occurredAt === null) {
                continue;
            }
            $client = trim((string) ($row['client'] ?? ''));
            $against = trim((string) ($row['fee_note_number'] ?? ''));
            $items[] = [
                'event' => 'Credit note issued',
                'entity' => $number.($against !== '' ? ' vs '.$against : '').($client !== '' ? ' — '.$client : ''),
                'user' => 'Billing',
                'occurred_at' => $occurredAt,
                'url' => isset($row['id']) ? route('admin.billing.module.preview', ['credit-notes', (int) $row['id']]) : null,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{event: string, entity: string, user: string, occurred_at: Carbon, url: string|null}>
     */
    private static function fromCases(): array
    {
        $items = [];
        foreach (self::readJsonList('admin/cases.json') as $row) {
            if (! is_array($row)) {
                continue;
            }
            $caseNumber = trim((string) ($row['case_number'] ?? ''));
            $client = trim((string) ($row['client'] ?? ''));
            $officer = trim((string) ($row['officer'] ?? 'Case officer'));
            $entityBase = $caseNumber.($client !== '' ? ' — '.$client : '');

            $createdAt = self::parseDate($row['created_at'] ?? null);
            if ($createdAt !== null && $caseNumber !== '') {
                $items[] = [
                    'event' => 'Case opened',
                    'entity' => $entityBase,
                    'user' => $officer,
                    'occurred_at' => $createdAt,
                    'url' => isset($row['id']) ? route('admin.cases.show', (int) $row['id']) : null,
                ];
            }

            foreach ($row['notes'] ?? [] as $note) {
                if (! is_array($note)) {
                    continue;
                }
                $noteAt = self::parseDate($note['created_at'] ?? null);
                $body = trim((string) ($note['body'] ?? ''));
                if ($noteAt === null || $body === '') {
                    continue;
                }
                $items[] = [
                    'event' => 'Case note added',
                    'entity' => $entityBase,
                    'user' => $officer,
                    'occurred_at' => $noteAt,
                    'url' => isset($row['id']) ? route('admin.cases.show', (int) $row['id']) : null,
                ];
            }
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function readJsonList(string $path): array
    {
        if (! Storage::disk('local')->exists($path)) {
            return [];
        }

        $decoded = json_decode((string) Storage::disk('local')->get($path), true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    private static function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
