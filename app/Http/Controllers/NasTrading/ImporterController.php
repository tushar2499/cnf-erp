<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingImporter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ImporterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingImporter::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.importers.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.importers.index');
    }

    public function show(NasTradingImporter $importer)
    {
        return response()->json($importer);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        NasTradingImporter::create($request->only('name', 'bin_no', 'address', 'status'));
        return response()->json(['message' => 'Importer created successfully.']);
    }

    public function update(Request $request, NasTradingImporter $importer)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $importer->update($request->only('name', 'bin_no', 'address', 'status'));
        return response()->json(['message' => 'Importer updated successfully.']);
    }

    public function destroy(NasTradingImporter $importer)
    {
        $importer->delete();
        return response()->json(['message' => 'Importer deleted.']);
    }
}
