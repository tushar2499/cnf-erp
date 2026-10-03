<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\FreightExportBookingExpense\EditFreightExportBookingExpenseRequest;
use App\Http\Requests\NasFreights\FreightExportBookingExpense\UpdateFreightExportBookingExpenseRequest;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasFreights\NasFreightsExpenseHead;
use App\Models\NasFreights\NasFreightsFreightBookingExpenseItem;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingExpense;
use Illuminate\Support\Facades\DB;

class FreightExportBookingExpenseController extends Controller
{
    public function edit(EditFreightExportBookingExpenseRequest $request, NasFreightsFreightExportBooking $exportBooking)
    {
        $exportBooking->load(['customer', 'salesperson']);

        $expense = NasFreightsFreightExportBookingExpense::with('items')
            ->where('booking_id', $exportBooking->id)
            ->first();

        $expenseHeads = NasFreightsExpenseHead::where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'amount']);

        $employees = NasFreightsEmployee::where('branch_id', session('nas_freights_branch_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $existingRows = $expense
            ? $expense->items->map(fn ($i) => [
                'expense_head_id' => $i->expense_head_id,
                'receiptable'     => $i->receiptable,
                'expense_amount'  => $i->expense_amount,
                'approved_amount' => $i->approved_amount,
                'expense_date'    => $i->expense_date?->format('Y-m-d'),
                'note'            => $i->note,
            ])->values()
            : collect();

        return view('nas-freights.freight-export-bookings.expense', [
            'exportBooking' => $exportBooking,
            'expense'       => $expense,
            'expenseHeads'  => $expenseHeads,
            'employees'     => $employees,
            'existingRows'  => $existingRows,
            'today'         => now()->format('Y-m-d'),
        ]);
    }

    public function update(UpdateFreightExportBookingExpenseRequest $request, NasFreightsFreightExportBooking $exportBooking)
    {
        DB::transaction(function () use ($request, $exportBooking) {
            $totalExpense = collect($request->rows)->sum('expense_amount');
            $totalApproved = collect($request->rows)->sum(fn ($r) => (float) ($r['approved_amount'] ?? 0));

            $expense = NasFreightsFreightExportBookingExpense::firstOrNew([
                'booking_id' => $exportBooking->id,
            ]);

            $expense->fill([
                'expense_no'            => $expense->expense_no ?? NasFreightsFreightExportBookingExpense::generateExpenseNo(),
                'booking_type'          => 'export',
                'booking_id'            => $exportBooking->id,
                'booking_no'            => $exportBooking->export_booking_no,
                'invoice_no'            => $exportBooking->invoice_no,
                'bl_no'                 => $exportBooking->export_bl_no,
                'employee_id'           => $request->employee_id ?: null,
                'branch_id'             => session('nas_freights_branch_id'),
                'date'                  => $request->date,
                'total_expense_amount'  => $totalExpense,
                'total_approved_amount' => $totalApproved,
                'remarks'               => $request->remarks,
                'status'                => $totalApproved > 0 ? 'Approved' : 'Draft',
                'entry_by'              => auth()->id(),
            ]);

            $expense->save();

            $expense->items()->delete();

            foreach ($request->rows as $row) {
                NasFreightsFreightBookingExpenseItem::create([
                    'freight_booking_expense_id' => $expense->id,
                    'expense_head_id'            => $row['expense_head_id'],
                    'receiptable'                => $row['receiptable'] ?? 'No',
                    'expense_amount'             => $row['expense_amount'] ?? 0,
                    'approved_amount'            => $row['approved_amount'] ?? 0,
                    'expense_date'               => $row['expense_date'] ?? null,
                    'note'                       => $row['note'] ?? null,
                ]);
            }
        });

        return response()->json([
            'message'  => 'Expense saved for '.$exportBooking->export_booking_no.'.',
            'redirect' => route('nas-freights.freight-export-bookings.show', $exportBooking),
        ]);
    }
}
