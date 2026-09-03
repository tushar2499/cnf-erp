<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasTrading\StoreNasTradingLcRequest;
use App\Models\NasTrading\NasTradingBank;
use App\Models\NasTrading\NasTradingCustomer;
use App\Models\NasTrading\NasTradingExpenseHead;
use App\Models\NasTrading\NasTradingImporter;
use App\Models\NasTrading\NasTradingLc;
use App\Models\NasTrading\NasTradingPort;
use App\Models\NasTrading\NasTradingPsiCompany;
use App\Models\NasTrading\NasTradingSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LcController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NasTradingLc::latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('lc_open_date', fn ($r) => $r->lc_open_date?->format('d-M-Y'))
                ->addColumn('status_badge', fn ($r) => match ($r->lc_status) {
                    'Open'      => '<span class="badge bg-success">Open</span>',
                    'Closed'    => '<span class="badge bg-secondary">Closed</span>',
                    'Cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                    'Amended'   => '<span class="badge bg-warning text-dark">Amended</span>',
                    default     => $r->lc_status,
                })
                ->addColumn('action', fn ($r) => '<a href="'.route('nas-trading.lcs.show', $r->id).'" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-eye"></i></a> '.
                    '<a href="'.route('nas-trading.lcs.edit', $r->id).'" class="btn btn-sm btn-outline-primary" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-edit"></i></a> '.
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="'.route('nas-trading.lcs.destroy', $r->id).'" data-name="'.e($r->lc_no_system).'" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-trading.lcs.index');
    }

    public function create()
    {
        $banks = NasTradingBank::where('status', 'Active')->get();
        $importers = NasTradingImporter::where('status', 'Active')->get();
        $psiCompanies = NasTradingPsiCompany::where('status', 'Active')->get();
        $ports = NasTradingPort::where('status', 'Active')->get();

        return view('nas-trading.lcs.create', compact('banks', 'importers', 'psiCompanies', 'ports'));
    }

    public function store(StoreNasTradingLcRequest $request)
    {
        DB::transaction(function () use ($request) {
            $lc = NasTradingLc::create(array_merge(
                ['lc_no_system' => NasTradingLc::generateLcNo(), 'created_by' => auth()->user()?->id],
                $request->except(['_token', 'items', 'payments', 'other_charge_items', 'invoices', 'bill_of_entries', 'rt_values', 'bill_paid'])
            ));

            foreach ($request->input('rt_values', []) as $rtv) {
                if (isset($rtv['amount']) && $rtv['amount'] !== '') {
                    $lc->rtValues()->create(['amount' => $rtv['amount']]);
                }
            }

            foreach ($request->input('items', []) as $item) {
                if (! empty($item['product_name'])) {
                    $lc->items()->create($item);
                }
            }

            foreach ($request->input('payments', []) as $payment) {
                if (! empty($payment['amount'])) {
                    $lc->payments()->create($payment);
                }
            }

            foreach ($request->input('other_charge_items', []) as $charge) {
                if (! empty($charge['name'])) {
                    $lc->otherChargeItems()->create($charge);
                }
            }

            foreach ($request->input('invoices', []) as $invoice) {
                if (! empty($invoice['invoice_no']) || ! empty($invoice['invoice_value'])) {
                    $lc->invoiceValues()->create($invoice);
                }
            }

            foreach ($request->input('bill_paid', []) as $bp) {
                if (! empty($bp['amount'])) {
                    $lc->billPaids()->create([
                        'date'    => $bp['date'] ?? null,
                        'posting' => $bp['posting'] ?? null,
                        'remarks' => $bp['remarks'] ?? null,
                        'amount'  => $bp['amount'],
                    ]);
                }
            }

            $lc->update(['total_bill_paid' => $lc->billPaids()->sum('amount')]);

            foreach ($request->input('bill_of_entries', []) as $boeData) {
                if (! empty($boeData['be_no'])) {
                    $boe = $lc->billOfEntries()->create([
                        'be_no'                 => $boeData['be_no'],
                        'be_date'               => $boeData['be_date'],
                        'customs_duty'          => $boeData['customs_duty'] ?? null,
                        'customs_duty_posting'  => $boeData['customs_duty_posting'] ?? null,
                        'cnf_party'             => $boeData['cnf_party'] ?? null,
                        'cnf_total_costing'     => $boeData['cnf_total_costing'] ?? null,
                        'cnf_total_posting'     => $boeData['cnf_total_posting'] ?? null,
                    ]);
                    foreach ($boeData['duty_advances'] ?? [] as $daData) {
                        if (! empty($daData['amount'])) {
                            $boe->dutyAdvances()->create([
                                'amount'  => $daData['amount'],
                                'date'    => $daData['date'],
                                'posting' => $daData['posting'] ?? null,
                            ]);
                        }
                    }
                }
            }

        });

        return response()->json(['message' => 'LC created successfully.', 'redirect' => route('nas-trading.lcs.index')]);
    }

    public function show(NasTradingLc $lc)
    {
        $lc->load('items', 'payments', 'otherChargeItems', 'invoiceValues', 'expenses.expenseHead', 'billOfEntries.dutyAdvances', 'rtValues', 'billPaids');
        $banks = NasTradingBank::where('status', 'Active')->get();
        $importers = NasTradingImporter::where('status', 'Active')->get();
        $psiCompanies = NasTradingPsiCompany::where('status', 'Active')->get();
        $ports = NasTradingPort::where('status', 'Active')->get();
        $expenseHeads = NasTradingExpenseHead::where('status', 'Active')->get();

        return view('nas-trading.lcs.show', compact('lc', 'banks', 'importers', 'psiCompanies', 'ports', 'expenseHeads'));
    }

    public function edit(NasTradingLc $lc)
    {
        $lc->load('items', 'payments', 'otherChargeItems', 'invoiceValues', 'billOfEntries.dutyAdvances', 'rtValues', 'billPaids');
        $banks = NasTradingBank::where('status', 'Active')->get();
        $importers = NasTradingImporter::where('status', 'Active')->get();
        $psiCompanies = NasTradingPsiCompany::where('status', 'Active')->get();
        $ports = NasTradingPort::where('status', 'Active')->get();

        return view('nas-trading.lcs.edit', compact('lc', 'banks', 'importers', 'psiCompanies', 'ports'));
    }

    public function update(StoreNasTradingLcRequest $request, NasTradingLc $lc)
    {
        DB::transaction(function () use ($request, $lc) {
            $lc->update($request->except(['_token', '_method', 'items', 'payments', 'other_charge_items', 'invoices', 'bill_of_entries', 'rt_values', 'bill_paid']));

            $lc->rtValues()->delete();
            foreach ($request->input('rt_values', []) as $rtv) {
                if (isset($rtv['amount']) && $rtv['amount'] !== '') {
                    $lc->rtValues()->create(['amount' => $rtv['amount']]);
                }
            }

            $lc->items()->delete();
            foreach ($request->input('items', []) as $item) {
                if (! empty($item['product_name'])) {
                    $lc->items()->create($item);
                }
            }

            $lc->payments()->delete();
            foreach ($request->input('payments', []) as $payment) {
                if (! empty($payment['amount'])) {
                    $lc->payments()->create($payment);
                }
            }

            $lc->otherChargeItems()->delete();
            foreach ($request->input('other_charge_items', []) as $charge) {
                if (! empty($charge['name'])) {
                    $lc->otherChargeItems()->create($charge);
                }
            }

            $lc->invoiceValues()->delete();
            foreach ($request->input('invoices', []) as $invoice) {
                if (! empty($invoice['invoice_no']) || ! empty($invoice['invoice_value'])) {
                    $lc->invoiceValues()->create($invoice);
                }
            }

            $lc->billPaids()->delete();
            foreach ($request->input('bill_paid', []) as $bp) {
                if (! empty($bp['amount'])) {
                    $lc->billPaids()->create([
                        'date'    => $bp['date'] ?? null,
                        'posting' => $bp['posting'] ?? null,
                        'remarks' => $bp['remarks'] ?? null,
                        'amount'  => $bp['amount'],
                    ]);
                }
            }

            $lc->update(['total_bill_paid' => $lc->billPaids()->sum('amount')]);

            $survivingBoeIds = [];
            foreach ($request->input('bill_of_entries', []) as $boeData) {
                if (empty($boeData['be_no'])) {
                    continue;
                }

                $boeFields = [
                    'be_no'                => $boeData['be_no'],
                    'be_date'              => $boeData['be_date'],
                    'customs_duty'         => $boeData['customs_duty'] ?? null,
                    'customs_duty_posting' => $boeData['customs_duty_posting'] ?? null,
                    'cnf_party'            => $boeData['cnf_party'] ?? null,
                    'cnf_total_costing'    => $boeData['cnf_total_costing'] ?? null,
                    'cnf_total_posting'    => $boeData['cnf_total_posting'] ?? null,
                ];

                if (! empty($boeData['id']) && $boe = $lc->billOfEntries()->find((int) $boeData['id'])) {
                    $boe->update($boeFields);
                } else {
                    $boe = $lc->billOfEntries()->create($boeFields);
                }

                $survivingBoeIds[] = $boe->id;

                // Sync duty advances for this BOE
                $survivingDaIds = [];
                foreach ($boeData['duty_advances'] ?? [] as $daData) {
                    if (empty($daData['amount'])) {
                        continue;
                    }
                    $daFields = [
                        'amount'  => $daData['amount'],
                        'date'    => $daData['date'],
                        'posting' => $daData['posting'] ?? null,
                    ];
                    if (! empty($daData['id']) && $da = $boe->dutyAdvances()->find((int) $daData['id'])) {
                        $da->update($daFields);
                    } else {
                        $da = $boe->dutyAdvances()->create($daFields);
                    }
                    $survivingDaIds[] = $da->id;
                }
                if (! empty($survivingDaIds)) {
                    $boe->dutyAdvances()->whereNotIn('id', $survivingDaIds)->delete();
                } else {
                    $boe->dutyAdvances()->delete();
                }
            }

            // Delete BOEs removed from the form (cascade deletes their duty advances)
            if (! empty($survivingBoeIds)) {
                $lc->billOfEntries()->whereNotIn('id', $survivingBoeIds)->delete();
            } else {
                $lc->billOfEntries()->delete();
            }

        });

        return response()->json(['message' => 'LC updated successfully.', 'redirect' => route('nas-trading.lcs.show', $lc->id)]);
    }

    public function destroy(NasTradingLc $lc)
    {
        $lc->items()->delete();
        $lc->expenses()->delete();
        $lc->otherChargeItems()->delete();
        $lc->invoiceValues()->delete();
        foreach ($lc->billOfEntries as $boe) {
            $boe->dutyAdvances()->delete();
        }
        $lc->billOfEntries()->delete();
        $lc->delete();

        return response()->json(['message' => 'LC deleted.']);
    }

    public function generateBill(NasTradingLc $lc)
    {
        $lc->load('items', 'expenses.expenseHead', 'payments', 'billOfEntries.dutyAdvances');
        $expenseHeads = NasTradingExpenseHead::where('status', 'Active')->get();

        return view('nas-trading.customer-bills.generate', compact('lc', 'expenseHeads'));
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');

        return response()->json(
            NasTradingCustomer::where('status', 'Active')
                ->where(fn ($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name', 'address', 'delivery_address'])
                ->map(fn ($c) => ['id' => $c->id, 'text' => $c->code.' | '.$c->company_name, 'address' => $c->address, 'delivery_address' => $c->delivery_address])
        );
    }

    public function searchSuppliers(Request $request)
    {
        $term = $request->input('q', '');

        return response()->json(
            NasTradingSupplier::where('status', 'Active')
                ->where(fn ($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name', 'country', 'currency'])
                ->map(fn ($s) => ['id' => $s->id, 'text' => $s->code.' | '.$s->company_name, 'country' => $s->country, 'currency' => $s->currency])
        );
    }

    public function search(Request $request)
    {
        $term = $request->input('q', '');

        return response()->json(
            NasTradingLc::where(fn ($q) => $q->where('lc_no_system', 'like', "%{$term}%")->orWhere('lc_no', 'like', "%{$term}%")->orWhere('pfi_no', 'like', "%{$term}%")->orWhere('customer_name', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'lc_no_system', 'lc_no', 'pfi_no', 'customer_id', 'customer_name'])
                ->map(fn ($l) => ['id' => $l->id, 'text' => $l->lc_no_system.' | '.$l->pfi_no.' | '.$l->customer_name, 'lc_no' => $l->lc_no, 'pfi_no' => $l->pfi_no, 'customer_id' => $l->customer_id, 'customer_name' => $l->customer_name])
        );
    }
}
