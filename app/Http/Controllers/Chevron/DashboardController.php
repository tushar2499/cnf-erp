<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronEmployee;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobExpense;
use App\Models\Chevron\ChevronMoneyReceipt;

class DashboardController extends Controller
{
    public function index()
    {
        $branchId = session('active_branch_id');

        // --- Stat cards ---
        $totalJobs        = ChevronJob::where('branch_id', $branchId)->count();
        $jobsThisMonth    = ChevronJob::where('branch_id', $branchId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $activeJobs       = ChevronJob::where('branch_id', $branchId)->where('status', 'Active')->count();
        $pendingJobs      = ChevronJob::where('branch_id', $branchId)->where('status', 'Pending')->count();
        $closedJobs       = ChevronJob::where('branch_id', $branchId)->where('status', 'Closed')->count();

        $totalBills       = ChevronBill::where('branch_id', $branchId)->count();
        $totalReceivable  = ChevronBill::where('branch_id', $branchId)->sum('due_amount');
        $totalNetPayable  = ChevronBill::where('branch_id', $branchId)->sum('net_payable');

        $totalReceipts        = ChevronMoneyReceipt::where('branch_id', $branchId)->sum('total_amount');
        $receiptsThisMonth    = ChevronMoneyReceipt::where('branch_id', $branchId)->whereMonth('receipt_date', now()->month)
                                    ->whereYear('receipt_date', now()->year)->sum('total_amount');

        $totalCustomers   = ChevronCustomer::count();
        $totalEmployees   = ChevronEmployee::where('branch_id', $branchId)->count();

        $approvedExpenses = ChevronJobExpense::where('branch_id', $branchId)->where('status', 'Approved')->sum('total_approved_amount');

        // --- Monthly jobs last 6 months (for chart) ---
        $monthlyLabels   = [];
        $monthlyJobData  = [];
        $monthlyBillData = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $monthlyLabels[]   = $d->format('M y');
            $monthlyJobData[]  = ChevronJob::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->count();
            $monthlyBillData[] = round(
                ChevronBill::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->sum('net_payable'),
                2
            );
        }

        // --- Bill status breakdown (for donut) ---
        $billStatusCounts = ChevronBill::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')->pluck('cnt', 'status');

        // --- Top 5 customers by job count ---
        $topCustomers = ChevronJob::selectRaw('party_name, COUNT(*) as job_count')
            ->groupBy('party_name')
            ->orderByDesc('job_count')
            ->limit(5)
            ->get();

        // --- Recent jobs ---
        $recentJobs = ChevronJob::with('port')->latest()->limit(8)->get();

        // --- Recent bills ---
        $recentBills = ChevronBill::latest()->limit(6)->get();

        return view('chevron.dashboard', compact(
            'totalJobs', 'jobsThisMonth', 'activeJobs', 'pendingJobs', 'closedJobs',
            'totalBills', 'totalReceivable', 'totalNetPayable',
            'totalReceipts', 'receiptsThisMonth',
            'totalCustomers', 'totalEmployees', 'approvedExpenses',
            'monthlyLabels', 'monthlyJobData', 'monthlyBillData',
            'billStatusCounts', 'topCustomers',
            'recentJobs', 'recentBills'
        ));
    }
}
