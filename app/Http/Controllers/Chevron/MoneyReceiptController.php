<?php

namespace App\Http\Controllers\Chevron;

use App\Helpers\NumberHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\MoneyReceipt\CreateMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\DestroyMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\EditMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\IndexMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\PrintMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\ShowMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\StoreMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\UpdateMoneyReceiptRequest;
use App\Models\Chevron\ChevronAccount;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronMoneyReceipt;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class MoneyReceiptController extends Controller
{
    public function index(IndexMoneyReceiptRequest $request)
    {
        if ($request->ajax()) {
            $query = ChevronMoneyReceipt::with(['allocations.bill:id,bill_no', 'allocations.job:id,job_no'])
                ->where('branch_id', session('active_branch_id'))
                ->orderByDesc('id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('receipt_date', fn ($r) => $r->receipt_date?->format('d-M-Y'))
                ->editColumn('total_amount', fn ($r) => number_format($r->total_amount, 2))
                ->editColumn('payable_amount', fn ($r) => number_format($r->payable_amount, 2))
                ->addColumn('type_badge', fn ($r) => match ($r->receipt_type) {
                    ChevronMoneyReceipt::TYPE_ADVANCE => '<span class="badge bg-info text-dark">Advance</span>',
                    ChevronMoneyReceipt::TYPE_BILL    => '<span class="badge bg-success">Bill</span>',
                    default                           => '<span class="badge bg-secondary">Legacy</span>',
                })
                ->addColumn('reference', fn ($r) => e($this->referenceLabel($r)))
                ->filterColumn('reference', function ($query, $keyword) {
                    $query->whereHas('allocations', function ($allocation) use ($keyword) {
                        $allocation->where(function ($match) use ($keyword) {
                            $match->whereHas('bill', fn ($bill) => $bill->where('bill_no', 'like', "%{$keyword}%"))
                                ->orWhereHas('job', fn ($job) => $job->where('job_no', 'like', "%{$keyword}%"));
                        });
                    });
                })
                ->addColumn('status_badge', function ($r) {
                    return match ($r->status) {
                        'Approved'  => '<span class="badge bg-success">Approved</span>',
                        'Submitted' => '<span class="badge bg-warning text-dark">Submitted</span>',
                        default     => '<span class="badge bg-primary">Active</span>',
                    };
                })
                ->addColumn('action', function ($r) use ($request) {
                    $html = '<a href="'.route('chevron.cnf.money-receipts.show', $r->id).'" class="btn btn-sm btn-outline-info py-0 px-1" title="View" aria-label="View receipt '.e($r->receipt_no).'"><i class="fa fa-eye"></i></a> '
                        .'<a href="'.route('chevron.cnf.money-receipts.print', $r->id).'" target="_blank" class="btn btn-sm btn-outline-dark py-0 px-1" title="Print" aria-label="Print receipt '.e($r->receipt_no).'"><i class="fa fa-print"></i></a> ';

                    if (! $r->isEditable()) {
                        return $html.'<span class="text-muted ms-1" title="'.e($r->status).' receipts are locked"><i class="fa fa-lock"></i><span class="visually-hidden">Locked</span></span>';
                    }

                    if ($request->user()->hasPermission('cnf.money-receipt.edit')) {
                        $html .= '<a href="'.route('chevron.cnf.money-receipts.edit', $r->id).'" class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit"><i class="fa fa-edit"></i></a> ';
                    }
                    if ($request->user()->hasPermission('cnf.money-receipt.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete" data-url="'.route('chevron.cnf.money-receipts.destroy', $r->id).'" data-name="'.e($r->receipt_no).'" title="Delete"><i class="fa fa-trash"></i></button>';
                    }

                    return $html;
                })
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.cnf.money-receipts.index');
    }

    public function create(CreateMoneyReceiptRequest $request)
    {
        return view('chevron.cnf.money-receipts.create', array_merge($this->formData(), [
            'existingItems' => old('items', []),
        ]));
    }

    public function store(StoreMoneyReceiptRequest $request)
    {
        $this->persist($request);

        return redirect()->route('chevron.cnf.money-receipts.index')
            ->with('success', 'Money receipt created successfully.');
    }

    public function edit(EditMoneyReceiptRequest $request, ChevronMoneyReceipt $moneyReceipt)
    {
        $this->guardEditable($moneyReceipt);

        $moneyReceipt->load(['items', 'allocations.job:id,job_no,party_name']);

        $existingItems = $moneyReceipt->items->map(fn ($i) => [
            'payment_type'       => $i->payment_type,
            'account_id'         => $i->account_id,
            'account_no'         => $i->account_no,
            'cheque_card_holder' => $i->cheque_card_holder,
            'cheque_card_no'     => $i->cheque_card_no,
            'amount'             => $i->amount,
            'cheque_date'        => $i->cheque_date?->format('Y-m-d'),
        ])->values()->toArray();

        $existingAllocations = $moneyReceipt->allocations
            ->whereNotNull('bill_id')
            ->map(fn ($a) => ['bill_id' => $a->bill_id, 'amount' => (float) $a->amount])
            ->values()
            ->all();

        $existingJobAllocations = $moneyReceipt->allocations
            ->whereNull('bill_id')
            ->whereNotNull('job_id')
            ->map(fn ($a) => ['job_id' => $a->job_id, 'job_no' => $a->job?->job_no, 'amount' => (float) $a->amount])
            ->values()
            ->all();

        return view('chevron.cnf.money-receipts.create', array_merge(
            $this->formData(),
            [
                'receipt'                => $moneyReceipt,
                'existingItems'          => old('items', $existingItems),
                'existingAllocations'    => old('allocations') ? array_values(old('allocations')) : $existingAllocations,
                'existingJobAllocations' => old('job_allocations') ? $this->withJobNumbers(old('job_allocations')) : $existingJobAllocations,
            ]
        ));
    }

    public function update(UpdateMoneyReceiptRequest $request, ChevronMoneyReceipt $moneyReceipt)
    {
        $this->persist($request, $moneyReceipt);

        return redirect()->route('chevron.cnf.money-receipts.index')
            ->with('success', 'Money receipt updated successfully.');
    }

    public function destroy(DestroyMoneyReceiptRequest $request, ChevronMoneyReceipt $moneyReceipt): JsonResponse
    {
        $this->guardBranch($moneyReceipt);

        if (! $moneyReceipt->isEditable()) {
            return response()->json(['message' => $moneyReceipt->status.' receipts cannot be deleted.'], 422);
        }

        try {
            DB::transaction(function () use ($moneyReceipt) {
                if ($moneyReceipt->party_id) {
                    ChevronCustomer::whereKey($moneyReceipt->party_id)->lockForUpdate()->first();
                }

                $billIds = $moneyReceipt->allocations()->whereNotNull('bill_id')->pluck('bill_id')->unique()->sort()->values();
                $bills = ChevronBill::whereIn('id', $billIds)->orderBy('id')->lockForUpdate()->get();

                $moneyReceipt->allocations()->delete();
                $moneyReceipt->delete();

                $bills->each->syncReceivedAmount();

                if ($moneyReceipt->party_id) {
                    if (ChevronMoneyReceipt::advanceBalance((int) session('active_branch_id'), (int) $moneyReceipt->party_id) < -0.004) {
                        throw ValidationException::withMessages([
                            'receipt' => 'Part of this advance is already adjusted against bills, so the receipt cannot be deleted.',
                        ]);
                    }

                    ChevronCustomer::refreshAdvanceBalance((int) $moneyReceipt->party_id);
                }
            });
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        }

        return response()->json(['message' => 'Money receipt deleted.']);
    }

    public function show(ShowMoneyReceiptRequest $request, ChevronMoneyReceipt $moneyReceipt)
    {
        $moneyReceipt = $this->loadForDisplay($moneyReceipt);

        return view('chevron.cnf.money-receipts.show', [
            'moneyReceipt'  => $moneyReceipt,
            'amountInWords' => NumberHelper::amountInWords((float) $moneyReceipt->total_amount),
            'canEdit'       => $moneyReceipt->isEditable() && (bool) $request->user()?->hasPermission('cnf.money-receipt.edit'),
        ]);
    }

    public function printView(PrintMoneyReceiptRequest $request, ChevronMoneyReceipt $moneyReceipt)
    {
        $moneyReceipt = $this->loadForDisplay($moneyReceipt);

        return view('chevron.cnf.money-receipts.print', [
            'moneyReceipt'  => $moneyReceipt,
            'amountInWords' => NumberHelper::amountInWords((float) $moneyReceipt->total_amount),
            'company'       => Company::where('slug', 'chevron-lines')->first(),
        ]);
    }

    public function searchParties(IndexMoneyReceiptRequest $request)
    {
        $term = $request->q ?? '';
        $results = ChevronCustomer::where('name', 'like', "%{$term}%")
            ->limit(20)
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'text' => $c->name]);

        return response()->json(['results' => $results]);
    }

    /**
     * Outstanding bills of a party. When editing, `receipt_id` adds that receipt's own
     * allocation back so its earlier payment does not reduce what it may collect.
     */
    public function partyBills(IndexMoneyReceiptRequest $request): JsonResponse
    {
        $own = collect();

        if ($request->filled('receipt_id')) {
            $receipt = ChevronMoneyReceipt::where('branch_id', session('active_branch_id'))->find($request->integer('receipt_id'));
            $own = $receipt
                ? $receipt->allocations()->whereNotNull('bill_id')->pluck('amount', 'bill_id')
                : collect();
        }

        $bills = $this->partyBillsQuery($request->integer('party_id') ?: null, (string) $request->party_name)
            ->with('job:id,job_no')
            ->orderBy('bill_date')
            ->orderBy('id')
            ->get()
            ->map(function (ChevronBill $bill) use ($own) {
                $ownAmount = (float) ($own[$bill->id] ?? 0);

                return [
                    'id'              => $bill->id,
                    'bill_no'         => $bill->bill_no,
                    'bill_date'       => $bill->bill_date?->format('d-M-Y'),
                    'job_no'          => $bill->job?->job_no ?? $bill->job_no,
                    'due_amount'      => (float) $bill->due_amount,
                    'received_amount' => round((float) $bill->received_amount - $ownAmount, 2),
                    'balance'         => round($bill->balance + $ownAmount, 2),
                ];
            })
            ->filter(fn ($row) => $row['balance'] > 0)
            ->values();

        return response()->json(['bills' => $bills]);
    }

    /**
     * Advance balance of a customer in the active branch. When editing, `receipt_id` leaves that receipt
     * out, so the amount it already adjusted counts as available again.
     */
    public function partyAdvance(IndexMoneyReceiptRequest $request): JsonResponse
    {
        $partyId = $request->integer('party_id');

        if (! $partyId) {
            return response()->json(['received' => 0, 'adjusted' => 0, 'balance' => 0, 'statement_url' => null]);
        }

        $branchId = (int) session('active_branch_id');
        $except = $request->integer('receipt_id') ?: null;
        $received = ChevronMoneyReceipt::advanceReceived($branchId, $partyId, $except);
        $adjusted = ChevronMoneyReceipt::advanceAdjusted($branchId, $partyId, $except);

        return response()->json([
            'received'      => $received,
            'adjusted'      => $adjusted,
            'balance'       => round($received - $adjusted, 2),
            'statement_url' => route('chevron.cnf.advance-balances.show', $partyId),
        ]);
    }

    public function partyJobs(IndexMoneyReceiptRequest $request): JsonResponse
    {
        $partyId = $request->integer('party_id') ?: null;
        $partyName = (string) $request->party_name;
        $term = trim((string) $request->q);
        $page = max(1, $request->integer('page', 1));
        $perPage = 30;

        $jobs = $this->partyJobsQuery($partyId, $partyName)
            ->when($term !== '', fn (Builder $q) => $q->where('job_no', 'like', "%{$term}%"))
            ->orderByDesc('id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage + 1)
            ->get(['id', 'job_no']);

        return response()->json([
            'results'    => $jobs->take($perPage)->map(fn ($j) => ['id' => $j->id, 'text' => $j->job_no])->values(),
            'pagination' => ['more' => $jobs->count() > $perPage],
        ]);
    }

    /**
     * Create or update a receipt together with its payment rows and allocations, enforcing
     * that a Bill receipt never collects more than a bill's outstanding balance.
     */
    private function persist(StoreMoneyReceiptRequest $request, ?ChevronMoneyReceipt $receipt = null): ChevronMoneyReceipt
    {
        return DB::transaction(function () use ($request, $receipt) {
            $type = $request->receipt_type;
            $partyId = $request->party_id ? (int) $request->party_id : null;
            $totalAmount = round((float) collect($request->items)->sum('amount'), 2);
            $allocations = collect($request->allocations ?? []);

            $branchId = (int) session('active_branch_id');
            $adjustmentTotal = round((float) collect($request->items)
                ->filter(fn ($item) => ($item['payment_type'] ?? '') === ChevronMoneyReceipt::ADVANCE_ADJUSTMENT)
                ->sum('amount'), 2);

            $previousBillIds = [];
            if ($receipt) {
                $receipt = ChevronMoneyReceipt::lockForUpdate()->findOrFail($receipt->id);
                $this->guardEditable($receipt);
                $previousBillIds = $receipt->allocations()->whereNotNull('bill_id')->pluck('bill_id')->all();
            }

            // Customer rows are the lock that serialises every change to a customer's advance balance.
            $partyIds = collect([$receipt?->party_id, $partyId])->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values();
            ChevronCustomer::whereIn('id', $partyIds)->orderBy('id')->lockForUpdate()->get();

            if ($adjustmentTotal > 0) {
                if ($type !== ChevronMoneyReceipt::TYPE_BILL) {
                    throw ValidationException::withMessages(['items' => 'Advance adjustment can only be used when collecting against bills.']);
                }

                if (! $partyId) {
                    throw ValidationException::withMessages(['party_name' => 'Select the party from the list to use its advance balance.']);
                }

                $available = ChevronMoneyReceipt::advanceBalance($branchId, $partyId, $receipt?->id);

                if (round($adjustmentTotal - $available, 2) > 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Advance adjustment ('.number_format($adjustmentTotal, 2).') exceeds the available advance balance ('.number_format(max($available, 0), 2).').',
                    ]);
                }
            }

            $requestedBillIds = $type === ChevronMoneyReceipt::TYPE_BILL
                ? $allocations->pluck('bill_id')->map(fn ($id) => (int) $id)->all()
                : [];

            $lockIds = collect($previousBillIds)->merge($requestedBillIds)->unique()->sort()->values();
            $bills = ChevronBill::whereIn('id', $lockIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($receipt) {
                $receipt->allocations()->delete();
                $bills->each->syncReceivedAmount();
            }

            $payableAmount = 0.0;
            $allocationRows = [];

            if ($type === ChevronMoneyReceipt::TYPE_BILL) {
                $allowedBillIds = $this->partyBillsQuery($partyId, $request->party_name)
                    ->whereIn('id', $requestedBillIds)
                    ->pluck('id')
                    ->flip();

                $allocatedTotal = 0.0;

                foreach ($allocations as $key => $row) {
                    $bill = $bills->get((int) $row['bill_id']);

                    if (! $bill || ! $allowedBillIds->has($bill->id)) {
                        throw ValidationException::withMessages([
                            "allocations.{$key}.bill_id" => 'This bill is not available for the selected party.',
                        ]);
                    }

                    $amount = round((float) $row['amount'], 2);

                    if (round($amount - $bill->balance, 2) > 0) {
                        throw ValidationException::withMessages([
                            "allocations.{$key}.amount" => $bill->bill_no.': cannot collect more than the bill balance of '.number_format($bill->balance, 2).'.',
                        ]);
                    }

                    $allocatedTotal += $amount;
                    $payableAmount += $bill->balance;
                    $allocationRows[] = ['bill_id' => $bill->id, 'job_id' => $bill->job_id, 'amount' => $amount];
                }

                if (round($allocatedTotal - $totalAmount, 2) !== 0.0) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Collected total ('.number_format($totalAmount, 2).') must equal the amount allocated to bills ('.number_format($allocatedTotal, 2).').',
                    ]);
                }
            } else {
                $jobAllocations = $request->advance_mode === 'general'
                    ? collect()
                    : collect($request->job_allocations ?? []);
                $requestedJobIds = $jobAllocations->pluck('job_id')->map(fn ($id) => (int) $id)->all();
                $allowedJobIds = $this->partyJobsQuery($partyId, $request->party_name)
                    ->whereIn('id', $requestedJobIds)
                    ->pluck('id')
                    ->flip();

                $assignedTotal = 0.0;

                foreach ($jobAllocations as $key => $row) {
                    if (! $allowedJobIds->has((int) $row['job_id'])) {
                        throw ValidationException::withMessages([
                            "job_allocations.{$key}.job_id" => 'This job is not available for the selected party.',
                        ]);
                    }

                    $amount = round((float) $row['amount'], 2);
                    $assignedTotal += $amount;
                    $allocationRows[] = ['bill_id' => null, 'job_id' => (int) $row['job_id'], 'amount' => $amount];
                }

                $unassigned = round($totalAmount - $assignedTotal, 2);

                if ($unassigned < 0) {
                    throw ValidationException::withMessages([
                        'job_allocations' => 'Job-wise advance ('.number_format($assignedTotal, 2).') cannot exceed the collected total ('.number_format($totalAmount, 2).').',
                    ]);
                }

                if ($unassigned > 0) {
                    $allocationRows[] = ['bill_id' => null, 'job_id' => null, 'amount' => $unassigned];
                }
            }

            $attributes = [
                'receipt_date'   => $request->receipt_date,
                'receipt_type'   => $type,
                'party_id'       => $partyId,
                'party_name'     => $request->party_name,
                'pay_type'       => $request->pay_type,
                'payable_amount' => round($payableAmount, 2),
                'total_amount'   => $totalAmount,
                'description'    => $request->description,
            ];

            if ($receipt) {
                $receipt->update($attributes);
                $receipt->items()->delete();
            } else {
                $receipt = ChevronMoneyReceipt::create($attributes + [
                    'receipt_no' => ChevronMoneyReceipt::generateReceiptNo(),
                    'branch_id'  => session('active_branch_id'),
                    'status'     => 'Active',
                ]);
            }

            foreach ($request->items as $item) {
                $isAdjustment = $item['payment_type'] === ChevronMoneyReceipt::ADVANCE_ADJUSTMENT;

                $receipt->items()->create([
                    'payment_type'       => $item['payment_type'],
                    'account_id'         => $isAdjustment ? null : ($item['account_id'] ?? null),
                    'account_no'         => $isAdjustment ? null : ($item['account_no'] ?? null),
                    'cheque_card_holder' => $isAdjustment ? null : ($item['cheque_card_holder'] ?? null),
                    'cheque_card_no'     => $isAdjustment ? null : ($item['cheque_card_no'] ?? null),
                    'amount'             => $item['amount'],
                    'cheque_date'        => $isAdjustment ? null : ($item['cheque_date'] ?? null),
                ]);
            }

            $receipt->allocations()->createMany($allocationRows);

            $bills->each->syncReceivedAmount();

            foreach ($partyIds as $affectedPartyId) {
                $balance = ChevronMoneyReceipt::advanceBalance($branchId, $affectedPartyId);

                if ($balance < -0.004) {
                    throw ValidationException::withMessages([
                        'items' => 'This change would leave the customer\'s advance balance at '.number_format($balance, 2).'. Part of the advance is already adjusted against bills.',
                    ]);
                }

                ChevronCustomer::refreshAdvanceBalance($affectedPartyId);
            }

            return $receipt;
        });
    }

    /**
     * Bills of the active branch that belong to the party: linked to one of its jobs, or billed under its name.
     *
     * @return Builder<ChevronBill>
     */
    private function partyBillsQuery(?int $partyId, string $partyName): Builder
    {
        return ChevronBill::query()
            ->where('branch_id', session('active_branch_id'))
            ->where(function (Builder $q) use ($partyId, $partyName) {
                $q->where('party_name', $partyName);

                if ($partyId) {
                    $q->orWhereIn('job_id', ChevronJob::where('customer_id', $partyId)->select('id'));
                }
            });
    }

    /**
     * @return Builder<ChevronJob>
     */
    private function partyJobsQuery(?int $partyId, string $partyName): Builder
    {
        return ChevronJob::query()
            ->where('branch_id', session('active_branch_id'))
            ->where(function (Builder $q) use ($partyId, $partyName) {
                $q->where('party_name', $partyName);

                if ($partyId) {
                    $q->orWhere('customer_id', $partyId);
                }
            });
    }

    private function referenceLabel(ChevronMoneyReceipt $receipt): string
    {
        if ($receipt->receipt_type === ChevronMoneyReceipt::TYPE_BILL) {
            return $receipt->allocations
                ->filter(fn ($a) => $a->bill?->bill_no)
                ->map(fn ($a) => $a->bill->bill_no.($a->job?->job_no ? ' ('.$a->job->job_no.')' : ''))
                ->implode(', ');
        }

        if ($receipt->receipt_type === ChevronMoneyReceipt::TYPE_ADVANCE) {
            $jobNos = $receipt->allocations->pluck('job.job_no')->filter()->implode(', ');

            if ($jobNos === '') {
                return 'General advance';
            }

            return $receipt->allocations->contains(fn ($a) => $a->job_id === null) ? $jobNos.' + General' : $jobNos;
        }

        return '—';
    }

    /**
     * Attach job numbers to submitted job allocation rows so the form can redraw them after a validation error.
     *
     * @param  array<int|string, array{job_id?: mixed, amount?: mixed}>  $rows
     * @return array<int, array{job_id: int, job_no: string|null, amount: mixed}>
     */
    private function withJobNumbers(array $rows): array
    {
        $jobNos = ChevronJob::whereIn('id', collect($rows)->pluck('job_id')->filter())->pluck('job_no', 'id');

        return collect($rows)
            ->map(fn ($row) => [
                'job_id' => (int) ($row['job_id'] ?? 0),
                'job_no' => $jobNos[(int) ($row['job_id'] ?? 0)] ?? null,
                'amount' => $row['amount'] ?? '',
            ])
            ->values()
            ->all();
    }

    private function loadForDisplay(ChevronMoneyReceipt $receipt): ChevronMoneyReceipt
    {
        $this->guardBranch($receipt);

        return $receipt->load(['branch', 'items.account', 'allocations.bill', 'allocations.job']);
    }

    private function guardBranch(ChevronMoneyReceipt $receipt): void
    {
        abort_unless((int) $receipt->branch_id === (int) session('active_branch_id'), 404);
    }

    private function guardEditable(ChevronMoneyReceipt $receipt): void
    {
        $this->guardBranch($receipt);

        abort_unless($receipt->isEditable(), 403, $receipt->status.' receipts cannot be changed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $accounts = ChevronAccount::where('is_active', true)
            ->orderBy('account_no')
            ->get(['id', 'account_no', 'account_name', 'account_type'])
            ->toArray();

        return [
            'payTypes'               => ChevronMoneyReceipt::payTypes(),
            'rowPayTypes'            => ChevronMoneyReceipt::rowPayTypes(),
            'receiptTypes'           => ChevronMoneyReceipt::receiptTypes(),
            'accounts'               => $accounts,
            'existingItems'          => [],
            'existingAllocations'    => old('allocations') ? array_values(old('allocations')) : [],
            'existingJobAllocations' => old('job_allocations') ? $this->withJobNumbers(old('job_allocations')) : [],
            'receipt'                => null,
        ];
    }
}
