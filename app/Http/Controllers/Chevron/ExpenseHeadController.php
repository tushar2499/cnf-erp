<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ExpenseHeadController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronExpenseHead::with('expenseCategory'))
                ->addIndexColumn()
                ->addColumn('category_name', fn($row) => $row->expenseCategory?->name ?? '-')
                ->addColumn('status_badge', fn($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($row) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="' . $row->id . '"
                        data-name="' . e($row->name) . '"
                        data-expense_category_id="' . $row->expense_category_id . '"
                        data-type="' . $row->type . '"
                        data-amount="' . $row->amount . '"
                        data-is_active="' . (int)$row->is_active . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('chevron.settings.expense-heads.destroy', $row->id) . '"
                        data-name="' . e($row->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $categories = ChevronExpenseCategory::where('is_active', true)->orderBy('name')->get();
        return view('chevron.settings.expense-heads.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'exists:chevron_expense_categories,id'],
            'type'                => ['required', 'in:External,Internal'],
            'amount'              => ['nullable', 'numeric', 'min:0'],
        ]);

        ChevronExpenseHead::create([
            'name'                => $request->name,
            'expense_category_id' => $request->expense_category_id,
            'type'                => $request->type,
            'amount'              => $request->amount,
            'is_active'           => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Expense head created successfully.']);
    }

    public function update(Request $request, ChevronExpenseHead $expenseHead)
    {
        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'exists:chevron_expense_categories,id'],
            'type'                => ['required', 'in:External,Internal'],
            'amount'              => ['nullable', 'numeric', 'min:0'],
        ]);

        $expenseHead->update([
            'name'                => $request->name,
            'expense_category_id' => $request->expense_category_id,
            'type'                => $request->type,
            'amount'              => $request->amount,
            'is_active'           => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Expense head updated successfully.']);
    }

    public function destroy(ChevronExpenseHead $expenseHead)
    {
        $expenseHead->delete();
        return response()->json(['message' => 'Expense head deleted.']);
    }
}
