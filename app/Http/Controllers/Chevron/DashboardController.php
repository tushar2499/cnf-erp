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
        $user = auth()->user();

        $canJob = $user->hasPermission('cnf.job.list');
        $canBill = $user->hasPermission('cnf.bill.list');
        $canReceipt = $user->hasPermission('cnf.money-receipt.list');
        $canJobExpense = $user->hasPermission('cnf.job-expense.list');
        $canCustomer = $user->hasPermission('cnf.customer.list');

        // --- Stat cards ---
        $totalJobs = $canJob ? ChevronJob::where('branch_id', $branchId)->count() : null;
        $jobsThisMonth = $canJob ? ChevronJob::where('branch_id', $branchId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() : null;
        $activeJobs = $canJob ? ChevronJob::where('branch_id', $branchId)->where('status', 'Active')->count() : null;
        $pendingJobs = $canJob ? ChevronJob::where('branch_id', $branchId)->where('status', 'Pending')->count() : null;
        $closedJobs = $canJob ? ChevronJob::where('branch_id', $branchId)->where('status', 'Closed')->count() : null;

        $totalBills = $canBill ? ChevronBill::where('branch_id', $branchId)->count() : null;
        $totalReceivable = $canBill ? ChevronBill::where('branch_id', $branchId)->sum('due_amount') : null;
        $totalNetPayable = $canBill ? ChevronBill::where('branch_id', $branchId)->sum('net_payable') : null;

        $totalReceipts = $canReceipt ? ChevronMoneyReceipt::where('branch_id', $branchId)->sum('total_amount') : null;
        $receiptsThisMonth = $canReceipt ? ChevronMoneyReceipt::where('branch_id', $branchId)->whereMonth('receipt_date', now()->month)->whereYear('receipt_date', now()->year)->sum('total_amount') : null;

        $totalCustomers = $canCustomer ? ChevronCustomer::count() : null;
        $totalEmployees = ChevronEmployee::where('branch_id', $branchId)->count();

        $approvedExpenses = $canJobExpense ? ChevronJobExpense::where('branch_id', $branchId)->where('status', 'Approved')->sum('total_approved_amount') : null;

        // --- Monthly chart (last 6 months) ---
        $monthlyLabels = [];
        $monthlyJobData = [];
        $monthlyBillData = [];
        if ($canJob || $canBill) {
            for ($i = 5; $i >= 0; $i--) {
                $d = now()->subMonths($i);
                $monthlyLabels[] = $d->format('M y');
                $monthlyJobData[] = $canJob ? ChevronJob::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->count() : 0;
                $monthlyBillData[] = $canBill ? round(ChevronBill::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->sum('net_payable'), 2) : 0;
            }
        }

        // --- Bill status breakdown (donut) ---
        $billStatusCounts = $canBill
            ? ChevronBill::selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status')
            : collect();

        // --- Top 5 customers by job count ---
        $topCustomers = $canJob
            ? ChevronJob::selectRaw('party_name, COUNT(*) as job_count')->groupBy('party_name')->orderByDesc('job_count')->limit(5)->get()
            : collect();

        // --- Recent jobs ---
        $recentJobs = $canJob ? ChevronJob::with('port')->latest()->limit(8)->get() : collect();

        // --- Recent bills ---
        $recentBills = $canBill ? ChevronBill::latest()->limit(6)->get() : collect();

        return view('chevron.dashboard', compact(
            'canJob', 'canBill', 'canReceipt', 'canJobExpense', 'canCustomer',
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
