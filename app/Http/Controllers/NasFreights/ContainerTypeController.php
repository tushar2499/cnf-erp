<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\ContainerType\DestroyContainerTypeRequest;
use App\Http\Requests\NasFreights\ContainerType\IndexContainerTypeRequest;
use App\Http\Requests\NasFreights\ContainerType\StoreContainerTypeRequest;
use App\Http\Requests\NasFreights\ContainerType\UpdateContainerTypeRequest;
use App\Models\NasFreights\NasFreightsContainerType;
use Yajra\DataTables\Facades\DataTables;

class ContainerTypeController extends Controller
{
    public function index(IndexContainerTypeRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsContainerType::query()->orderBy('sort_order')->orderBy('name'))
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
                        data-url="'.route('nas-freights.settings.container-types.destroy', $r->id).'"
                        data-name="'.e($r->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.settings.container-types.index');
    }

    public function store(StoreContainerTypeRequest $request)
    {
        NasFreightsContainerType::create([
            'name'        => strtoupper(trim($request->name)),
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Container type created.']);
    }

    public function update(UpdateContainerTypeRequest $request, NasFreightsContainerType $containerType)
    {
        $containerType->update([
            'name'        => strtoupper(trim($request->name)),
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Container type updated.']);
    }

    public function destroy(DestroyContainerTypeRequest $request, NasFreightsContainerType $containerType)
    {
        $containerType->delete();

        return response()->json(['message' => 'Container type deleted.']);
    }
}
