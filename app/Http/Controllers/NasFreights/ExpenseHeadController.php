<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\ExpenseHead\DestroyExpenseHeadRequest;
use App\Http\Requests\NasFreights\ExpenseHead\IndexExpenseHeadRequest;
use App\Http\Requests\NasFreights\ExpenseHead\ShowExpenseHeadRequest;
use App\Http\Requests\NasFreights\ExpenseHead\StoreExpenseHeadRequest;
use App\Http\Requests\NasFreights\ExpenseHead\UpdateExpenseHeadRequest;
use App\Models\NasFreights\NasFreightsExpenseCategory;
use App\Models\NasFreights\NasFreightsExpenseHead;
use Yajra\DataTables\Facades\DataTables;

class ExpenseHeadController extends Controller
{
    public function index(IndexExpenseHeadRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsExpenseHead::with('expenseCategory')->latest())
                ->addIndexColumn()
                ->addColumn('category_name', fn ($r) => $r->expenseCategory?->name ?? '—')
                ->addColumn('status_badge', fn ($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', function ($r) use ($request) {
                    $html = '';

                    if ($request->user()->hasPermission('freight.expense-head.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="'.$r->id.'"
                            data-name="'.e($r->name).'"
                            data-type="'.e($r->type).'"
                            data-expense_category_id="'.$r->expense_category_id.'"
                            data-amount="'.($r->amount ?? '').'"
                            data-status="'.e($r->status).'">
                            <i class="fa fa-edit"></i>
                        </button> ';
                    }

                    if ($request->user()->hasPermission('freight.expense-head.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('nas-freights.settings.expense-heads.destroy', $r->id).'"
                            data-name="'.e($r->name).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $categories = NasFreightsExpenseCategory::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $types = NasFreightsExpenseHead::types();

        return view('nas-freights.settings.expense-heads.index', compact('categories', 'types'));
    }

    public function show(ShowExpenseHeadRequest $request, NasFreightsExpenseHead $expenseHead)
    {
        return response()->json($expenseHead);
    }

    public function store(StoreExpenseHeadRequest $request)
    {
        NasFreightsExpenseHead::create($request->only('name', 'type', 'expense_category_id', 'amount', 'status'));

        return response()->json(['message' => 'Expense head created successfully.']);
    }

    public function update(UpdateExpenseHeadRequest $request, NasFreightsExpenseHead $expenseHead)
    {
        $expenseHead->update($request->only('name', 'type', 'expense_category_id', 'amount', 'status'));

        return response()->json(['message' => 'Expense head updated successfully.']);
    }

    public function destroy(DestroyExpenseHeadRequest $request, NasFreightsExpenseHead $expenseHead)
    {
        $expenseHead->delete();

        return response()->json(['message' => 'Expense head deleted.']);
    }
}
