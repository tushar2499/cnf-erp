<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsCustomer::where('branch_id', session('nas_freights_branch_id')))
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('nas-freights.stakeholders.customers.destroy', $r->id) . '"
                        data-name="' . e($r->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.stakeholders.customers.index', [
            'customerGroups' => NasFreightsCustomer::customerGroups(),
        ]);
    }

    public function nextId(Request $request)
    {
        $prefix = $request->input('prefix', 'CUS-');
        return response()->json(['customer_id' => NasFreightsCustomer::generateCustomerId($prefix)]);
    }

    public function show(NasFreightsCustomer $customer)
    {
        return response()->json($customer);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_prefix' => ['required', 'string', 'max:20'],
            'name'      => ['required', 'string', 'max:255'],
        ]);

        if ($request->filled('portal_password')) {
            $request->validate(['portal_password_confirm' => ['same:portal_password']]);
        }

        DB::transaction(function () use ($request) {
            NasFreightsCustomer::create([
                'branch_id'              => session('nas_freights_branch_id'),
                'id_prefix'              => $request->id_prefix,
                'customer_group'         => $request->customer_group,
                'customer_id'            => NasFreightsCustomer::generateCustomerId($request->id_prefix),
                'name'                   => $request->name,
                'owner_name'             => $request->owner_name,
                'address'                => $request->address,
                'phone'                  => $request->phone,
                'fax'                    => $request->fax,
                'mobile'                 => $request->mobile,
                'email'                  => $request->email,
                'sales_person'           => $request->sales_person,
                'customer_account'       => $request->customer_account,
                'vat_id'                 => $request->vat_id,
                'identity_type'          => $request->identity_type ?: null,
                'tin_bin_nid'            => $request->tin_bin_nid,
                'contact_person_details' => $request->contact_person_details,
                'country'                => $request->country,
                'division'               => $request->division,
                'district'               => $request->district,
                'city'                   => $request->city,
                'region'                 => $request->region,
                'customer_id_reference'  => $request->customer_id_reference,
                'postal_code'            => $request->postal_code,
                'pay_type'               => $request->pay_type ?? 'Cash',
                'portal_password'        => $request->filled('portal_password') ? bcrypt($request->portal_password) : null,
                'status'                 => $request->status ?? 'Active',
                'taxscope'               => $request->taxscope ?? 'Exempted',
                'discount'               => $request->discount ?? 0,
                'commission'             => $request->commission ?? 0,
                'credit_limit'           => $request->credit_limit ?? 0,
                'limit_days'             => $request->limit_days ?? 0,
                'security_deposit'       => $request->security_deposit ?? 0,
                'maturity_days'          => $request->maturity_days ?? 0,
                'prefix'                 => $request->prefix,
            ]);
        });

        return response()->json(['message' => 'Customer created successfully.']);
    }

    public function update(Request $request, NasFreightsCustomer $customer)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        if ($request->filled('portal_password')) {
            $request->validate(['portal_password_confirm' => ['same:portal_password']]);
        }

        $customer->update([
            'customer_group'         => $request->customer_group,
            'name'                   => $request->name,
            'owner_name'             => $request->owner_name,
            'address'                => $request->address,
            'phone'                  => $request->phone,
            'fax'                    => $request->fax,
            'mobile'                 => $request->mobile,
            'email'                  => $request->email,
            'sales_person'           => $request->sales_person,
            'customer_account'       => $request->customer_account,
            'vat_id'                 => $request->vat_id,
            'identity_type'          => $request->identity_type ?: null,
            'tin_bin_nid'            => $request->tin_bin_nid,
            'contact_person_details' => $request->contact_person_details,
            'country'                => $request->country,
            'division'               => $request->division,
            'district'               => $request->district,
            'city'                   => $request->city,
            'region'                 => $request->region,
            'customer_id_reference'  => $request->customer_id_reference,
            'postal_code'            => $request->postal_code,
            'pay_type'               => $request->pay_type ?? 'Cash',
            'portal_password'        => $request->filled('portal_password') ? bcrypt($request->portal_password) : $customer->portal_password,
            'status'                 => $request->status ?? 'Active',
            'taxscope'               => $request->taxscope ?? 'Exempted',
            'discount'               => $request->discount ?? 0,
            'commission'             => $request->commission ?? 0,
            'credit_limit'           => $request->credit_limit ?? 0,
            'limit_days'             => $request->limit_days ?? 0,
            'security_deposit'       => $request->security_deposit ?? 0,
            'maturity_days'          => $request->maturity_days ?? 0,
            'prefix'                 => $request->prefix,
        ]);

        return response()->json(['message' => 'Customer updated successfully.']);
    }

    public function destroy(NasFreightsCustomer $customer)
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted.']);
    }
}
