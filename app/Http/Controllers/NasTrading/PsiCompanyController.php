<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingPsiCompany;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PsiCompanyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingPsiCompany::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.psi-companies.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.psi-companies.index');
    }

    public function show(NasTradingPsiCompany $psiCompany)
    {
        return response()->json($psiCompany);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        NasTradingPsiCompany::create($request->only('name', 'country', 'status'));
        return response()->json(['message' => 'PSI Company created successfully.']);
    }

    public function update(Request $request, NasTradingPsiCompany $psiCompany)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $psiCompany->update($request->only('name', 'country', 'status'));
        return response()->json(['message' => 'PSI Company updated successfully.']);
    }

    public function destroy(NasTradingPsiCompany $psiCompany)
    {
        $psiCompany->delete();
        return response()->json(['message' => 'PSI Company deleted.']);
    }
}
