<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingCustomer;
use App\Models\NasTrading\NasTradingLc;
use App\Models\NasTrading\NasTradingLcBillStatement;
use App\Models\NasTrading\NasTradingLcBillStatementItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LcBillStatementController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            return DataTables::of(NasTradingLcBillStatement::with('customer')->latest())
                ->addIndexColumn()
                ->editColumn('bill_date', fn($r) => $r->bill_date?->format('d-M-Y'))
                ->addColumn('customer_name', fn($r) => $r->customer?->company_name ?? '-')
                ->addColumn('status_badge', fn($r) => match ($r->status) {
                    'Draft'     => '<span class="badge bg-secondary">Draft</span>',
                    'Confirmed' => '<span class="badge bg-success">Confirmed</span>',
                    'Paid'      => '<span class="badge bg-primary">Paid</span>',
                    default     => $r->status,
                })
                ->addColumn('action', fn($r) => '<a href="' . route('nas-trading.lc-bill-statements.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-eye"></i></a> ' .
                    ($r->status === 'Draft' ? '<a href="' . route('nas-trading.lc-bill-statements.edit', $r->id) . '" class="btn btn-sm btn-outline-primary" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-edit"></i></a> ' : '') .
                    ($r->status === 'Draft' ? '<button class="btn btn-sm btn-outline-success btn-confirm" data-url="' . route('nas-trading.lc-bill-statements.confirm', $r->id) . '" style="padding:2px 6px;font-size:.7rem" title="Confirm"><i class="fa fa-check"></i></button> ' : '') .
                    ($r->status === 'Draft' ? '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.lc-bill-statements.destroy', $r->id) . '" data-name="' . e($r->bill_no) . '" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-trash"></i></button>' : ''))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-trading.lc-bill-statements.index');
    }

    public function create()
    {
        return view('nas-trading.lc-bill-statements.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'bill_date'   => 'required|date',
            'lc_ids'      => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $statement = NasTradingLcBillStatement::create([
                'branch_id'   => session('nas_trading_branch_id'),
                'bill_no'     => NasTradingLcBillStatement::generateBillNo(),
                'customer_id' => $request->customer_id,
                'bill_date'   => $request->bill_date,
                'status'      => 'Draft',
                'note'        => $request->note,
                'enclosed'    => $request->enclosed,
                'created_by'  => auth()->id(),
            ]);

            foreach ($request->lc_ids as $index => $lcId) {
                $statement->items()->create([
                    'lc_id'         => $lcId,
                    'serial_number' => NasTradingLcBillStatementItem::generateSerialNo(),
                    'sort_order'    => $index + 1,
                ]);
            }

        });

        return response()->json(['message' => 'LC Bill Statement created successfully.', 'redirect' => route('nas-trading.lc-bill-statements.index')]);
    }

    public function show(NasTradingLcBillStatement $lcBillStatement)
    {
        $lcBillStatement->load(['customer', 'items.lc']);

        return view('nas-trading.lc-bill-statements.show', compact('lcBillStatement'));
    }

    public function edit(NasTradingLcBillStatement $lcBillStatement)
    {

        if ($lcBillStatement->status !== 'Draft') {
            return redirect()->route('nas-trading.lc-bill-statements.show', $lcBillStatement->id)
                ->with('error', 'Only Draft statements can be edited.');
        }

        $lcBillStatement->load(['customer', 'items.lc']);

        return view('nas-trading.lc-bill-statements.edit', compact('lcBillStatement'));
    }

    public function update(Request $request, NasTradingLcBillStatement $lcBillStatement)
    {
        $request->validate([
            'customer_id' => 'required',
            'bill_date'   => 'required|date',
            'lc_ids'      => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $lcBillStatement) {
            $lcBillStatement->update([
                'customer_id' => $request->customer_id,
                'bill_date'   => $request->bill_date,
                'note'        => $request->note,
                'enclosed'    => $request->enclosed,
            ]);

            $lcBillStatement->items()->delete();

            foreach ($request->lc_ids as $index => $lcId) {
                $lcBillStatement->items()->create([
                    'lc_id'         => $lcId,
                    'serial_number' => NasTradingLcBillStatementItem::generateSerialNo(),
                    'sort_order'    => $index + 1,
                ]);
            }

        });

        return response()->json(['message' => 'LC Bill Statement updated successfully.', 'redirect' => route('nas-trading.lc-bill-statements.show', $lcBillStatement->id)]);
    }

    public function confirm(NasTradingLcBillStatement $lcBillStatement)
    {
        $lcBillStatement->update(['status' => 'Confirmed']);

        return response()->json(['message' => 'Statement confirmed successfully.']);
    }

    public function destroy(NasTradingLcBillStatement $lcBillStatement)
    {

        if ($lcBillStatement->status !== 'Draft') {
            return response()->json(['message' => 'Only Draft statements can be deleted.'], 422);
        }

        $lcBillStatement->items()->delete();
        $lcBillStatement->delete();

        return response()->json(['message' => 'Statement deleted.']);
    }

    public function printCnfDues(NasTradingLcBillStatement $lcBillStatement)
    {
        $lcBillStatement->load(['customer', 'items.lc']);

        return view('nas-trading.lc-bill-statements.prints.cnf-dues', compact('lcBillStatement'));
    }

    public function printCommissionBill(NasTradingLcBillStatement $lcBillStatement)
    {
        $lcBillStatement->load(['customer', 'items.lc']);

        return view('nas-trading.lc-bill-statements.prints.commission-bill-all', compact('lcBillStatement'));
    }

    public function printCommissionBillItem(NasTradingLcBillStatement $lcBillStatement, NasTradingLcBillStatementItem $item)
    {
        $lcBillStatement->load('customer');
        $item->load('lc');

        return view('nas-trading.lc-bill-statements.prints.commission-bill', compact('lcBillStatement', 'item'));
    }

    public function printCommissionStatement(NasTradingLcBillStatement $lcBillStatement)
    {
        $lcBillStatement->load(['customer', 'items.lc']);

        return view('nas-trading.lc-bill-statements.prints.commission-statement', compact('lcBillStatement'));
    }

    public function printBillStatement(NasTradingLcBillStatement $lcBillStatement)
    {
        $lcBillStatement->load(['customer', 'items.lc.customerBill']);

        return view('nas-trading.lc-bill-statements.prints.bill-statement', compact('lcBillStatement'));
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');

        return response()->json(
            NasTradingCustomer::where('status', 'Active')
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name', 'address'])
                ->map(fn($c) => ['id' => $c->id, 'text' => $c->code . ' | ' . $c->company_name, 'address' => $c->address])
        );
    }

    public function searchLcs(Request $request)
    {
        $term       = $request->input('q', '');
        $customerId = $request->input('customer_id');

        return response()->json(
            NasTradingLc::when($customerId, fn($q) => $q->where('customer_id', $customerId))
                ->when($term, fn($q) => $q->where(fn($q) => $q->where('lc_no', 'like', "%{$term}%")->orWhere('pfi_no', 'like', "%{$term}%")->orWhere('lc_no_system', 'like', "%{$term}%")))
                ->orderBy('lc_open_date', 'desc')
                ->get(['id', 'lc_no_system', 'lc_no', 'pfi_no', 'lc_open_date', 'lc_rt_value', 'lc_commission_percent', 'lc_commission_flat', 'lc_retirement_date'])
                ->map(fn($l) => [
                    'id'                    => $l->id,
                    'text'                  => $l->lc_no_system . ' | ' . $l->lc_no . ' | ' . $l->pfi_no,
                    'lc_no'                 => $l->lc_no,
                    'pfi_no'                => $l->pfi_no,
                    'lc_open_date'          => $l->lc_open_date?->format('d-M-Y'),
                    'lc_rt_value'           => $l->lc_rt_value,
                    'lc_commission_percent' => $l->lc_commission_percent,
                    'lc_commission_flat'    => $l->lc_commission_flat,
                    'lc_retirement_date'    => $l->lc_retirement_date?->format('d-M-Y'),
                ])
        );
    }

}
