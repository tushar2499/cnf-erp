<?php

namespace App\Services\Accounting\Sources;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

abstract class AccountingSource
{
    /** Registry key, e.g. "chevron.bill". */
    abstract public function key(): string;

    abstract public function label(): string;

    abstract public function companySlug(): string;

    /**
     * Amount parts this document produces. "match" names a MatchOptions list when the
     * part can have a specific rule per expense head, payment mode, bank account...
     *
     * @return array<string, array{label: string, match?: string}>
     */
    abstract public function components(): array;

    /**
     * @return Collection<int, SourceEntry>
     */
    abstract public function entries(CarbonInterface $from, CarbonInterface $to): Collection;

    public function voucherType(): string
    {
        return 'journal';
    }

    protected function entry(object $row, string $ref, string $date, string $docNo, ?int $branchId, ?string $partyType, ?int $partyId, ?string $partyName, string $narration, ?string $sourceId = null): SourceEntry
    {
        return new SourceEntry(
            sourceType: $this->key(),
            sourceId: (int) ($sourceId ?? $row->id),
            sourceRef: $this->key().':'.$ref,
            date: substr($date, 0, 10),
            docNo: $docNo,
            branchId: $branchId,
            partyType: $partyId ? $partyType : null,
            partyId: $partyId ?: null,
            partyName: $partyName,
            narration: $narration,
            updatedAt: $row->updated_at ?? null,
        );
    }

    protected static function range(CarbonInterface $from, CarbonInterface $to): array
    {
        return [$from->toDateString(), $to->toDateString()];
    }
}
