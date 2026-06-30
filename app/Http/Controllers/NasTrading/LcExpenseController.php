<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingLcExpense;
use Illuminate\Http\Request;

class LcExpenseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'lc_id'        => 'required|exists:nas_trading_lcs,id',
            'amount'       => 'required|numeric|min:0',
            'posting_type' => 'required',
        ]);

        $expenseHead = null;
        if ($request->expense_head_id) {
            $expenseHead = \App\Models\NasTrading\NasTradingExpenseHead::find($request->expense_head_id);
        }

        $expense = NasTradingLcExpense::create([
            'lc_id'             => $request->lc_id,
            'expense_head_id'   => $request->expense_head_id,
            'expense_head_name' => $expenseHead?->name ?? $request->expense_head_name,
            'expense_date'      => $request->expense_date ?: now()->toDateString(),
            'amount'            => $request->amount,
            'posting_type'      => $request->posting_type,
            'posting_sub_type'  => $request->posting_sub_type ?: null,
            'reference'         => $request->reference,
            'note'              => $request->note,
            'entry_by'          => auth()->id(),
        ]);

        return response()->json(['message' => 'Expense added successfully.', 'expense' => $expense->load('expenseHead')]);
    }

    public function update(Request $request, NasTradingLcExpense $lcExpense)
    {
        $request->validate(['amount' => 'required|numeric|min:0', 'posting_type' => 'required']);

        $expenseHead = null;
        if ($request->expense_head_id) {
            $expenseHead = \App\Models\NasTrading\NasTradingExpenseHead::find($request->expense_head_id);
        }

        $lcExpense->update([
            'expense_head_id'   => $request->expense_head_id,
            'expense_head_name' => $expenseHead?->name ?? $request->expense_head_name,
            'expense_date'      => $request->expense_date ?: $lcExpense->expense_date,
            'amount'            => $request->amount,
            'posting_type'      => $request->posting_type,
            'posting_sub_type'  => $request->posting_sub_type ?: null,
            'reference'         => $request->reference,
            'note'              => $request->note,
        ]);

        return response()->json(['message' => 'Expense updated successfully.']);
    }

    public function destroy(NasTradingLcExpense $lcExpense)
    {
        $lcExpense->delete();
        return response()->json(['message' => 'Expense deleted.']);
    }
}
