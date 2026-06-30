<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsSupplier;
use App\Models\NasFreights\NasFreightsVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsVehicle::where('branch_id', session('nas_freights_branch_id')))
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('nas-freights.vehicles.destroy', $r->id) . '"
                        data-name="' . e($r->vehicle_number) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.vehicles.index', [
            'vehicleClasses' => NasFreightsVehicle::vehicleClasses(),
            'vehicleTypes'   => NasFreightsVehicle::vehicleTypes(),
            'purchaseUnits'  => NasFreightsVehicle::purchaseUnits(),
        ]);
    }

    public function searchSuppliers(Request $request)
    {
        $term = $request->input('q', '');
        $results = NasFreightsSupplier::where('is_active', true)
            ->where('branch_id', session('nas_freights_branch_id'))
            ->where(function ($q) use ($term) {
                $q->where('company_name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            })
            ->limit(15)
            ->get(['id', 'code', 'company_name']);

        return response()->json($results->map(fn($s) => [
            'id'   => $s->id,
            'text' => $s->code . ' — ' . $s->company_name,
            'name' => $s->company_name,
        ]));
    }

    public function show(NasFreightsVehicle $vehicle)
    {
        return response()->json($vehicle);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_number' => ['required', 'string', 'max:50', 'unique:nas_freights_vehicles,vehicle_number'],
            'vehicle_class'  => ['required', 'string'],
            'vehicle_type'   => ['required', 'string'],
            'purchase_unit'  => ['nullable', 'string'],
        ]);

        $data = $this->prepareData($request);
        $data['branch_id'] = session('nas_freights_branch_id');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('nas-freights/vehicles', 'public');
        }

        NasFreightsVehicle::create($data);

        return response()->json(['message' => 'Vehicle created successfully.']);
    }

    public function update(Request $request, NasFreightsVehicle $vehicle)
    {
        $request->validate([
            'vehicle_number' => ['required', 'string', 'max:50', 'unique:nas_freights_vehicles,vehicle_number,' . $vehicle->id],
            'vehicle_class'  => ['required', 'string'],
            'vehicle_type'   => ['required', 'string'],
            'purchase_unit'  => ['nullable', 'string'],
        ]);

        $data = $this->prepareData($request);

        if ($request->hasFile('image')) {
            if ($vehicle->image) Storage::disk('public')->delete($vehicle->image);
            $data['image'] = $request->file('image')->store('nas-freights/vehicles', 'public');
        }

        $vehicle->update($data);

        return response()->json(['message' => 'Vehicle updated successfully.']);
    }

    public function destroy(NasFreightsVehicle $vehicle)
    {
        if ($vehicle->image) Storage::disk('public')->delete($vehicle->image);
        $vehicle->delete();
        return response()->json(['message' => 'Vehicle deleted.']);
    }

    private function prepareData(Request $request): array
    {
        return [
            'vehicle_number'     => $request->vehicle_number,
            'vehicle_name'       => $request->vehicle_name,
            'vehicle_class'      => $request->vehicle_class,
            'vehicle_type'       => $request->vehicle_type,
            'supplier_id'        => $request->supplier_id ?: null,
            'supplier_name'      => $request->supplier_name,
            'purchase_unit'      => $request->purchase_unit,
            'price'              => $request->price ?? 0,
            'description'        => $request->description,
            'remarks'            => $request->remarks,
            'status'             => $request->status ?? 'Active',
            'availability_in_po' => $request->boolean('availability_in_po'),
            'availability_in_so' => $request->boolean('availability_in_so'),
        ];
    }
}
