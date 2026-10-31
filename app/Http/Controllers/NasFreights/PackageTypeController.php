<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\PackageType\DestroyPackageTypeRequest;
use App\Http\Requests\NasFreights\PackageType\IndexPackageTypeRequest;
use App\Http\Requests\NasFreights\PackageType\StorePackageTypeRequest;
use App\Http\Requests\NasFreights\PackageType\UpdatePackageTypeRequest;
use App\Models\NasFreights\NasFreightsPackageType;
use Yajra\DataTables\Facades\DataTables;

class PackageTypeController extends Controller
{
    public function index(IndexPackageTypeRequest $request)
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

    public function store(StorePackageTypeRequest $request)
    {
        NasFreightsPackageType::create([
            'name'        => $request->name,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Package type created.']);
    }

    public function update(UpdatePackageTypeRequest $request, NasFreightsPackageType $packageType)
    {
        $packageType->update([
            'name'        => $request->name,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Package type updated.']);
    }

    public function destroy(DestroyPackageTypeRequest $request, NasFreightsPackageType $packageType)
    {
        $packageType->delete();

        return response()->json(['message' => 'Package type deleted.']);
    }
}
