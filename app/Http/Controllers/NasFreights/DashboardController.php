<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBooking;
use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\NasFreights\NasFreightsSupplierBill;
use App\Models\NasFreights\NasFreightsMoneyReceipt;
use App\Models\NasFreights\NasFreightsSupplierPayment;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsCustomerBillItem;
use App\Models\NasFreights\NasFreightsSupplier;

class DashboardController extends Controller
{
    public function index()
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $today      = now()->toDateString();

        // Bookings
        $stats['bookings_total']     = NasFreightsBooking::count();
        $stats['bookings_month']     = NasFreightsBooking::whereDate('job_date', '>=', $monthStart)->count();
        $stats['bookings_draft']     = NasFreightsBooking::where('status', 'Draft')->count();
        $stats['bookings_approved']  = NasFreightsBooking::where('status', 'Approved')->count();
        $stats['bookings_rejected']  = NasFreightsBooking::where('status', 'Rejected')->count();

        // Customer Bills
        $stats['cust_bills_draft']     = NasFreightsCustomerBill::where('status', 'Draft')->count();
        $stats['cust_bills_confirmed'] = NasFreightsCustomerBill::where('status', 'Approved')->count();
        $stats['cust_bills_paid']      = NasFreightsCustomerBill::where('status', 'Paid')->count();
        $stats['cust_due_amount']      = NasFreightsCustomerBill::where('status', 'Approved')->sum('total_amount');

        // Supplier Bills
        $stats['sup_bills_draft']     = NasFreightsSupplierBill::where('status', 'Draft')->count();
        $stats['sup_bills_confirmed'] = NasFreightsSupplierBill::where('status', 'Approved')->count();
        $stats['sup_bills_paid']      = NasFreightsSupplierBill::where('status', 'Paid')->count();
        $stats['sup_due_amount']      = NasFreightsSupplierBill::where('status', 'Approved')->sum('total_amount');

        // Collections
        $stats['receipts_month']  = NasFreightsMoneyReceipt::whereDate('receipt_date', '>=', $monthStart)->sum('amount_received');
        $stats['receipts_total']  = NasFreightsMoneyReceipt::sum('amount_received');
        $stats['payments_month']  = NasFreightsSupplierPayment::whereDate('payment_date', '>=', $monthStart)->sum('amount_paid');
        $stats['payments_total']  = NasFreightsSupplierPayment::sum('amount_paid');

        // Stakeholders
        $stats['total_customers'] = NasFreightsCustomer::where('status', 'Active')->count();
        $stats['total_suppliers'] = NasFreightsSupplier::where('is_active', true)->count();

        // Recent bookings
        $recentBookings = NasFreightsBooking::where('branch_id', session('nas_freights_branch_id'))
            ->latest()->limit(8)->get();
        $billedBookingIds = NasFreightsCustomerBillItem::whereIn('booking_id', $recentBookings->pluck('id'))
            ->pluck('booking_id')->flip()->toArray();

        // Customer due bills
        $customerDueBills = NasFreightsCustomerBill::where('status', 'Approved')->latest('bill_date')->limit(6)->get();

        // Supplier due bills
        $supplierDueBills = NasFreightsSupplierBill::where('status', 'Approved')->latest('bill_date')->limit(6)->get();

        return view('nas-freights.dashboard', compact('stats', 'recentBookings', 'billedBookingIds', 'customerDueBills', 'supplierDueBills'));
    }
}
