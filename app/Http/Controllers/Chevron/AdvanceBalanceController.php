<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\MoneyReceipt\IndexMoneyReceiptRequest;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronMoneyReceipt;
use App\Models\Chevron\ChevronMoneyReceiptItem;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;

/**
 * Customer-wise advance balance: advance received through Advance money receipts, minus the part
 * already adjusted against bills ("Advance Adjustment" payment rows). Both sides are read straight from
 * the receipts, so the balance can never drift from the documents behind it.
 */
class AdvanceBalanceController extends Controller
{
    public function index(IndexMoneyReceiptRequest $request)
    {
        $branchId = (int) session('active_branch_id');

        if ($request->ajax()) {
            $received = ChevronMoneyReceipt::query()
                ->selectRaw('COALESCE(SUM(total_amount), 0)')
                ->whereColumn('party_id', 'chevron_customers.id')
                ->where('branch_id', $branchId)
                ->where('receipt_type', ChevronMoneyReceipt::TYPE_ADVANCE);

            $adjusted = ChevronMoneyReceiptItem::query()
                ->selectRaw('COALESCE(SUM(chevron_money_receipt_items.amount), 0)')
                ->join('chevron_money_receipts as adjusting', 'adjusting.id', '=', 'chevron_money_receipt_items.receipt_id')
                ->whereColumn('adjusting.party_id', 'chevron_customers.id')
                ->where('adjusting.branch_id', $branchId)
                ->where('adjusting.receipt_type', ChevronMoneyReceipt::TYPE_BILL)
                ->where('chevron_money_receipt_items.payment_type', ChevronMoneyReceipt::ADVANCE_ADJUSTMENT);

            $query = ChevronCustomer::query()
                ->select(['chevron_customers.id', 'chevron_customers.customer_id', 'chevron_customers.name'])
                ->selectSub($received, 'advance_received')
                ->selectSub($adjusted, 'advance_adjusted')
                ->whereExists(fn ($q) => $q->selectRaw('1')
                    ->from('chevron_money_receipts as advance_receipts')
                    ->whereColumn('advance_receipts.party_id', 'chevron_customers.id')
                    ->where('advance_receipts.branch_id', $branchId)
                    ->where('advance_receipts.receipt_type', ChevronMoneyReceipt::TYPE_ADVANCE));

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('advance_received', fn ($r) => number_format($r->advance_received, 2))
                ->editColumn('advance_adjusted', fn ($r) => number_format($r->advance_adjusted, 2))
                ->addColumn('balance', fn ($r) => number_format($r->advance_received - $r->advance_adjusted, 2))
                ->addColumn('action', fn ($r) => '<a href="'.route('chevron.cnf.advance-balances.show', $r->id).'" class="btn btn-sm btn-outline-info py-0 px-2" title="View statement"><i class="fa fa-file-lines me-1"></i>Statement</a>')
                ->orderColumn('advance_received', 'advance_received $1')
                ->orderColumn('advance_adjusted', 'advance_adjusted $1')
                ->rawColumns(['action'])
                ->make(true);
        }

        $received = (float) ChevronMoneyReceipt::where('branch_id', $branchId)
            ->where('receipt_type', ChevronMoneyReceipt::TYPE_ADVANCE)
            ->whereNotNull('party_id')
            ->sum('total_amount');
        $adjusted = (float) ChevronMoneyReceiptItem::where('payment_type', ChevronMoneyReceipt::ADVANCE_ADJUSTMENT)
            ->whereHas('receipt', fn ($q) => $q->where('branch_id', $branchId)->where('receipt_type', ChevronMoneyReceipt::TYPE_BILL))
            ->sum('amount');

        return view('chevron.cnf.advance-balances.index', [
            'received' => round($received, 2),
            'adjusted' => round($adjusted, 2),
            'balance'  => round($received - $adjusted, 2),
        ]);
    }

    public function show(IndexMoneyReceiptRequest $request, ChevronCustomer $customer)
    {
        $branchId = (int) session('active_branch_id');

        $entries = $this->statementEntries($branchId, $customer->id);
        $received = round($entries->sum('credit'), 2);
        $adjusted = round($entries->sum('debit'), 2);

        return view('chevron.cnf.advance-balances.show', [
            'customer' => $customer,
            'entries'  => $entries,
            'received' => $received,
            'adjusted' => $adjusted,
            'balance'  => round($received - $adjusted, 2),
        ]);
    }

    /**
     * Chronological statement lines (advance received / advance adjusted) with a running balance.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function statementEntries(int $branchId, int $partyId): Collection
    {
        $credits = ChevronMoneyReceipt::with('allocations.job:id,job_no')
            ->where('branch_id', $branchId)
            ->where('party_id', $partyId)
            ->where('receipt_type', ChevronMoneyReceipt::TYPE_ADVANCE)
            ->get()
            ->map(function (ChevronMoneyReceipt $receipt) {
                $jobNos = $receipt->allocations->pluck('job.job_no')->filter()->implode(', ');

                return [
                    'sort'      => $receipt->receipt_date?->format('Y-m-d').'-'.str_pad((string) $receipt->id, 10, '0', STR_PAD_LEFT).'-0',
                    'date'      => $receipt->receipt_date,
                    'receipt'   => $receipt,
                    'kind'      => 'Received',
                    'reference' => $jobNos !== '' ? $jobNos : 'General advance',
                    'credit'    => (float) $receipt->total_amount,
                    'debit'     => 0.0,
                ];
            });

        $debits = ChevronMoneyReceiptItem::with('receipt.allocations.bill:id,bill_no')
            ->where('payment_type', ChevronMoneyReceipt::ADVANCE_ADJUSTMENT)
            ->whereHas('receipt', fn ($q) => $q
                ->where('branch_id', $branchId)
                ->where('party_id', $partyId)
                ->where('receipt_type', ChevronMoneyReceipt::TYPE_BILL))
            ->get()
            ->map(function (ChevronMoneyReceiptItem $item) {
                $receipt = $item->receipt;

                return [
                    'sort'      => $receipt->receipt_date?->format('Y-m-d').'-'.str_pad((string) $receipt->id, 10, '0', STR_PAD_LEFT).'-1',
                    'date'      => $receipt->receipt_date,
                    'receipt'   => $receipt,
                    'kind'      => 'Adjusted',
                    'reference' => $receipt->allocations->pluck('bill.bill_no')->filter()->implode(', ') ?: '—',
                    'credit'    => 0.0,
                    'debit'     => (float) $item->amount,
                ];
            });

        $running = 0.0;

        return $credits->concat($debits)
            ->sortBy('sort')
            ->values()
            ->map(function (array $entry) use (&$running) {
                $running = round($running + $entry['credit'] - $entry['debit'], 2);
                $entry['balance'] = $running;

                return $entry;
            });
    }
}
