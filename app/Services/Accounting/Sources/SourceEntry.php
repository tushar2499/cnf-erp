<?php

namespace App\Services\Accounting\Sources;

/**
 * One operational document (or document row) that can become a voucher.
 * Each part is an amount of the document that a transaction mapping turns into Dr / Cr lines.
 */
class SourceEntry
{
    /** @var list<array{component: string, match_key: string, amount: float, narration: ?string}> */
    public array $parts = [];

    public function __construct(
        public string $sourceType,
        public ?int $sourceId,
        public string $sourceRef,
        public string $date,
        public string $docNo,
        public ?int $branchId,
        public ?string $partyType,
        public ?int $partyId,
        public ?string $partyName,
        public string $narration,
        public ?string $updatedAt,
    ) {}

    public function addPart(string $component, float|string|null $amount, string|int|null $matchKey = '', ?string $narration = null): self
    {
        $amount = round((float) $amount, 2);
        if ($amount != 0.0) {
            $this->parts[] = [
                'component' => $component,
                'match_key' => (string) ($matchKey ?? ''),
                'amount'    => $amount,
                'narration' => $narration,
            ];
        }

        return $this;
    }

    public function total(): float
    {
        return round(array_sum(array_column($this->parts, 'amount')), 2);
    }
}
