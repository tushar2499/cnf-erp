<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsSupplier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsSupplier::where('branch_id', session('nas_freights_branch_id')))
                ->addIndexColumn()
                ->addColumn('group_badge', fn($r) => $r->supplier_group
                    ? '<span class="badge ' . ($r->supplier_group === 'Exporter' ? 'bg-info text-dark' : 'bg-warning text-dark') . '">' . e($r->supplier_group) . '</span>'
                    : '—')
                ->addColumn('status_badge', fn($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'             . $r->id                       . '"
                        data-code="'           . e($r->code)                  . '"
                        data-company_name="'   . e($r->company_name)          . '"
                        data-owner_name="'     . e($r->owner_name)            . '"
                        data-address="'        . e($r->address)               . '"
                        data-phone_no="'       . e($r->phone_no)              . '"
                        data-fax="'            . e($r->fax)                   . '"
                        data-url="'            . e($r->url)                   . '"
                        data-mobile_no="'      . e($r->mobile_no)             . '"
                        data-email="'          . e($r->email)                 . '"
                        data-contact="'        . e($r->contact)               . '"
                        data-designation="'    . e($r->designation)           . '"
                        data-supplier_group="' . e($r->supplier_group)        . '"
                        data-taxscope="'       . e($r->taxscope)              . '"
                        data-is_active="'      . (int) $r->is_active          . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'  . route('nas-freights.stakeholders.suppliers.destroy', $r->id) . '"
                        data-name="' . e($r->code) . ' — ' . e($r->company_name)                   . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['group_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.suppliers.index', [
            'supplierGroups' => NasFreightsSupplier::supplierGroups(),
            'taxscopes'      => NasFreightsSupplier::taxscopes(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'owner_name'     => ['nullable', 'string', 'max:255'],
            'address'        => ['nullable', 'string'],
            'phone_no'       => ['nullable', 'string', 'max:30'],
            'fax'            => ['nullable', 'string', 'max:30'],
            'url'            => ['nullable', 'string', 'max:255'],
            'mobile_no'      => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:255'],
            'contact'        => ['nullable', 'string', 'max:255'],
            'designation'    => ['nullable', 'string', 'max:255'],
            'supplier_group' => ['nullable', 'string'],
            'taxscope'       => ['required', 'string'],
        ]);

        NasFreightsSupplier::create([
            'branch_id'      => session('nas_freights_branch_id'),
            'code'           => NasFreightsSupplier::generateCode(),
            'company_name'   => $request->company_name,
            'owner_name'     => $request->owner_name,
            'address'        => $request->address,
            'phone_no'       => $request->phone_no,
            'fax'            => $request->fax,
            'url'            => $request->url,
            'mobile_no'      => $request->mobile_no,
            'email'          => $request->email,
            'contact'        => $request->contact,
            'designation'    => $request->designation,
            'supplier_group' => $request->supplier_group,
            'taxscope'       => $request->taxscope ?? 'Exempted',
            'is_active'      => $request->status === 'Active',
        ]);

        return response()->json(['message' => 'Supplier created successfully.']);
    }

    public function update(Request $request, NasFreightsSupplier $supplier)
    {
        $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'owner_name'     => ['nullable', 'string', 'max:255'],
            'address'        => ['nullable', 'string'],
            'phone_no'       => ['nullable', 'string', 'max:30'],
            'fax'            => ['nullable', 'string', 'max:30'],
            'url'            => ['nullable', 'string', 'max:255'],
            'mobile_no'      => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:255'],
            'contact'        => ['nullable', 'string', 'max:255'],
            'designation'    => ['nullable', 'string', 'max:255'],
            'supplier_group' => ['nullable', 'string'],
            'taxscope'       => ['required', 'string'],
        ]);

        $supplier->update([
            'company_name'   => $request->company_name,
            'owner_name'     => $request->owner_name,
            'address'        => $request->address,
            'phone_no'       => $request->phone_no,
            'fax'            => $request->fax,
            'url'            => $request->url,
            'mobile_no'      => $request->mobile_no,
            'email'          => $request->email,
            'contact'        => $request->contact,
            'designation'    => $request->designation,
            'supplier_group' => $request->supplier_group,
            'taxscope'       => $request->taxscope,
            'is_active'      => $request->status === 'Active',
        ]);

        return response()->json(['message' => 'Supplier updated successfully.']);
    }

    public function destroy(NasFreightsSupplier $supplier)
    {
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted.']);
    }
}
