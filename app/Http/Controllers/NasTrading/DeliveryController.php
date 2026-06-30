<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingDelivery;
use App\Models\NasTrading\NasTradingCustomerBill;
use App\Models\NasTrading\NasTradingTransportCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingDelivery::latest())
                ->addIndexColumn()
                ->editColumn('delivery_date', fn($r) => $r->delivery_date?->format('d-M-Y'))
                ->addColumn('status_badge', fn($r) => match($r->delivery_status) {
                    'Pending'    => '<span class="badge bg-secondary">Pending</span>',
                    'Dispatched' => '<span class="badge bg-warning text-dark">Dispatched</span>',
                    'Delivered'  => '<span class="badge bg-success">Delivered</span>',
                    default      => $r->delivery_status,
                })
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-trading.deliveries.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-eye"></i></a> ' .
                    ($r->delivery_status === 'Pending' ? '<button class="btn btn-sm btn-outline-warning btn-dispatch" data-url="' . route('nas-trading.deliveries.dispatch', $r->id) . '" style="padding:2px 6px;font-size:.7rem" title="Dispatch"><i class="fa fa-truck"></i></button> ' : '') .
                    ($r->delivery_status === 'Dispatched' ? '<button class="btn btn-sm btn-outline-success btn-deliver" data-url="' . route('nas-trading.deliveries.deliver', $r->id) . '" style="padding:2px 6px;font-size:.7rem" title="Mark Delivered"><i class="fa fa-check-circle"></i></button>' : ''))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.deliveries.index');
    }

    public function create()
    {
        $transportCos = NasTradingTransportCompany::where('status', 'Active')->get();
        return view('nas-trading.deliveries.create', compact('transportCos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bill_id'          => 'required|exists:nas_trading_customer_bills,id',
            'delivery_address' => 'required',
        ]);

        DB::transaction(function () use ($request) {
            $bill = NasTradingCustomerBill::with('lc.items')->find($request->bill_id);
            $delivery = NasTradingDelivery::create([
                'delivery_no'     => NasTradingDelivery::generateDeliveryNo(),
                'bill_id'         => $bill->id,
                'bill_no'         => $bill->bill_no,
                'lc_id'           => $bill->lc_id,
                'lc_no'           => $bill->lc_no,
                'customer_id'     => $bill->customer_id,
                'customer_name'   => $bill->customer_name,
                'delivery_date'   => $request->delivery_date ?: now()->toDateString(),
                'delivery_address'=> $request->delivery_address,
                'transport_co_id' => $request->transport_co_id,
                'vehicle_no'      => $request->vehicle_no,
                'driver_name'     => $request->driver_name,
                'driver_phone'    => $request->driver_phone,
                'delivery_status' => 'Pending',
                'note'            => $request->note,
                'entry_by'        => auth()->id(),
            ]);

            if ($bill->lc?->items) {
                foreach ($bill->lc->items as $item) {
                    $delivery->deliveryItems()->create([
                        'lc_item_id'   => $item->id,
                        'product_name' => $item->product_name,
                        'hs_code'      => $item->hs_code,
                        'qty'          => $item->qty_count,
                        'unit'         => $item->qty_unit,
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Delivery created successfully.', 'redirect' => route('nas-trading.deliveries.index')]);
    }

    public function show(NasTradingDelivery $delivery)
    {
        $delivery->load('deliveryItems');
        return view('nas-trading.deliveries.show', compact('delivery'));
    }

    public function edit(NasTradingDelivery $delivery)
    {
        $delivery->load('deliveryItems');
        $transportCos = NasTradingTransportCompany::where('status', 'Active')->get();
        return view('nas-trading.deliveries.edit', compact('delivery', 'transportCos'));
    }

    public function update(Request $request, NasTradingDelivery $delivery)
    {
        $delivery->update($request->only('delivery_date', 'delivery_address', 'transport_co_id', 'vehicle_no', 'driver_name', 'driver_phone', 'note'));
        return response()->json(['message' => 'Delivery updated successfully.']);
    }

    public function dispatch(NasTradingDelivery $delivery)
    {
        $delivery->update(['delivery_status' => 'Dispatched']);
        return response()->json(['message' => 'Marked as Dispatched.']);
    }

    public function deliver(NasTradingDelivery $delivery)
    {
        $delivery->update(['delivery_status' => 'Delivered']);
        return response()->json(['message' => 'Marked as Delivered.']);
    }
}
