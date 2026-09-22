<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\ExpenseCategory\DestroyExpenseCategoryRequest;
use App\Http\Requests\NasFreights\ExpenseCategory\IndexExpenseCategoryRequest;
use App\Http\Requests\NasFreights\ExpenseCategory\StoreExpenseCategoryRequest;
use App\Http\Requests\NasFreights\ExpenseCategory\UpdateExpenseCategoryRequest;
use App\Models\NasFreights\NasFreightsExpenseCategory;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    public function index(IndexExpenseCategoryRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsExpenseCategory::query()->latest())
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', function ($r) use ($request) {
                    $html = '';

                    if ($request->user()->hasPermission('freight.expense-category.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="'.$r->id.'"
                            data-name="'.e($r->name).'"
                            data-description="'.e($r->description).'"
                            data-is_active="'.(int) $r->is_active.'">
                            <i class="fa fa-edit"></i>
                        </button> ';
                    }

                    if ($request->user()->hasPermission('freight.expense-category.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('nas-freights.settings.expense-categories.destroy', $r->id).'"
                            data-name="'.e($r->name).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.settings.expense-categories.index');
    }

    public function store(StoreExpenseCategoryRequest $request)
    {
        NasFreightsExpenseCategory::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Expense category created successfully.']);
    }

    public function update(UpdateExpenseCategoryRequest $request, NasFreightsExpenseCategory $expenseCategory)
    {
        $expenseCategory->update([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Expense category updated successfully.']);
    }

    public function destroy(DestroyExpenseCategoryRequest $request, NasFreightsExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        return response()->json(['message' => 'Expense category deleted.']);
    }
}
