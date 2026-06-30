<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingExpenseHead;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ExpenseHeadController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingExpenseHead::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.expense-heads.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.expense-heads.index');
    }

    public function show(NasTradingExpenseHead $expenseHead)
    {
        return response()->json($expenseHead);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'category' => 'required']);
        NasTradingExpenseHead::create($request->only('name', 'category', 'status'));
        return response()->json(['message' => 'Expense Head created successfully.']);
    }

    public function update(Request $request, NasTradingExpenseHead $expenseHead)
    {
        $request->validate(['name' => 'required|string|max:255', 'category' => 'required']);
        $expenseHead->update($request->only('name', 'category', 'status'));
        return response()->json(['message' => 'Expense Head updated successfully.']);
    }

    public function destroy(NasTradingExpenseHead $expenseHead)
    {
        $expenseHead->delete();
        return response()->json(['message' => 'Expense Head deleted.']);
    }
}
