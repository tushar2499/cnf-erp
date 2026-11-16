<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccAccount;
use App\Models\Accounting\AccTransactionMapping;
use App\Services\Accounting\Sources\SourceEntry;
use Illuminate\Support\Collection;

/**
 * Turns a source entry's amount parts into proposed voucher lines using the transaction mappings.
 * A rule for the exact match key (expense head, payment mode...) wins over the part's default rule.
 */
class MappingResolver
{
    /** @var Collection<string, AccTransactionMapping>|null */
    private ?Collection $mappings = null;

    /**
     * @return array{lines: list<array<string, mixed>>, unmapped: list<array{component: string, match_key: string, amount: float}>}
     */
    public function resolve(SourceEntry $entry): array
    {
        $lines = [];
        $unmapped = [];

        foreach ($entry->parts as $part) {
            $mapping = $this->find($entry->sourceType, $part['component'], $part['match_key']);
            if (! $mapping) {
                $unmapped[] = ['component' => $part['component'], 'match_key' => $part['match_key'], 'amount' => $part['amount']];

                continue;
            }

            $amount = $part['amount'];
            // A negative part (e.g. a deduction larger than the charge) swaps the sides.
            [$debitAccount, $creditAccount] = $amount >= 0
                ? [$mapping->debitAccount, $mapping->creditAccount]
                : [$mapping->creditAccount, $mapping->debitAccount];
            $amount = abs($amount);

            $this->addLine($lines, $debitAccount, 'debit', $amount, $entry);
            $this->addLine($lines, $creditAccount, 'credit', $amount, $entry);
        }

        return ['lines' => array_values($lines), 'unmapped' => $unmapped];
    }

    public function find(string $sourceType, string $component, string $matchKey): ?AccTransactionMapping
    {
        $mappings = $this->mappings();

        return $mappings->get("{$sourceType}|{$component}|{$matchKey}")
            ?? $mappings->get("{$sourceType}|{$component}|");
    }

    /**
     * @param  array<string, array<string, mixed>>  $lines
     */
    private function addLine(array &$lines, AccAccount $account, string $side, float $amount, SourceEntry $entry): void
    {
        $withParty = $account->party_type !== null && $entry->partyId;
        $key = $account->id.'|'.$side;

        if (! isset($lines[$key])) {
            $lines[$key] = [
                'account_id'   => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'debit'        => 0.0,
                'credit'       => 0.0,
                'party_type'   => $withParty ? $entry->partyType : null,
                'party_id'     => $withParty ? $entry->partyId : null,
                'party_name'   => $withParty ? $entry->partyName : null,
                'narration'    => null,
            ];
        }

        $lines[$key][$side] = round($lines[$key][$side] + $amount, 2);
    }

    /**
     * @return Collection<string, AccTransactionMapping>
     */
    private function mappings(): Collection
    {
        return $this->mappings ??= AccTransactionMapping::with(['debitAccount', 'creditAccount'])
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($m) => "{$m->source_type}|{$m->component}|{$m->match_key}");
    }
}
