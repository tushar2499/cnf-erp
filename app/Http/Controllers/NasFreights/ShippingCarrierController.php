<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsShippingCarrier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ShippingCarrierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsShippingCarrier::query()->orderBy('name'))
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->addColumn('action', fn ($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'.$r->id.'"
                        data-carrier_code="'.e($r->carrier_code).'"
                        data-name="'.e($r->name).'"
                        data-scac_code="'.e($r->scac_code).'"
                        data-is_active="'.(int) $r->is_active.'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route('nas-freights.settings.shipping-carriers.destroy', $r->id).'"
                        data-name="'.e($r->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.settings.shipping-carriers.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'scac_code' => 'nullable|string|max:20',
        ]);

        NasFreightsShippingCarrier::create([
            'carrier_code' => NasFreightsShippingCarrier::generateCode(),
            'name'         => $request->name,
            'scac_code'    => $request->scac_code ? strtoupper($request->scac_code) : null,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Shipping carrier created.']);
    }

    public function update(Request $request, NasFreightsShippingCarrier $shippingCarrier)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'scac_code' => 'nullable|string|max:20',
        ]);

        $shippingCarrier->update([
            'name'      => $request->name,
            'scac_code' => $request->scac_code ? strtoupper($request->scac_code) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Shipping carrier updated.']);
    }

    public function destroy(NasFreightsShippingCarrier $shippingCarrier)
    {
        $shippingCarrier->delete();

        return response()->json(['message' => 'Shipping carrier deleted.']);
    }
}
