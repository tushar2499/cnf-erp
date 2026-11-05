<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\BillItemDescription\DestroyBillItemDescriptionRequest;
use App\Http\Requests\NasFreights\BillItemDescription\IndexBillItemDescriptionRequest;
use App\Http\Requests\NasFreights\BillItemDescription\StoreBillItemDescriptionRequest;
use App\Http\Requests\NasFreights\BillItemDescription\UpdateBillItemDescriptionRequest;
use App\Models\NasFreights\NasFreightsBillItemDescription;
use Yajra\DataTables\Facades\DataTables;

class BillItemDescriptionController extends Controller
{
    public function index(IndexBillItemDescriptionRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsBillItemDescription::query()->orderBy('sort_order')->orderBy('name'))
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->addColumn('action', fn ($r) => '<button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'.$r->id.'"
                        data-name="'.e($r->name).'"
                        data-sort_order="'.$r->sort_order.'"
                        data-is_active="'.(int) $r->is_active.'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route('nas-freights.settings.bill-item-descriptions.destroy', $r->id).'"
                        data-name="'.e($r->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.settings.bill-item-descriptions.index');
    }

    public function store(StoreBillItemDescriptionRequest $request)
    {
        NasFreightsBillItemDescription::create([
            'name'       => trim($request->name),
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Bill item description created.']);
    }

    public function update(UpdateBillItemDescriptionRequest $request, NasFreightsBillItemDescription $billItemDescription)
    {
        $billItemDescription->update([
            'name'       => trim($request->name),
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Bill item description updated.']);
    }

    public function destroy(DestroyBillItemDescriptionRequest $request, NasFreightsBillItemDescription $billItemDescription)
    {
        $billItemDescription->delete();

        return response()->json(['message' => 'Bill item description deleted.']);
    }
}
