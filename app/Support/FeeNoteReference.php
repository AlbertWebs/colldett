<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class FeeNoteReference
{
    /**
     * Office reference: {serviceId}/{clientAccountRef}/{year} e.g. 1/001/2026
     */
    public static function build(int $serviceId, string $company, ?string $issuedDate): string
    {
        if ($serviceId < 1) {
            return '';
        }

        $company = trim($company);
        if ($company === '') {
            return '';
        }

        $accountSeg = ClientDirectory::accountRefSegmentForCompany($company);
        $year = self::yearFrom($issuedDate);

        return $serviceId.'/'.$accountSeg.'/'.$year;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function apply(array $data): array
    {
        $serviceId = (int) ($data['service_id'] ?? 0);
        $company = trim((string) ($data['client'] ?? ''));

        if ($serviceId > 0 && $company !== '') {
            $data['our_ref'] = self::build(
                $serviceId,
                $company,
                isset($data['issued_date']) ? (string) $data['issued_date'] : null
            );
        }

        return $data;
    }

    public static function yearFrom(?string $issuedDate): int
    {
        $issuedDate = trim((string) $issuedDate);
        if ($issuedDate !== '') {
            try {
                return (int) Carbon::parse($issuedDate)->format('Y');
            } catch (\Throwable) {
                // fall through
            }
        }

        return (int) date('Y');
    }
}
