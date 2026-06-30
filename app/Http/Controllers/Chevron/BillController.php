<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronBillItem;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BillController extends Controller
{
    private function formData(): array
    {
        $heads = ChevronExpenseHead::where('is_active', true)->orderBy('name')->get();
        return [
            'billTypes'          => ChevronBill::billTypes(),
            'commissionOnOptions'=> ChevronBill::commissionOnOptions(),
            'expenseCategories'  => ChevronExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'expenseHeads'       => $heads,
            'expenseHeadsJson'   => $heads->map(fn($h) => ['id' => $h->id, 'name' => $h->name, 'cat' => $h->expense_category_id])->values(),
            'today'              => now()->format('Y-m-d'),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ChevronBill::where('branch_id', session('active_branch_id'));
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bill_date',     fn($r) => $r->bill_date?->format('d M Y')     ?? '—')
                ->editColumn('delivery_date', fn($r) => $r->delivery_date?->format('d M Y') ?? '—')
                ->addColumn('sub_total_fmt',  fn($r) => number_format($r->sub_total, 2))
                ->addColumn('net_payable_fmt',fn($r) => number_format($r->net_payable, 2))
                ->addColumn('due_amount_fmt', fn($r) => number_format($r->due_amount, 2))
                ->addColumn('status_badge', fn($r) => match ($r->status) {
                    'Submitted' => '<span class="badge bg-warning text-dark">Submitted</span>',
                    'Approved'  => '<span class="badge bg-success">Approved</span>',
                    default     => '<span class="badge bg-primary">Active</span>',
                })
                ->addColumn('action', fn($r) => '
                    <a href="' . route('chevron.cnf.bills.edit', $r->id) . '" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete"
                        data-url="' . route('chevron.cnf.bills.destroy', $r->id) . '"
                        data-name="' . e($r->bill_no) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('chevron.cnf.bills.index');
    }

    public function create()
    {
        return view('chevron.cnf.bills.create', array_merge($this->formData(), [
            'bill'         => null,
            'existingRows' => [],
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bill_date' => ['required', 'date'],
            'rows'      => ['required', 'array', 'min:1'],
            'rows.*.expense_category_id' => ['required'],
        ]);

        DB::transaction(function () use ($request) {
            $bill = ChevronBill::create(array_merge($this->prepareData($request), [
                'bill_no' => ChevronBill::generateBillNo(),
            ]));
            $this->saveItems($bill, $request->rows);
        });

        return redirect()->route('chevron.cnf.bills.index')
            ->with('success', 'Bill created successfully.');
    }

    public function edit(ChevronBill $bill)
    {
        $bill->load('items');
        $existingRows = $bill->items->map(fn($i) => [
            'expense_category_id' => $i->expense_category_id,
            'expense_head_id'     => $i->expense_head_id,
            'amount'              => $i->amount,
            'note'                => $i->note,
        ])->values();
        return view('chevron.cnf.bills.create', array_merge($this->formData(), [
            'bill'         => $bill,
            'existingRows' => $existingRows,
        ]));
    }

    public function update(Request $request, ChevronBill $bill)
    {
        $request->validate([
            'bill_date' => ['required', 'date'],
            'rows'      => ['required', 'array', 'min:1'],
            'rows.*.expense_category_id' => ['required'],
        ]);

        DB::transaction(function () use ($request, $bill) {
            $bill->update($this->prepareData($request));
            $bill->items()->delete();
            $this->saveItems($bill, $request->rows);
        });

        return back()->with('success', 'Bill ' . $bill->bill_no . ' updated successfully.');
    }

    public function destroy(ChevronBill $bill)
    {
        $bill->delete();
        return response()->json(['message' => 'Bill deleted.']);
    }

    public function searchJobs(Request $request)
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
                'assessable_value_bdt', 'invoice_value_2',
            ]);

        return response()->json($jobs->map(fn($j) => [
            'id'                => $j->id,
            'text'              => $j->job_no . ' — ' . $j->party_name,
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
            'be_date'           => $j->be_date,
            'invoice_no'        => $j->invoice_no,
            'invoice_ref'       => $j->hbi_hawb_no,
            'invoice_date'      => $j->invoice_date,
            'bl_no'             => $j->bl_no,
            'bl_ref'            => $j->mbl_mawb_no,
            'assessable_value'  => $j->assessable_value_bdt,
            'invoice_value_bdt' => $j->invoice_value_2,
        ]));
    }

    private function prepareData(Request $request): array
    {
        $subTotal       = (float) $request->sub_total;
        $commRate       = (float) $request->commission_rate;
        $commOn         = $request->commission_on ?? 'ASSESSABLE';
        $assessable     = (float) $request->assessable_value;
        $invoiceBdt     = (float) $request->invoice_value_bdt;
        $base           = $commOn === 'INVOICE VALUE' ? $invoiceBdt : $assessable;
        $commAmt        = round($base * $commRate / 100, 2);
        $totalPayable   = round($subTotal + $commAmt, 2);
        $lessDuty       = (float) $request->less_customs_duty_tax;
        $incomeTax      = (float) $request->income_tax_cnf_com;
        $netPayable     = round($totalPayable - $lessDuty - $incomeTax, 2);
        $advance        = (float) $request->advance_amount;
        $dueAmount      = round($netPayable - $advance, 2);

        return [
            'bill_type'            => $request->bill_type,
            'bill_date'            => $request->bill_date,
            'delivery_date'        => $request->delivery_date ?: null,
            'job_id'               => $request->job_id ?: null,
            'job_no'               => $request->job_no,
            'party_name'           => $request->party_name,
            'party_address'        => $request->party_address,
            'goods_description'    => $request->goods_description,
            'mate_code'            => $request->mate_code,
            'po_no'                => $request->po_no,
            'quantity'             => $request->quantity ?: null,
            'quantity_unit'        => $request->quantity_unit ?: 'KG',
            'quantity_remark'      => $request->quantity_remark,
            'gross_weight'         => $request->gross_weight ?: null,
            'gross_weight_unit'    => $request->gross_weight_unit,
            'lc_no'                => $request->lc_no,
            'lc_ref'               => $request->lc_ref,
            'be_no'                => $request->be_no,
            'be_date'              => $request->be_date ?: null,
            'invoice_no'           => $request->invoice_no,
            'invoice_ref'          => $request->invoice_ref,
            'invoice_date'         => $request->invoice_date ?: null,
            'bl_no'                => $request->bl_no,
            'bl_ref'               => $request->bl_ref,
            'assessable_value'     => $assessable ?: null,
            'invoice_value_bdt'    => $invoiceBdt ?: null,
            'remarks'              => $request->remarks,
            'sub_total'            => $subTotal,
            'commission_on'        => $commOn,
            'commission_rate'      => $commRate ?: null,
            'commission_amount'    => $commAmt,
            'total_payable'        => $totalPayable,
            'less_customs_duty_tax'=> $lessDuty,
            'income_tax_cnf_com'   => $incomeTax,
            'net_payable'          => $netPayable,
            'advance_amount'       => $advance,
            'due_amount'           => $dueAmount,
            'status'               => ($advance > 0 && $dueAmount <= 0) ? 'Approved' : ($advance > 0 ? 'Submitted' : 'Active'),
            'branch_id'            => session('active_branch_id'),
        ];
    }

    private function saveItems(ChevronBill $bill, array $rows): void
    {
        foreach ($rows as $row) {
            $bill->items()->create([
                'expense_category_id' => $row['expense_category_id'] ?: null,
                'expense_head_id'     => $row['expense_head_id'] ?: null,
                'amount'              => $row['amount'] ?? 0,
                'note'                => $row['note'] ?? null,
            ]);
        }
    }
}
