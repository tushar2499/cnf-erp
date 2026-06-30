<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronAccount;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronMoneyReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MoneyReceiptController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ChevronMoneyReceipt::where('branch_id', session('active_branch_id'));

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('receipt_date', fn($r) => $r->receipt_date?->format('d-M-Y'))
                ->editColumn('total_amount', fn($r) => number_format($r->total_amount, 2))
                ->editColumn('payable_amount', fn($r) => number_format($r->payable_amount, 2))
                ->addColumn('status_badge', function ($r) {
                    return match ($r->status) {
                        'Approved'  => '<span class="badge bg-success">Approved</span>',
                        'Submitted' => '<span class="badge bg-warning text-dark">Submitted</span>',
                        default     => '<span class="badge bg-primary">Active</span>',
                    };
                })
                ->addColumn('action', fn($r) => '
                    <a href="' . route('chevron.cnf.money-receipts.edit', $r->id) . '" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'  . route('chevron.cnf.money-receipts.destroy', $r->id) . '"
                        data-name="' . e($r->receipt_no) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.cnf.money-receipts.index');
    }

    public function create()
    {
        return view('chevron.cnf.money-receipts.create', $this->formData());
    }

    public function store(Request $request)
    {
        $request->validate([
            'receipt_date'         => ['required', 'date'],
            'party_name'           => ['required', 'string', 'max:255'],
            'pay_type'             => ['required', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.payment_type' => ['required', 'string'],
            'items.*.amount'       => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = collect($request->items)->sum('amount');

            $receipt = ChevronMoneyReceipt::create([
                'receipt_no'     => ChevronMoneyReceipt::generateReceiptNo(),
                'branch_id'      => session('active_branch_id'),
                'receipt_date'   => $request->receipt_date,
                'party_id'       => $request->party_id ?: null,
                'party_name'     => $request->party_name,
                'pay_type'       => $request->pay_type,
                'payable_amount' => $request->payable_amount ?? 0,
                'total_amount'   => $totalAmount,
                'description'    => $request->description,
                'status'         => 'Active',
            ]);

            foreach ($request->items as $item) {
                $receipt->items()->create([
                    'payment_type'       => $item['payment_type'],
                    'account_id'         => $item['account_id'] ?: null,
                    'account_no'         => $item['account_no'] ?? null,
                    'cheque_card_holder' => $item['cheque_card_holder'] ?? null,
                    'cheque_card_no'     => $item['cheque_card_no'] ?? null,
                    'amount'             => $item['amount'],
                    'cheque_date'        => $item['cheque_date'] ?: null,
                ]);
            }
        });

        return redirect()->route('chevron.cnf.money-receipts.index')
            ->with('success', 'Money receipt created successfully.');
    }

    public function edit(ChevronMoneyReceipt $moneyReceipt)
    {
        $existingItems = $moneyReceipt->items->map(fn($i) => [
            'payment_type'       => $i->payment_type,
            'account_id'         => $i->account_id,
            'account_no'         => $i->account_no,
            'cheque_card_holder' => $i->cheque_card_holder,
            'cheque_card_no'     => $i->cheque_card_no,
            'amount'             => $i->amount,
            'cheque_date'        => $i->cheque_date?->format('Y-m-d'),
        ])->values()->toArray();

        return view('chevron.cnf.money-receipts.create', array_merge(
            $this->formData(),
            ['receipt' => $moneyReceipt, 'existingItems' => $existingItems]
        ));
    }

    public function update(Request $request, ChevronMoneyReceipt $moneyReceipt)
    {
        $request->validate([
            'receipt_date'         => ['required', 'date'],
            'party_name'           => ['required', 'string', 'max:255'],
            'pay_type'             => ['required', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.payment_type' => ['required', 'string'],
            'items.*.amount'       => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($request, $moneyReceipt) {
            $totalAmount = collect($request->items)->sum('amount');

            $moneyReceipt->update([
                'receipt_date'   => $request->receipt_date,
                'party_id'       => $request->party_id ?: null,
                'party_name'     => $request->party_name,
                'pay_type'       => $request->pay_type,
                'payable_amount' => $request->payable_amount ?? 0,
                'total_amount'   => $totalAmount,
                'description'    => $request->description,
            ]);

            $moneyReceipt->items()->delete();

            foreach ($request->items as $item) {
                $moneyReceipt->items()->create([
                    'payment_type'       => $item['payment_type'],
                    'account_id'         => $item['account_id'] ?: null,
                    'account_no'         => $item['account_no'] ?? null,
                    'cheque_card_holder' => $item['cheque_card_holder'] ?? null,
                    'cheque_card_no'     => $item['cheque_card_no'] ?? null,
                    'amount'             => $item['amount'],
                    'cheque_date'        => $item['cheque_date'] ?: null,
                ]);
            }
        });

        return redirect()->route('chevron.cnf.money-receipts.index')
            ->with('success', 'Money receipt updated successfully.');
    }

    public function destroy(ChevronMoneyReceipt $moneyReceipt)
    {
        $moneyReceipt->delete();
        return response()->json(['message' => 'Money receipt deleted.']);
    }

    public function searchParties(Request $request)
    {
        $term = $request->q ?? '';
        $results = ChevronCustomer::where('name', 'like', "%{$term}%")
            ->limit(20)
            ->get(['id', 'name'])
            ->map(fn($c) => ['id' => $c->id, 'text' => $c->name]);

        return response()->json(['results' => $results]);
    }

    public function getPartyPayable(Request $request)
    {
        $partyName = $request->party_name ?? '';
        $payable = ChevronBill::where('party_name', $partyName)->sum('due_amount');
        return response()->json(['payable_amount' => round($payable, 2)]);
    }

    private function formData(): array
    {
        $accounts = ChevronAccount::where('is_active', true)
            ->orderBy('account_no')
            ->get(['id', 'account_no', 'account_name', 'account_type'])
            ->toArray();

        return [
            'payTypes'    => ChevronMoneyReceipt::payTypes(),
            'rowPayTypes' => ChevronMoneyReceipt::rowPayTypes(),
            'accounts'    => $accounts,
            'existingItems' => [],
            'receipt'     => null,
        ];
    }
}
