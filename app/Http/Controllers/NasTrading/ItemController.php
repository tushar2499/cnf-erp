<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingItem::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.items.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.items.index');
    }

    public function show(NasTradingItem $item)
    {
        return response()->json($item);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        DB::transaction(function () use ($request) {
            NasTradingItem::create([
                'code'     => NasTradingItem::generateCode(),
                'name'     => $request->name,
                'hs_code'  => $request->hs_code,
                'unit'     => $request->unit,
                'category' => $request->category,
                'status'   => $request->status ?? 'Active',
            ]);
        });
        return response()->json(['message' => 'Item created successfully.']);
    }

    public function update(Request $request, NasTradingItem $item)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $item->update([
            'name'     => $request->name,
            'hs_code'  => $request->hs_code,
            'unit'     => $request->unit,
            'category' => $request->category,
            'status'   => $request->status ?? 'Active',
        ]);
        return response()->json(['message' => 'Item updated successfully.']);
    }

    public function destroy(NasTradingItem $item)
    {
        $item->delete();
        return response()->json(['message' => 'Item deleted.']);
    }

    public function search(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasTradingItem::where('status', 'Active')
                ->where(fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'name', 'hs_code', 'unit'])
                ->map(fn($i) => ['id' => $i->id, 'text' => $i->code . ' | ' . $i->name, 'code' => $i->code, 'hs_code' => $i->hs_code, 'unit' => $i->unit])
        );
    }
}
