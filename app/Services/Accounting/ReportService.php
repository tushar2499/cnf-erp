<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccAccount;
use App\Models\Accounting\AccVoucher;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Financial reports over posted vouchers. $companyId = null means all companies (group view).
 * Balances are kept as debit minus credit; positive = Dr, negative = Cr.
 */
class ReportService
{
    /**
     * @return array{account: AccAccount, opening: float, rows: list<array<string, mixed>>, total_debit: float, total_credit: float, closing: float}
     */
    public function ledger(AccAccount $account, ?int $companyId, string $from, string $to, ?string $partyType = null, ?int $partyId = null): array
    {
        $scope = function (Builder $q) use ($account, $companyId, $partyType, $partyId) {
            $q->where('l.account_id', $account->id);
            $this->scopeCompany($q, $companyId);
            if ($partyType && $partyId) {
                $q->where('l.party_type', $partyType)->where('l.party_id', $partyId);
            }
        };

        $opening = (float) $this->lines()->tap($scope)->where('v.voucher_date', '<', $from)->sum(DB::raw('l.debit - l.credit'));

        $lines = $this->lines()->tap($scope)
            ->whereBetween('v.voucher_date', [$from, $to])
            ->orderBy('v.voucher_date')->orderBy('v.id')->orderBy('l.sort_order')
            ->get(['l.*', 'v.voucher_no', 'v.voucher_date', 'v.voucher_type', 'v.narration as voucher_narration', 'v.company_id']);

        $counterNames = $this->counterAccounts($lines->pluck('voucher_id')->unique()->all());

        $balance = $opening;
        $rows = $lines->map(function ($line) use (&$balance, $counterNames) {
            $balance += $line->debit - $line->credit;

            return [
                'date'         => $line->voucher_date,
                'voucher_id'   => $line->voucher_id,
                'voucher_no'   => $line->voucher_no,
                'voucher_type' => $line->voucher_type,
                'particulars'  => $this->otherAccounts($counterNames[$line->voucher_id] ?? [], $line->account_id),
                'party_name'   => $line->party_name,
                'narration'    => $line->narration ?: $line->voucher_narration,
                'debit'        => (float) $line->debit,
                'credit'       => (float) $line->credit,
                'balance'      => round($balance, 2),
            ];
        })->all();

        return [
            'account'      => $account,
            'opening'      => round($opening, 2),
            'rows'         => $rows,
            'total_debit'  => round(array_sum(array_column($rows, 'debit')), 2),
            'total_credit' => round(array_sum(array_column($rows, 'credit')), 2),
            'closing'      => round($balance, 2),
        ];
    }

    /**
     * Opening, period movement and closing per account, rolled up to the requested level.
     *
     * @return array{rows: list<array<string, mixed>>, totals: array<string, float>}
     */
    public function trialBalance(?int $companyId, string $from, string $to, int $level = AccAccount::LEVEL_LEDGER, bool $hideZero = true): array
    {
        $opening = $this->balancesByAccount($companyId, null, $from, false);
        $period = $this->movementByAccount($companyId, $from, $to);
        $accounts = $this->accountsByCode();
        $codeLength = AccAccount::CODE_LENGTHS[$level];

        $rows = [];
        foreach ($accounts->where('is_group', false) as $account) {
            $open = $opening[$account->id] ?? 0.0;
            $debit = $period[$account->id]['debit'] ?? 0.0;
            $credit = $period[$account->id]['credit'] ?? 0.0;

            $code = substr($account->code, 0, $codeLength);
            $target = $accounts[$code] ?? $account;
            $rows[$code] ??= ['code' => $target->code, 'name' => $target->name, 'type' => $target->type, 'account_id' => $target->is_group ? null : $target->id, 'opening' => 0.0, 'debit' => 0.0, 'credit' => 0.0];
            $rows[$code]['opening'] += $open;
            $rows[$code]['debit'] += $debit;
            $rows[$code]['credit'] += $credit;
        }

        $rows = collect($rows)
            ->map(fn ($r) => $r + ['closing' => round($r['opening'] + $r['debit'] - $r['credit'], 2)])
            ->filter(fn ($r) => ! $hideZero || abs($r['opening']) > 0.004 || $r['debit'] > 0 || $r['credit'] > 0)
            ->sortKeys()
            ->values()
            ->all();

        $totals = [
            'opening_dr' => 0.0, 'opening_cr' => 0.0, 'debit' => 0.0, 'credit' => 0.0, 'closing_dr' => 0.0, 'closing_cr' => 0.0,
        ];
        foreach ($rows as $r) {
            $totals['opening_dr'] += max($r['opening'], 0);
            $totals['opening_cr'] += max(-$r['opening'], 0);
            $totals['debit'] += $r['debit'];
            $totals['credit'] += $r['credit'];
            $totals['closing_dr'] += max($r['closing'], 0);
            $totals['closing_cr'] += max(-$r['closing'], 0);
        }

        return ['rows' => $rows, 'totals' => array_map(fn ($v) => round($v, 2), $totals)];
    }

    /**
     * Income and expense for the period, grouped by Sub-1 and Sub-2.
     *
     * @return array{income: array<string, mixed>, expense: array<string, mixed>, net_profit: float}
     */
    public function profitLoss(?int $companyId, string $from, string $to): array
    {
        $movement = $this->movementByAccount($companyId, $from, $to);
        $balances = collect($movement)->map(fn ($m) => $m['debit'] - $m['credit'])->all();

        $income = $this->section('income', $balances, -1);
        $expense = $this->section('expense', $balances, 1);

        return [
            'income'     => $income,
            'expense'    => $expense,
            'net_profit' => round($income['total'] - $expense['total'], 2),
        ];
    }

    /**
     * Assets, liabilities and equity as on a date; profit to date is shown inside equity.
     *
     * @return array<string, mixed>
     */
    public function balanceSheet(?int $companyId, string $asOn): array
    {
        $balances = $this->balancesByAccount($companyId, null, $asOn, true);

        $assets = $this->section('asset', $balances, 1);
        $liabilities = $this->section('liability', $balances, -1);
        $equity = $this->section('equity', $balances, -1);
        $income = $this->section('income', $balances, -1);
        $expense = $this->section('expense', $balances, 1);
        $profit = round($income['total'] - $expense['total'], 2);

        return [
            'assets'          => $assets,
            'liabilities'     => $liabilities,
            'equity'          => $equity,
            'profit'          => $profit,
            'total_assets'    => $assets['total'],
            'total_claims'    => round($liabilities['total'] + $equity['total'] + $profit, 2),
            'difference'      => round($assets['total'] - ($liabilities['total'] + $equity['total'] + $profit), 2),
        ];
    }

    /**
     * @return Collection<int, AccVoucher>
     */
    public function dayBook(?int $companyId, string $from, string $to, ?string $type = null): Collection
    {
        return AccVoucher::with(['lines.account', 'company'])
            ->posted()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($type, fn ($q) => $q->where('voucher_type', $type))
            ->whereBetween('voucher_date', [$from, $to])
            ->orderBy('voucher_date')->orderBy('id')
            ->get();
    }

    /**
     * One ledger per cash / bank account (or the selected one), with receipts and payments.
     *
     * @return list<array<string, mixed>>
     */
    public function cashBook(?int $companyId, string $from, string $to, ?int $accountId = null): array
    {
        $accounts = AccAccount::where('is_cash_bank', true)->where('is_group', false)
            ->when($accountId, fn ($q) => $q->where('id', $accountId))
            ->orderBy('code')
            ->get();

        return $accounts
            ->map(fn ($account) => $this->ledger($account, $companyId, $from, $to))
            ->filter(fn ($ledger) => $accountId || $ledger['rows'] || abs($ledger['opening']) > 0.004)
            ->values()
            ->all();
    }

    /**
     * Group the ledgers of one type under their Sub-1 / Sub-2 heads.
     * $sign turns debit-minus-credit into the natural sign of the type (+1 Dr types, -1 Cr types).
     *
     * @param  array<int, float>  $balances
     * @return array{groups: list<array<string, mixed>>, total: float}
     */
    private function section(string $type, array $balances, int $sign): array
    {
        $accounts = $this->accountsByCode();
        $groups = [];

        foreach ($accounts->where('type', $type)->where('is_group', false) as $ledger) {
            $amount = round(($balances[$ledger->id] ?? 0) * $sign, 2);
            if (abs($amount) < 0.005) {
                continue;
            }
            $sub1 = $accounts[substr($ledger->code, 0, 4)] ?? null;
            $sub2 = $accounts[substr($ledger->code, 0, 6)] ?? null;
            $k1 = $sub1?->code ?? '—';
            $k2 = $sub2?->code ?? '—';

            $groups[$k1] ??= ['code' => $sub1?->code, 'name' => $sub1?->name ?? 'Other', 'total' => 0.0, 'subgroups' => []];
            $groups[$k1]['subgroups'][$k2] ??= ['code' => $sub2?->code, 'name' => $sub2?->name ?? 'Other', 'total' => 0.0, 'ledgers' => []];
            $groups[$k1]['subgroups'][$k2]['ledgers'][] = ['id' => $ledger->id, 'code' => $ledger->code, 'name' => $ledger->name, 'amount' => $amount];
            $groups[$k1]['subgroups'][$k2]['total'] += $amount;
            $groups[$k1]['total'] += $amount;
        }

        ksort($groups);
        foreach ($groups as &$group) {
            ksort($group['subgroups']);
            $group['subgroups'] = array_values($group['subgroups']);
        }

        return ['groups' => array_values($groups), 'total' => round(array_sum(array_column($groups, 'total')), 2)];
    }

    /**
     * Debit minus credit per account before $date (exclusive) or up to $date (inclusive).
     *
     * @return array<int, float>
     */
    private function balancesByAccount(?int $companyId, ?string $from, string $date, bool $inclusive): array
    {
        return $this->lines()
            ->tap(fn ($q) => $this->scopeCompany($q, $companyId))
            ->when($from, fn ($q) => $q->where('v.voucher_date', '>=', $from))
            ->where('v.voucher_date', $inclusive ? '<=' : '<', $date)
            ->groupBy('l.account_id')
            ->selectRaw('l.account_id, SUM(l.debit - l.credit) as balance')
            ->pluck('balance', 'account_id')
            ->map(fn ($b) => (float) $b)
            ->all();
    }

    /**
     * @return array<int, array{debit: float, credit: float}>
     */
    private function movementByAccount(?int $companyId, string $from, string $to): array
    {
        return $this->lines()
            ->tap(fn ($q) => $this->scopeCompany($q, $companyId))
            ->whereBetween('v.voucher_date', [$from, $to])
            ->groupBy('l.account_id')
            ->selectRaw('l.account_id, SUM(l.debit) as debit, SUM(l.credit) as credit')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->account_id => ['debit' => (float) $r->debit, 'credit' => (float) $r->credit]])
            ->all();
    }

    /**
     * @param  list<int>  $voucherIds
     * @return array<int, array<int, string>> voucher id => [account id => name]
     */
    private function counterAccounts(array $voucherIds): array
    {
        if (! $voucherIds) {
            return [];
        }

        $result = [];
        DB::table('acc_voucher_lines as l')
            ->join('acc_accounts as a', 'a.id', '=', 'l.account_id')
            ->whereIn('l.voucher_id', $voucherIds)
            ->get(['l.voucher_id', 'l.account_id', 'a.name'])
            ->each(function ($r) use (&$result) {
                $result[$r->voucher_id][$r->account_id] = $r->name;
            });

        return $result;
    }

    /**
     * @param  array<int, string>  $names
     */
    private function otherAccounts(array $names, int $accountId): string
    {
        unset($names[$accountId]);

        return implode(', ', $names);
    }

    private function lines(): Builder
    {
        return DB::table('acc_voucher_lines as l')
            ->join('acc_vouchers as v', 'v.id', '=', 'l.voucher_id')
            ->where('v.status', 'posted');
    }

    private function scopeCompany(Builder $query, ?int $companyId): void
    {
        if ($companyId) {
            $query->where('v.company_id', $companyId);
        }
    }

    /**
     * @return Collection<string, AccAccount>
     */
    private function accountsByCode(): Collection
    {
        return AccAccount::orderBy('code')->get()->keyBy('code');
    }
}
