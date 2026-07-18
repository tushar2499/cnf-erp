<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsOverseasAgent;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OverseasAgentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsOverseasAgent::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->addColumn('action', fn ($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'.$r->id.'"
                        data-agent_code="'.e($r->agent_code).'"
                        data-name="'.e($r->name).'"
                        data-country="'.e($r->country).'"
                        data-city="'.e($r->city).'"
                        data-address="'.e($r->address).'"
                        data-contact_person="'.e($r->contact_person).'"
                        data-designation="'.e($r->designation).'"
                        data-email="'.e($r->email).'"
                        data-phone="'.e($r->phone).'"
                        data-mobile="'.e($r->mobile).'"
                        data-payment_terms="'.e($r->payment_terms).'"
                        data-remarks="'.e($r->remarks).'"
                        data-is_active="'.(int) $r->is_active.'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route('nas-freights.settings.overseas-agents.destroy', $r->id).'"
                        data-name="'.e($r->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.settings.overseas-agents.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'email'   => 'nullable|email|max:255',
        ]);

        NasFreightsOverseasAgent::create([
            'agent_code'     => NasFreightsOverseasAgent::generateCode(),
            'name'           => $request->name,
            'country'        => $request->country,
            'city'           => $request->city,
            'address'        => $request->address,
            'contact_person' => $request->contact_person,
            'designation'    => $request->designation,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'mobile'         => $request->mobile,
            'payment_terms'  => $request->payment_terms,
            'remarks'        => $request->remarks,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Overseas agent created.']);
    }

    public function update(Request $request, NasFreightsOverseasAgent $overseasAgent)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'email'   => 'nullable|email|max:255',
        ]);

        $overseasAgent->update([
            'name'           => $request->name,
            'country'        => $request->country,
            'city'           => $request->city,
            'address'        => $request->address,
            'contact_person' => $request->contact_person,
            'designation'    => $request->designation,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'mobile'         => $request->mobile,
            'payment_terms'  => $request->payment_terms,
            'remarks'        => $request->remarks,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Overseas agent updated.']);
    }

    public function destroy(NasFreightsOverseasAgent $overseasAgent)
    {
        $overseasAgent->delete();

        return response()->json(['message' => 'Overseas agent deleted.']);
    }
}
