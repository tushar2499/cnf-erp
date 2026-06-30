<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingShipment;
use App\Models\NasTrading\NasTradingLc;
use App\Models\NasTrading\NasTradingPort;
use App\Models\NasTrading\NasTradingPsiCompany;
use App\Models\NasTrading\NasTradingCnfAgent;
use App\Models\NasTrading\NasTradingTransportCompany;
use App\Models\NasTrading\NasTradingExpenseHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingShipment::latest())
                ->addIndexColumn()
                ->editColumn('arrival_date', fn($r) => $r->arrival_date?->format('d-M-Y'))
                ->addColumn('status_badge', fn($r) => match($r->shipment_status) {
                    'Pending'    => '<span class="badge bg-secondary">Pending</span>',
                    'In Transit' => '<span class="badge bg-info">In Transit</span>',
                    'Arrived'    => '<span class="badge bg-primary">Arrived</span>',
                    'Cleared'    => '<span class="badge bg-success">Cleared</span>',
                    'Delivered'  => '<span class="badge bg-dark">Delivered</span>',
                    default      => $r->shipment_status,
                })
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-trading.shipments.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-eye"></i></a> ' .
                    '<a href="' . route('nas-trading.shipments.edit', $r->id) . '" class="btn btn-sm btn-outline-primary" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-edit"></i></a> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.shipments.destroy', $r->id) . '" data-name="' . e($r->shipment_no) . '" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.shipments.index');
    }

    public function create()
    {
        $ports      = NasTradingPort::where('status', 'Active')->get();
        $psiCompanies = NasTradingPsiCompany::where('status', 'Active')->get();
        $cnfAgents  = NasTradingCnfAgent::where('status', 'Active')->get();
        $transportCos = NasTradingTransportCompany::where('status', 'Active')->get();
        $expenseHeads = NasTradingExpenseHead::where('status', 'Active')->get();
        return view('nas-trading.shipments.create', compact('ports', 'psiCompanies', 'cnfAgents', 'transportCos', 'expenseHeads'));
    }

    public function store(Request $request)
    {
        $request->validate(['lc_id' => 'required|exists:nas_trading_lcs,id']);

        DB::transaction(function () use ($request) {
            $shipment = NasTradingShipment::create(array_merge(
                ['shipment_no' => NasTradingShipment::generateShipmentNo(), 'created_by' => auth()->id()],
                $request->except(['_token', 'items', 'costs'])
            ));

            if ($request->items) {
                foreach ($request->items as $item) {
                    if (!empty($item['item_name'])) {
                        $shipment->items()->create($item);
                    }
                }
            }

            if ($request->costs) {
                foreach ($request->costs as $cost) {
                    if (!empty($cost['cost_head'])) {
                        $shipment->costs()->create($cost);
                    }
                }
            }
        });

        return response()->json(['message' => 'Shipment created successfully.', 'redirect' => route('nas-trading.shipments.index')]);
    }

    public function show(NasTradingShipment $shipment)
    {
        $shipment->load('items', 'costs');
        return view('nas-trading.shipments.show', compact('shipment'));
    }

    public function edit(NasTradingShipment $shipment)
    {
        $shipment->load('items', 'costs');
        $ports      = NasTradingPort::where('status', 'Active')->get();
        $psiCompanies = NasTradingPsiCompany::where('status', 'Active')->get();
        $cnfAgents  = NasTradingCnfAgent::where('status', 'Active')->get();
        $transportCos = NasTradingTransportCompany::where('status', 'Active')->get();
        $expenseHeads = NasTradingExpenseHead::where('status', 'Active')->get();
        return view('nas-trading.shipments.edit', compact('shipment', 'ports', 'psiCompanies', 'cnfAgents', 'transportCos', 'expenseHeads'));
    }

    public function update(Request $request, NasTradingShipment $shipment)
    {
        $request->validate(['lc_id' => 'required|exists:nas_trading_lcs,id']);

        DB::transaction(function () use ($request, $shipment) {
            $shipment->update($request->except(['_token', '_method', 'items', 'costs']));
            $shipment->items()->delete();
            $shipment->costs()->delete();
            if ($request->items) {
                foreach ($request->items as $item) {
                    if (!empty($item['item_name'])) $shipment->items()->create($item);
                }
            }
            if ($request->costs) {
                foreach ($request->costs as $cost) {
                    if (!empty($cost['cost_head'])) $shipment->costs()->create($cost);
                }
            }
        });

        return response()->json(['message' => 'Shipment updated successfully.', 'redirect' => route('nas-trading.shipments.show', $shipment->id)]);
    }

    public function destroy(NasTradingShipment $shipment)
    {
        $shipment->items()->delete();
        $shipment->costs()->delete();
        $shipment->delete();
        return response()->json(['message' => 'Shipment deleted.']);
    }

    public function searchLc(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasTradingLc::where(fn($q) => $q->where('lc_no_system', 'like', "%{$term}%")->orWhere('pfi_no', 'like', "%{$term}%")->orWhere('customer_name', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'lc_no_system', 'lc_no', 'pfi_no', 'customer_id', 'customer_name'])
                ->map(fn($l) => ['id' => $l->id, 'text' => $l->lc_no_system . ' | ' . $l->customer_name, 'lc_no' => $l->lc_no, 'pfi_no' => $l->pfi_no, 'customer_id' => $l->customer_id, 'customer_name' => $l->customer_name])
        );
    }
}
