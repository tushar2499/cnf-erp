<?php

namespace App\Http\Controllers\Chevron;

use App\Helpers\NumberHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\Bill\CreateBillRequest;
use App\Http\Requests\Chevron\Bill\DestroyBillRequest;
use App\Http\Requests\Chevron\Bill\EditBillRequest;
use App\Http\Requests\Chevron\Bill\IndexBillRequest;
use App\Http\Requests\Chevron\Bill\PrintBillRequest;
use App\Http\Requests\Chevron\Bill\ShowBillRequest;
use App\Http\Requests\Chevron\Bill\StoreBillRequest;
use App\Http\Requests\Chevron\Bill\UpdateBillRequest;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronMoneyReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class BillController extends Controller
{
    private function formData(): array
    {
        $heads = ChevronExpenseHead::where('is_active', true)
            ->where('is_bill', true)
            ->orderBy('name')
            ->get();

        return [
            'billTypes'           => ChevronBill::billTypes(),
            'commissionOnOptions' => ChevronBill::commissionOnOptions(),
            'expenseCategories'   => ChevronExpenseCategory::where('is_active', true)->where('is_bill', true)->orderBy('name')->get(),
            'expenseHeads'        => $heads,
            'expenseHeadsJson'    => $heads->map(fn ($h) => ['id' => $h->id, 'name' => $h->name, 'cat' => $h->expense_category_id, 'amount' => (float) $h->amount])->values(),
            'today'               => now()->format('Y-m-d'),
        ];
    }

    public function index(IndexBillRequest $request)
    {
        if ($request->ajax()) {
            $query = ChevronBill::where('branch_id', session('active_branch_id'))
                ->orderByDesc('id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bill_date', fn ($r) => $r->bill_date?->format('d M Y') ?? '—')
                ->editColumn('delivery_date', fn ($r) => $r->delivery_date?->format('d M Y') ?? '—')
                ->addColumn('sub_total_fmt', fn ($r) => number_format($r->sub_total, 2))
                ->addColumn('net_payable_fmt', fn ($r) => number_format($r->net_payable, 2))
                ->addColumn('due_amount_fmt', fn ($r) => number_format($r->due_amount, 2))
                ->addColumn('received_amount_fmt', fn ($r) => number_format($r->received_amount, 2))
                ->addColumn('balance_fmt', fn ($r) => number_format($r->balance, 2))
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Submitted' => '<span class="badge bg-warning text-dark">Submitted</span>',
                    'Approved'  => '<span class="badge bg-success">Approved</span>',
                    default     => '<span class="badge bg-primary">Active</span>',
                })
                ->addColumn('action', function ($r) use ($request) {
                    $html = '<a href="'.route('chevron.cnf.bills.show', $r->id).'" class="btn btn-sm btn-outline-info py-0 px-1" title="View"><i class="fa fa-eye"></i></a> ';
                    if ($request->user()->hasPermission('cnf.bill.print')) {
                        $html .= '<a href="'.route('chevron.cnf.bills.print', $r->id).'" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Print"><i class="fa fa-print"></i></a> ';
                    }
                    if ($request->user()->hasPermission('cnf.bill.edit')) {
                        $html .= '<a href="'.route('chevron.cnf.bills.edit', $r->id).'" class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit"><i class="fa fa-edit"></i></a> ';
                    }
                    if ($request->user()->hasPermission('cnf.bill.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete" data-url="'.route('chevron.cnf.bills.destroy', $r->id).'" data-name="'.e($r->bill_no).'" title="Delete"><i class="fa fa-trash"></i></button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.cnf.bills.index');
    }

    public function create(CreateBillRequest $request)
    {
        return view('chevron.cnf.bills.create', array_merge($this->formData(), [
            'bill'             => null,
            'assessableValue'  => null,
            'existingRows'     => [],
        ]));
    }

    public function store(StoreBillRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $this->reserveAdvance($this->prepareData($request), $request);

            $bill = ChevronBill::create(array_merge($data, [
                'bill_no' => ChevronBill::generateBillNo(),
            ]));
            $this->saveItems($bill, $request->rows);

            $this->refreshAdvanceBalances([$bill->customer_id]);
        });

        return redirect()->route('chevron.cnf.bills.index')
            ->with('success', 'Bill created successfully.');
    }

    public function edit(EditBillRequest $request, ChevronBill $bill)
    {
        $bill->load(['items', 'job']);
        $existingRows = $bill->items->map(fn ($i) => [
            'expense_category_id' => $i->expense_category_id,
            'expense_head_id'     => $i->expense_head_id,
            'amount'              => $i->amount,
            'note'                => $i->note,
            'rate'                => $i->rate,
            'qty'                 => $i->qty,
        ])->values();

        return view('chevron.cnf.bills.create', array_merge($this->formData(), [
            'bill'             => $bill,
            'assessableValue'  => $bill->job?->assessable_value ?? $bill->assessable_value,
            'existingRows'     => $existingRows,
        ]));
    }

    public function update(UpdateBillRequest $request, ChevronBill $bill)
    {

        DB::transaction(function () use ($request, $bill) {
            $previousCustomerId = $bill->customer_id;
            $data = $this->reserveAdvance($this->prepareData($request), $request, $bill);

            $bill = ChevronBill::lockForUpdate()->findOrFail($bill->id);

            if (round($data['due_amount'] - (float) $bill->received_amount, 2) < 0) {
                throw ValidationException::withMessages([
                    'advance_amount' => 'Due amount cannot be lower than '.number_format((float) $bill->received_amount, 2).', already received through money receipts.',
                ]);
            }

            $bill->update($data);
            $bill->items()->delete();
            $this->saveItems($bill, $request->rows);

            $this->refreshAdvanceBalances([$previousCustomerId, $bill->customer_id]);
        });

        return redirect()->route('chevron.cnf.bills.index')
            ->with('success', 'Bill '.$bill->bill_no.' updated successfully.');
    }

    public function destroy(DestroyBillRequest $request, ChevronBill $bill)
    {
        if ($bill->allocations()->exists()) {
            return response()->json(['message' => 'Bill has money receipts collected against it. Delete those receipts first.'], 422);
        }

        DB::transaction(function () use ($bill) {
            $this->lockCustomers([$bill->customer_id]);

            $bill->delete();

            $this->refreshAdvanceBalances([$bill->customer_id]);
        });

        return response()->json(['message' => 'Bill deleted.']);
    }

    public function show(ShowBillRequest $request, ChevronBill $bill)
    {
        abort_unless((int) $bill->branch_id === (int) session('active_branch_id'), 404);

        $bill->load(['items.expenseCategory', 'items.expenseHead', 'job', 'branch']);

        return view('chevron.cnf.bills.show', [
            'bill'               => $bill,
            'grouped'            => $bill->items->groupBy(fn ($i) => $i->expenseCategory?->name ?? 'OTHER'),
            'inWords'            => NumberHelper::amountInWords((float) $bill->due_amount),
            'advanceCollections' => $bill->advanceCollections(),
            'receiptCollections' => $bill->receiptCollections(),
            'canEdit'            => (bool) $request->user()?->hasPermission('cnf.bill.edit'),
            'canPrint'           => (bool) $request->user()?->hasPermission('cnf.bill.print'),
            'canOpenReceipts'    => (bool) $request->user()?->hasPermission('cnf.money-receipt.list'),
        ]);
    }

    public function print(PrintBillRequest $request, ChevronBill $bill)
    {
        $bill->load(['items.expenseCategory', 'items.expenseHead', 'job']);
        $grouped = $bill->items->groupBy(fn ($i) => $i->expenseCategory?->name ?? 'OTHER');
        $inWords = NumberHelper::amountInWords((float) $bill->due_amount);
        $advanceCollections = (float) $bill->advance_amount > 0 ? $bill->advanceCollections() : collect();

        return view('chevron.cnf.bills.print', compact('bill', 'grouped', 'inWords', 'advanceCollections'));
    }

    /**
     * Customer advance for the bill form: what was collected for the selected job, the customer's total
     * advance balance and how much of it this bill may use. When editing, `bill_id` hands the bill's own
     * advance back so it can be re-applied.
     */
    public function advanceInfo(IndexBillRequest $request): JsonResponse
    {
        $branchId = (int) session('active_branch_id');
        $job = $request->filled('job_id') ? ChevronJob::find($request->integer('job_id')) : null;
        $bill = $request->filled('bill_id')
            ? ChevronBill::where('branch_id', $branchId)->find($request->integer('bill_id'))
            : null;
        $customer = $this->resolveCustomer($job);
        $jobCollections = $customer && $job
            ? ChevronMoneyReceipt::advanceCollectionsForJob($branchId, $customer->id, $job->id)
            : collect();
        $canOpenReceipts = (bool) $request->user()?->hasPermission('cnf.money-receipt.list');

        return response()->json([
            'customer_id'     => $customer?->id,
            'customer_name'   => $customer?->name,
            'job_collections' => $jobCollections->map(fn (array $row) => [
                'date'        => $row['receipt_date']?->format('d-M-Y'),
                'receipt_no'  => $row['receipt_no'],
                'receipt_url' => $canOpenReceipts ? route('chevron.cnf.money-receipts.show', $row['receipt_id']) : null,
                'amount'      => $row['amount'],
            ])->all(),
            'job_advance'   => round((float) $jobCollections->sum('amount'), 2),
            'total_advance' => $customer ? max(ChevronMoneyReceipt::advanceBalance($branchId, $customer->id, null, $bill?->id), 0.0) : 0.0,
            'available'     => $this->availableAdvance($customer?->id, $bill),
            'statement_url' => $customer ? route('chevron.cnf.advance-balances.show', $customer->id) : null,
        ]);
    }

    public function searchJobs(IndexBillRequest $request)
    {
        $q = $request->input('q', '');
        $jobs = ChevronJob::where('job_no', 'like', "%{$q}%")
            ->orWhere('party_name', 'like', "%{$q}%")
            ->limit(20)
            ->get([
                'id', 'job_no', 'party_name', 'party_address',
                'goods_name', 'mate_code', 'po_no',
                'pack_quantity', 'pack_unit',
                'gross_weight', 'gross_weight_unit',
                'lc_no', 'lca_no',
                'be_no', 'be_date',
                'hbi_hawb_no', 'invoice_no', 'invoice_date',
                'mbl_mawb_no', 'bl_no', 'bl_date',
                'assessable_value', 'invoice_value_1', 'invoice_value_2',
                'currency_type', 'currency_rate', 'delivery_date',
            ]);

        return response()->json($jobs->map(fn ($j) => [
            'id'                => $j->id,
            'text'              => $j->job_no.' — '.$j->party_name,
            'job_no'            => $j->job_no,
            'party_name'        => $j->party_name,
            'party_address'     => $j->party_address,
            'goods_name'        => $j->goods_name,
            'mate_code'         => $j->mate_code,
            'po_no'             => $j->po_no,
            'quantity'          => $j->pack_quantity,
            'quantity_unit'     => $j->pack_unit,
            'gross_weight'      => $j->gross_weight,
            'gross_weight_unit' => $j->gross_weight_unit,
            'lc_no'             => $j->lc_no,
            'lc_ref'            => $j->lca_no,
            'be_no'             => $j->be_no,
            'be_date'           => $j->be_date?->format('Y-m-d'),
            'invoice_no'        => $j->invoice_no,
            'invoice_ref'       => $j->hbi_hawb_no,
            'invoice_date'      => $j->invoice_date?->format('Y-m-d'),
            'bl_no'             => $j->bl_no,
            'bl_ref'            => $j->mbl_mawb_no,
            'bl_date'           => $j->bl_date?->format('Y-m-d'),
            'delivery_date'     => $j->delivery_date?->format('Y-m-d'),
            'assessable_value'  => $j->assessable_value,
            'invoice_value'     => $j->invoice_value_1,
            'invoice_value_bdt' => $j->invoice_value_2,
            'currency_type'     => $j->currency_type,
            'currency_rate'     => $j->currency_rate,
        ]));
    }

    /**
     * The customer a job's bill belongs to: the job's linked customer, else the one customer carrying the party name.
     */
    private function resolveCustomer(?ChevronJob $job): ?ChevronCustomer
    {
        if (! $job) {
            return null;
        }

        if ($job->customer_id) {
            return ChevronCustomer::find($job->customer_id);
        }

        if (trim((string) $job->party_name) === '') {
            return null;
        }

        $matches = ChevronCustomer::where('name', $job->party_name)->limit(2)->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /**
     * Advance this bill may draw: the customer's balance in the active branch (the bill's own advance counts as
     * available again). Without a linked customer only advance already entered by hand on an old bill stays valid.
     */
    private function availableAdvance(?int $customerId, ?ChevronBill $bill): float
    {
        if ($customerId) {
            return max(ChevronMoneyReceipt::advanceBalance((int) session('active_branch_id'), $customerId, null, $bill?->id), 0.0);
        }

        return $bill && ! $bill->customer_id ? (float) $bill->advance_amount : 0.0;
    }

    /**
     * Checks the advance on a bill against the customer's balance and the bill's net payable, and links the
     * bill to its customer. Locks the customer rows first, the same order money receipts use.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function reserveAdvance(array $data, Request $request, ?ChevronBill $bill = null): array
    {
        $job = $request->filled('job_id') ? ChevronJob::find($request->integer('job_id')) : null;
        $customer = $this->resolveCustomer($job);

        $this->lockCustomers([$bill?->customer_id, $customer?->id]);

        $advance = (float) $data['advance_amount'];
        $netPayable = max((float) $data['net_payable'], 0.0);
        $available = $this->availableAdvance($customer?->id, $bill);

        if ($advance < 0) {
            throw ValidationException::withMessages(['advance_amount' => 'Advance amount cannot be negative.']);
        }

        if (round($advance - $netPayable, 2) > 0) {
            throw ValidationException::withMessages([
                'advance_amount' => 'Advance ('.number_format($advance, 2).') cannot exceed the bill\'s net payable amount ('.number_format($netPayable, 2).').',
            ]);
        }

        if (round($advance - $available, 2) > 0) {
            throw ValidationException::withMessages([
                'advance_amount' => $customer
                    ? 'Advance ('.number_format($advance, 2).') exceeds the available advance balance of '.$customer->name.' ('.number_format($available, 2).').'
                    : 'Advance can only be applied from a customer\'s advance balance, and this job has no linked customer.',
            ]);
        }

        return $data + ['customer_id' => $customer?->id];
    }

    /**
     * @param  array<int, int|string|null>  $customerIds
     */
    private function lockCustomers(array $customerIds): void
    {
        $ids = collect($customerIds)->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values();

        if ($ids->isNotEmpty()) {
            ChevronCustomer::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        }
    }

    /**
     * @param  array<int, int|string|null>  $customerIds
     */
    private function refreshAdvanceBalances(array $customerIds): void
    {
        collect($customerIds)->filter()->map(fn ($id) => (int) $id)->unique()
            ->each(fn (int $id) => ChevronCustomer::refreshAdvanceBalance($id));
    }

    private function prepareData(Request $request): array
    {
        $subTotal = (float) $request->sub_total;
        $commRate = (float) $request->commission_rate;
        $commOn = $request->commission_on ?? 'ASSESSABLE';
        $assessable = (float) $request->assessable_value;
        $invoiceBdt = (float) $request->invoice_value_bdt;
        if (in_array($commOn, ['MINIMUM', 'MAXIMUM'])) {
            $commAmt = round((float) $request->commission_amount, 2);
        } else {
            $base = $commOn === 'INVOICE VALUE' ? $invoiceBdt : $assessable;
            $commAmt = round($base * $commRate / 100, 2);
        }
        $totalPayable = round($subTotal + $commAmt, 2);
        $lessDuty = (float) $request->less_customs_duty_tax;
        $incomeTax = (float) $request->income_tax_cnf_com;
        $netPayable = round($totalPayable - $lessDuty - $incomeTax, 2);
        $advance = (float) $request->advance_amount;
        $dueAmount = round($netPayable - $advance, 2);

        return [
            'bill_type'             => $request->bill_type,
            'bill_date'             => $request->bill_date,
            'delivery_date'         => $request->delivery_date ?: null,
            'job_id'                => $request->job_id ?: null,
            'job_no'                => $request->job_no,
            'party_name'            => $request->party_name,
            'party_address'         => $request->party_address,
            'goods_description'     => $request->goods_description,
            'mate_code'             => $request->mate_code,
            'po_no'                 => $request->po_no,
            'quantity'              => $request->quantity ?: null,
            'quantity_unit'         => $request->quantity_unit ?: 'KG',
            'quantity_remark'       => $request->quantity_remark,
            'gross_weight'          => $request->gross_weight ?: null,
            'gross_weight_unit'     => $request->gross_weight_unit,
            'lc_no'                 => $request->lc_no,
            'lc_ref'                => $request->lc_ref,
            'be_no'                 => $request->be_no,
            'be_date'               => $request->be_date ?: null,
            'invoice_no'            => $request->invoice_no,
            'invoice_ref'           => $request->invoice_ref,
            'invoice_date'          => $request->invoice_date ?: null,
            'bl_no'                 => $request->bl_no,
            'bl_ref'                => $request->bl_ref,
            'bl_date'               => $request->bl_date ?: null,
            'assessable_value'      => $assessable ?: null,
            'invoice_value_bdt'     => $invoiceBdt ?: null,
            'invoice_value'         => $request->invoice_value ?: null,
            'currency_type'         => $request->currency_type ?: null,
            'currency_rate'         => $request->currency_rate ?: null,
            'remarks'               => $request->remarks,
            'sub_total'             => $subTotal,
            'commission_on'         => $commOn,
            'commission_rate'       => $commRate ?: null,
            'commission_amount'     => $commAmt,
            'total_payable'         => $totalPayable,
            'less_customs_duty_tax' => $lessDuty,
            'income_tax_cnf_com'    => $incomeTax,
            'net_payable'           => $netPayable,
            'advance_amount'        => $advance,
            'due_amount'            => $dueAmount,
            'status'                => ($advance > 0 && $dueAmount <= 0) ? 'Approved' : ($advance > 0 ? 'Submitted' : 'Active'),
            'branch_id'             => session('active_branch_id'),
        ];
    }

    private function saveItems(ChevronBill $bill, array $rows): void
    {
        foreach ($rows as $row) {
            $rate = is_numeric($row['rate'] ?? '') ? (float) $row['rate'] : null;
            $qty = is_numeric($row['qty'] ?? '') ? (float) $row['qty'] : null;
            $amt = ($rate !== null && $qty !== null && $qty > 0)
                ? round($rate * $qty, 2)
                : (float) ($row['amount'] ?? 0);

            $bill->items()->create([
                'expense_category_id' => $row['expense_category_id'] ?: null,
                'expense_head_id'     => $row['expense_head_id'] ?: null,
                'note'                => $row['note'] ?? null,
                'rate'                => $rate,
                'qty'                 => $qty,
                'amount'              => $amt,
            ]);
        }
    }
}
