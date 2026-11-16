<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccVoucher;
use App\Models\Company;
use App\Services\Accounting\Sources\AccountingSource;
use App\Services\Accounting\Sources\MatchOptions;
use App\Services\Accounting\Sources\SourceEntry;
use App\Services\Accounting\Sources\SourceRegistry;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Lists a company's operational documents for a period with the voucher lines the mappings propose,
 * and turns them into auto vouchers.
 */
class PostingService
{
    public function __construct(
        private SourceRegistry $registry,
        private MappingResolver $resolver,
        private VoucherService $vouchers,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function entries(Company $company, CarbonInterface $from, CarbonInterface $to, ?string $sourceKey = null): array
    {
        $rows = [];
        foreach ($this->sources($company, $sourceKey) as $source) {
            $entries = $source->entries($from, $to);
            $posted = AccVoucher::where('company_id', $company->id)
                ->whereIn('source_ref', $entries->pluck('sourceRef'))
                ->posted()
                ->get(['id', 'voucher_no', 'source_ref', 'source_updated_at'])
                ->keyBy('source_ref');

            foreach ($entries as $entry) {
                $rows[] = $this->describe($source, $entry, $posted[$entry->sourceRef] ?? null);
            }
        }

        usort($rows, fn ($a, $b) => [$a['date'], $a['doc_no']] <=> [$b['date'], $b['doc_no']]);

        return $rows;
    }

    /**
     * Post one document. Without $lines the mapping proposal is used.
     *
     * @param  list<array<string, mixed>>|null  $lines
     */
    public function post(Company $company, CarbonInterface $from, CarbonInterface $to, string $sourceKey, string $ref, ?array $lines, ?string $narration, ?int $userId): AccVoucher
    {
        $source = $this->sourceFor($company, $sourceKey);
        $entry = $source->entries($from, $to)->firstWhere('sourceRef', $ref);
        if (! $entry) {
            throw ValidationException::withMessages(['ref' => 'Document not found in the selected period. Reload the list.']);
        }

        $existing = AccVoucher::where('company_id', $company->id)->where('source_ref', $ref)->posted()->first();
        if ($existing && ! $this->isChanged($entry, $existing)) {
            throw ValidationException::withMessages(['ref' => "{$entry->docNo} is already posted as {$existing->voucher_no}."]);
        }

        if ($lines === null) {
            $resolved = $this->resolver->resolve($entry);
            if ($resolved['unmapped']) {
                throw ValidationException::withMessages(['ref' => "{$entry->docNo}: set up transaction mapping first."]);
            }
            $lines = $resolved['lines'];
        }

        $this->vouchers->validateLines($lines);

        return DB::transaction(function () use ($existing, $company, $entry, $source, $narration, $lines, $userId) {
            if ($existing) {
                $this->vouchers->cancel($existing, 'Re-posted after the source document changed.', $userId);
            }

            return $this->createFromEntry($company, $entry, $source, $narration, $lines, $userId);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function createFromEntry(Company $company, SourceEntry $entry, AccountingSource $source, ?string $narration, array $lines, ?int $userId): AccVoucher
    {
        return $this->vouchers->create([
            'company_id'        => $company->id,
            'branch_id'         => $entry->branchId,
            'voucher_type'      => $source->voucherType(),
            'voucher_date'      => $entry->date,
            'narration'         => $narration ?: $entry->narration,
            'source_type'       => $entry->sourceType,
            'source_id'         => $entry->sourceId,
            'source_ref'        => $entry->sourceRef,
            'source_doc_no'     => $entry->docNo,
            'source_updated_at' => $entry->updatedAt,
            'is_auto'           => true,
            'lines'             => $lines,
        ], $userId);
    }

    /**
     * Post many documents with their mapping proposal; documents that are not ready are skipped.
     *
     * @param  list<array{source_type: string, ref: string}>  $items
     * @return array{posted: int, skipped: list<string>}
     */
    public function postMany(Company $company, CarbonInterface $from, CarbonInterface $to, array $items, ?int $userId): array
    {
        $posted = 0;
        $skipped = [];
        foreach ($items as $item) {
            try {
                $this->post($company, $from, $to, $item['source_type'], $item['ref'], null, null, $userId);
                $posted++;
            } catch (ValidationException $e) {
                $skipped[] = collect($e->errors())->flatten()->first();
            }
        }

        return ['posted' => $posted, 'skipped' => $skipped];
    }

    /**
     * @return array<string, mixed>
     */
    private function describe(AccountingSource $source, SourceEntry $entry, ?AccVoucher $voucher): array
    {
        $resolved = $this->resolver->resolve($entry);
        $components = $source->components();

        $status = match (true) {
            $voucher && $this->isChanged($entry, $voucher)       => 'changed',
            $voucher !== null                                    => 'posted',
            $resolved['unmapped'] !== [] || $entry->parts === [] => 'unmapped',
            default                                              => 'ready',
        };

        return [
            'source_type'  => $entry->sourceType,
            'source_label' => $source->label(),
            'ref'          => $entry->sourceRef,
            'date'         => $entry->date,
            'doc_no'       => $entry->docNo,
            'party_name'   => $entry->partyName,
            'narration'    => $entry->narration,
            'amount'       => $entry->total(),
            'lines'        => $resolved['lines'],
            'unmapped'     => array_map(function ($part) use ($components) {
                $match = $components[$part['component']]['match'] ?? null;

                return [
                    'label'  => ($components[$part['component']]['label'] ?? $part['component'])
                        .($match && $part['match_key'] !== '' ? ' — '.MatchOptions::label($match, $part['match_key']) : ''),
                    'amount' => $part['amount'],
                ];
            }, $resolved['unmapped']),
            'status'       => $status,
            'voucher_id'   => $voucher?->id,
            'voucher_no'   => $voucher?->voucher_no,
        ];
    }

    private function isChanged(SourceEntry $entry, AccVoucher $voucher): bool
    {
        return $entry->updatedAt !== null
            && $voucher->source_updated_at !== null
            && Carbon::parse($entry->updatedAt)->gt($voucher->source_updated_at);
    }

    /**
     * @return array<string, AccountingSource>
     */
    private function sources(Company $company, ?string $sourceKey): array
    {
        return $sourceKey ? [$sourceKey => $this->sourceFor($company, $sourceKey)] : $this->registry->forCompany($company->slug);
    }

    private function sourceFor(Company $company, string $sourceKey): AccountingSource
    {
        $source = $this->registry->has($sourceKey) ? $this->registry->get($sourceKey) : null;
        if (! $source || $source->companySlug() !== $company->slug) {
            throw ValidationException::withMessages(['source_type' => 'This document type does not belong to the selected company.']);
        }

        return $source;
    }
}
