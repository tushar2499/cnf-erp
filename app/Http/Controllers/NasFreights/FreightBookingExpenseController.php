<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\FreightBookingExpense\CreateFreightBookingExpenseRequest;
use App\Http\Requests\NasFreights\FreightBookingExpense\DestroyFreightBookingExpenseRequest;
use App\Http\Requests\NasFreights\FreightBookingExpense\EditFreightBookingExpenseRequest;
use App\Http\Requests\NasFreights\FreightBookingExpense\IndexFreightBookingExpenseRequest;
use App\Http\Requests\NasFreights\FreightBookingExpense\ShowFreightBookingExpenseRequest;
use App\Http\Requests\NasFreights\FreightBookingExpense\StoreFreightBookingExpenseRequest;
use App\Http\Requests\NasFreights\FreightBookingExpense\UpdateFreightBookingExpenseRequest;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasFreights\NasFreightsExpenseHead;
use App\Models\NasFreights\NasFreightsFreightBooking;
use App\Models\NasFreights\NasFreightsFreightBookingExpense;
use App\Models\NasFreights\NasFreightsFreightBookingExpenseItem;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FreightBookingExpenseController extends Controller
{
    private function bookingType(): string
    {
        return str_contains(request()->path(), 'export-expenses') ? 'export' : 'import';
    }

    private function routePrefix(): string
    {
        return 'nas-freights.'.$this->bookingType().'-expenses';
    }

    public function index(IndexFreightBookingExpenseRequest $request)
    {
        $bookingType = $this->bookingType();
        $routePrefix = $this->routePrefix();

        if ($request->ajax()) {
            $query = NasFreightsFreightBookingExpense::with(['branch', 'employee'])
                ->where('branch_id', session('nas_freights_branch_id'))
                ->where('booking_type', $bookingType)
                ->when($request->from_date, fn ($q) => $q->whereDate('date', '>=', $request->from_date))
                ->when($request->to_date, fn ($q) => $q->whereDate('date', '<=', $request->to_date))
                ->when($request->status_filter, fn ($q, $s) => $q->where('status', $s))
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('date', fn ($r) => $r->date?->format('d M Y') ?? '—')
                ->addColumn('employee_name', fn ($r) => $r->employee?->name ?? '—')
                ->addColumn('total_expense_amount_fmt', fn ($r) => number_format($r->total_expense_amount, 2))
                ->addColumn('total_approved_amount_fmt', fn ($r) => number_format($r->total_approved_amount, 2))
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Approved'  => '<span class="badge bg-success">Approved</span>',
                    'Submitted' => '<span class="badge bg-warning text-dark">Submitted</span>',
                    default     => '<span class="badge bg-secondary">Draft</span>',
                })
                ->addColumn('action', function ($r) use ($request, $routePrefix, $bookingType) {
                    $html = '<a href="'.route($routePrefix.'.show', $r->id).'"
                        class="btn btn-sm btn-outline-info py-0 px-1"><i class="fa fa-eye"></i></a> ';

                    if ($request->user()->hasPermission("freight.{$bookingType}-expense.edit")) {
                        $html .= '<a href="'.route($routePrefix.'.edit', $r->id).'"
                            class="btn btn-sm btn-outline-primary py-0 px-1"><i class="fa fa-edit"></i></a> ';
                    }

                    if ($request->user()->hasPermission("freight.{$bookingType}-expense.delete")) {
                        $html .= '<button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete"
                            data-url="'.route($routePrefix.'.destroy', $r->id).'"
                            data-name="'.e($r->expense_no).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $statuses = NasFreightsFreightBookingExpense::statuses();

        return view('nas-freights.freight-booking-expenses.index', compact('statuses', 'bookingType', 'routePrefix'));
    }

    public function create(CreateFreightBookingExpenseRequest $request)
    {
        $bookingType = $this->bookingType();
        $routePrefix = $this->routePrefix();

        $expenseHeads = NasFreightsExpenseHead::where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'amount']);

        $employees = NasFreightsEmployee::where('branch_id', session('nas_freights_branch_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('nas-freights.freight-booking-expenses.create', [
            'expense'      => null,
            'expenseHeads' => $expenseHeads,
            'employees'    => $employees,
            'bookingType'  => $bookingType,
            'routePrefix'  => $routePrefix,
            'today'        => now()->format('Y-m-d'),
        ]);
    }

    public function store(StoreFreightBookingExpenseRequest $request)
    {
        $bookingType = $this->bookingType();
        $routePrefix = $this->routePrefix();

        DB::transaction(function () use ($request, $bookingType) {
            $totalExpense = collect($request->rows)->sum('expense_amount');
            $totalApproved = collect($request->rows)->sum('approved_amount');

            $expense = NasFreightsFreightBookingExpense::create([
                'expense_no'            => NasFreightsFreightBookingExpense::generateExpenseNo(),
                'booking_type'          => $bookingType,
                'booking_id'            => $request->booking_id ?: null,
                'booking_no'            => $request->booking_no ?: null,
                'employee_id'           => $request->employee_id ?: null,
                'invoice_no'            => $request->invoice_no ?: null,
                'invoice_value_usd'     => $request->invoice_value_usd ?: null,
                'bl_no'                 => $request->bl_no ?: null,
                'branch_id'             => session('nas_freights_branch_id'),
                'date'                  => $request->date,
                'total_expense_amount'  => $totalExpense,
                'total_approved_amount' => $totalApproved,
                'remarks'               => $request->remarks,
                'status'                => ($totalApproved > 0) ? 'Approved' : 'Draft',
                'entry_by'              => auth()->id(),
            ]);

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

        return redirect()->route($routePrefix.'.index')
            ->with('success', 'Freight booking expense created successfully.');
    }

    public function show(ShowFreightBookingExpenseRequest $request, NasFreightsFreightBookingExpense $freightBookingExpense)
    {
        $freightBookingExpense->load(['items.expenseHead.expenseCategory', 'branch', 'employee']);
        $routePrefix = $this->routePrefix();

        return view('nas-freights.freight-booking-expenses.show', compact('freightBookingExpense', 'routePrefix'));
    }

    public function edit(EditFreightBookingExpenseRequest $request, NasFreightsFreightBookingExpense $freightBookingExpense)
    {
        $freightBookingExpense->load('items');

        $bookingType = $this->bookingType();
        $routePrefix = $this->routePrefix();

        $expenseHeads = NasFreightsExpenseHead::where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'amount']);

        $employees = NasFreightsEmployee::where('branch_id', session('nas_freights_branch_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('nas-freights.freight-booking-expenses.create', [
            'expense'      => $freightBookingExpense,
            'expenseHeads' => $expenseHeads,
            'employees'    => $employees,
            'bookingType'  => $bookingType,
            'routePrefix'  => $routePrefix,
            'today'        => now()->format('Y-m-d'),
        ]);
    }

    public function update(UpdateFreightBookingExpenseRequest $request, NasFreightsFreightBookingExpense $freightBookingExpense)
    {
        $routePrefix = $this->routePrefix();

        DB::transaction(function () use ($request, $freightBookingExpense) {
            $totalExpense = collect($request->rows)->sum('expense_amount');
            $totalApproved = collect($request->rows)->sum('approved_amount');

            $freightBookingExpense->update([
                'booking_id'            => $request->booking_id ?: null,
                'booking_no'            => $request->booking_no ?: null,
                'employee_id'           => $request->employee_id ?: null,
                'invoice_no'            => $request->invoice_no ?: null,
                'invoice_value_usd'     => $request->invoice_value_usd ?: null,
                'bl_no'                 => $request->bl_no ?: null,
                'date'                  => $request->date,
                'total_expense_amount'  => $totalExpense,
                'total_approved_amount' => $totalApproved,
                'remarks'               => $request->remarks,
                'status'                => ($totalApproved > 0) ? 'Approved' : 'Draft',
            ]);

            $freightBookingExpense->items()->delete();

            foreach ($request->rows as $row) {
                NasFreightsFreightBookingExpenseItem::create([
                    'freight_booking_expense_id' => $freightBookingExpense->id,
                    'expense_head_id'            => $row['expense_head_id'],
                    'receiptable'                => $row['receiptable'] ?? 'No',
                    'expense_amount'             => $row['expense_amount'] ?? 0,
                    'approved_amount'            => $row['approved_amount'] ?? 0,
                    'expense_date'               => $row['expense_date'] ?? null,
                    'note'                       => $row['note'] ?? null,
                ]);
            }
        });

        return redirect()->route($routePrefix.'.show', $freightBookingExpense->id)
            ->with('success', 'Freight booking expense updated successfully.');
    }

    public function destroy(DestroyFreightBookingExpenseRequest $request, NasFreightsFreightBookingExpense $freightBookingExpense)
    {
        $freightBookingExpense->delete();

        return response()->json(['message' => 'Freight booking expense '.$freightBookingExpense->expense_no.' deleted.']);
    }

    public function searchBookings(IndexFreightBookingExpenseRequest $request)
    {
        $q = $request->get('q', '');
        $type = $this->bookingType();

        if ($type === 'export') {
            $results = NasFreightsFreightExportBooking::where('branch_id', session('nas_freights_branch_id'))
                ->where(fn ($s) => $s->where('export_booking_no', 'like', '%'.$q.'%'))
                ->limit(20)
                ->get(['id', 'export_booking_no', 'export_bl_no', 'invoice_no'])
                ->map(fn ($b) => [
                    'id'                => $b->id,
                    'text'              => $b->export_booking_no,
                    'bl_no'             => $b->export_bl_no,
                    'invoice_no'        => $b->invoice_no,
                    'invoice_value_usd' => null,
                ]);
        } else {
            $results = NasFreightsFreightBooking::where('branch_id', session('nas_freights_branch_id'))
                ->where(fn ($s) => $s->where('freight_booking_no', 'like', '%'.$q.'%'))
                ->limit(20)
                ->get(['id', 'freight_booking_no', 'bl_no'])
                ->map(fn ($b) => [
                    'id'                => $b->id,
                    'text'              => $b->freight_booking_no,
                    'bl_no'             => $b->bl_no,
                    'invoice_no'        => null,
                    'invoice_value_usd' => null,
                ]);
        }

        return response()->json($results);
    }
}
