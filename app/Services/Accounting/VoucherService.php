<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccAccount;
use App\Models\Accounting\AccVoucher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoucherService
{
    /**
     * Create a posted voucher. Lines: account_id, debit, credit, party_type, party_id, party_name, narration.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $userId): AccVoucher
    {
        $lines = $this->validateLines($data['lines'] ?? []);

        return DB::transaction(function () use ($data, $lines, $userId) {
            $date = Carbon::parse($data['voucher_date']);

            $voucher = AccVoucher::create([
                'company_id'        => $data['company_id'],
                'branch_id'         => $data['branch_id'] ?? null,
                'voucher_no'        => $this->nextNumber((int) $data['company_id'], $data['voucher_type'], $date->year),
                'voucher_type'      => $data['voucher_type'],
                'voucher_date'      => $date->toDateString(),
                'narration'         => $data['narration'] ?? null,
                'source_type'       => $data['source_type'] ?? null,
                'source_id'         => $data['source_id'] ?? null,
                'source_ref'        => $data['source_ref'] ?? null,
                'source_doc_no'     => $data['source_doc_no'] ?? null,
                'source_updated_at' => $data['source_updated_at'] ?? null,
                'is_auto'           => (bool) ($data['is_auto'] ?? false),
                'status'            => 'posted',
                'total_amount'      => array_sum(array_column($lines, 'debit')),
                'created_by'        => $userId,
            ]);

            $this->saveLines($voucher, $lines);

            return $voucher;
        });
    }

    /**
     * Replace date, narration and lines of a manual voucher. Number and type stay.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(AccVoucher $voucher, array $data): AccVoucher
    {
        if ($voucher->is_auto || ! $voucher->isPosted()) {
            throw ValidationException::withMessages(['voucher' => 'Only posted manual vouchers can be edited. Cancel and re-post auto vouchers instead.']);
        }

        $lines = $this->validateLines($data['lines'] ?? []);

        return DB::transaction(function () use ($voucher, $data, $lines) {
            $voucher->update([
                'voucher_date' => Carbon::parse($data['voucher_date'])->toDateString(),
                'narration'    => $data['narration'] ?? null,
                'branch_id'    => $data['branch_id'] ?? $voucher->branch_id,
                'total_amount' => array_sum(array_column($lines, 'debit')),
            ]);
            $voucher->lines()->delete();
            $this->saveLines($voucher, $lines);

            return $voucher->refresh();
        });
    }

    /**
     * Cancel keeps the voucher for audit and frees its source document for posting again.
     */
    public function cancel(AccVoucher $voucher, string $reason, ?int $userId): AccVoucher
    {
        if (! $voucher->isPosted()) {
            throw ValidationException::withMessages(['voucher' => 'Voucher is already cancelled.']);
        }

        $voucher->update([
            'status'        => 'cancelled',
            'source_ref'    => null,
            'cancelled_by'  => $userId,
            'cancelled_at'  => now(),
            'cancel_reason' => $reason,
        ]);

        return $voucher;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function validateLines(array $lines): array
    {
        $clean = [];
        foreach (array_values($lines) as $i => $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);
            if (empty($line['account_id']) && $debit == 0 && $credit == 0) {
                continue;
            }
            if ($debit < 0 || $credit < 0 || ($debit > 0) === ($credit > 0)) {
                throw ValidationException::withMessages(["lines.{$i}" => 'Line '.($i + 1).': enter either a debit or a credit amount.']);
            }
            $clean[] = [
                'account_id' => (int) ($line['account_id'] ?? 0),
                'debit'      => $debit,
                'credit'     => $credit,
                'party_type' => ($line['party_type'] ?? null) ?: null,
                'party_id'   => ($line['party_id'] ?? null) ?: null,
                'party_name' => ($line['party_name'] ?? null) ?: null,
                'narration'  => ($line['narration'] ?? null) ?: null,
            ];
        }

        if (count($clean) < 2) {
            throw ValidationException::withMessages(['lines' => 'A voucher needs at least one debit and one credit line.']);
        }

        $accounts = AccAccount::whereIn('id', array_column($clean, 'account_id'))->get()->keyBy('id');
        foreach ($clean as $i => $line) {
            $account = $accounts[$line['account_id']] ?? null;
            if (! $account) {
                throw ValidationException::withMessages(["lines.{$i}" => 'Line '.($i + 1).': select an account.']);
            }
            if ($account->is_group || ! $account->is_active) {
                throw ValidationException::withMessages(["lines.{$i}" => 'Line '.($i + 1).": {$account->code} {$account->name} is a group or inactive account."]);
            }
        }

        $debit = round(array_sum(array_column($clean, 'debit')), 2);
        $credit = round(array_sum(array_column($clean, 'credit')), 2);
        if ($debit <= 0 || abs($debit - $credit) > 0.004) {
            throw ValidationException::withMessages(['lines' => 'Total debit ('.number_format($debit, 2).') must equal total credit ('.number_format($credit, 2).').']);
        }

        return $clean;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function saveLines(AccVoucher $voucher, array $lines): void
    {
        foreach ($lines as $i => $line) {
            $voucher->lines()->create($line + ['sort_order' => $i]);
        }
    }

    private function nextNumber(int $companyId, string $type, int $year): string
    {
        DB::table('acc_voucher_sequences')->insertOrIgnore([
            'company_id' => $companyId, 'voucher_type' => $type, 'year' => $year, 'last_no' => 0,
        ]);

        $sequence = DB::table('acc_voucher_sequences')
            ->where(['company_id' => $companyId, 'voucher_type' => $type, 'year' => $year])
            ->lockForUpdate()
            ->first();

        $next = $sequence->last_no + 1;
        DB::table('acc_voucher_sequences')->where('id', $sequence->id)->update(['last_no' => $next]);

        return AccVoucher::PREFIXES[$type].'-'.$year.'-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
