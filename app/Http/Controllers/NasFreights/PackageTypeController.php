<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsPackageType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PackageTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsPackageType::query()->orderBy('sort_order')->orderBy('name'))
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->addColumn('action', fn ($r) => '<button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'.$r->id.'"
                        data-name="'.e($r->name).'"
                        data-description="'.e($r->description).'"
                        data-sort_order="'.$r->sort_order.'"
                        data-is_active="'.(int) $r->is_active.'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route('nas-freights.settings.package-types.destroy', $r->id).'"
                        data-name="'.e($r->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.settings.package-types.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100|unique:nas_freights_package_types,name',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        NasFreightsPackageType::create([
            'name'        => $request->name,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Package type created.']);
    }

    public function update(Request $request, NasFreightsPackageType $packageType)
    {
        $request->validate([
            'name'       => 'required|string|max:100|unique:nas_freights_package_types,name,'.$packageType->id,
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $packageType->update([
            'name'        => $request->name,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Package type updated.']);
    }

    public function destroy(NasFreightsPackageType $packageType)
    {
        $packageType->delete();

        return response()->json(['message' => 'Package type deleted.']);
    }
}
