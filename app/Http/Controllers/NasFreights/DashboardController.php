<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBooking;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\NasFreights\NasFreightsCustomerBillItem;
use App\Models\NasFreights\NasFreightsMoneyReceipt;
use App\Models\NasFreights\NasFreightsSupplier;
use App\Models\NasFreights\NasFreightsSupplierBill;
use App\Models\NasFreights\NasFreightsSupplierPayment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $monthStart = now()->startOfMonth()->toDateString();

        $canSeeBooking = $user->hasPermission('freight.booking.list');
        $canSeeCustomerBill = $user->hasPermission('freight.customer-bill.list');
        $canSeeSupplierBill = $user->hasPermission('freight.supplier-bill.list');
        $canSeeDueList = $user->hasPermission('freight.due-list.view');
        $canSeeMoneyReceipt = $user->hasPermission('freight.money-receipt.list');
        $canSeeSupplierPayment = $user->hasPermission('freight.supplier-payment.list');
        $canSeeCustomer = $user->hasPermission('freight.customer.list');
        $canSeeSupplier = $user->hasPermission('freight.supplier.list');

        $stats = [];

        if ($canSeeBooking) {
            $stats['bookings_total'] = NasFreightsBooking::count();
            $stats['bookings_month'] = NasFreightsBooking::whereDate('job_date', '>=', $monthStart)->count();
            $stats['bookings_draft'] = NasFreightsBooking::where('status', 'Draft')->count();
            $stats['bookings_approved'] = NasFreightsBooking::where('status', 'Approved')->count();
            $stats['bookings_rejected'] = NasFreightsBooking::where('status', 'Rejected')->count();
        }

        if ($canSeeCustomerBill || $canSeeDueList) {
            $stats['cust_bills_draft'] = NasFreightsCustomerBill::where('status', 'Draft')->count();
            $stats['cust_bills_confirmed'] = NasFreightsCustomerBill::where('status', 'Approved')->count();
            $stats['cust_bills_paid'] = NasFreightsCustomerBill::where('status', 'Paid')->count();
            $stats['cust_due_amount'] = NasFreightsCustomerBill::where('status', 'Approved')->sum('total_amount');
        }

        if ($canSeeSupplierBill || $canSeeDueList) {
            $stats['sup_bills_draft'] = NasFreightsSupplierBill::where('status', 'Draft')->count();
            $stats['sup_bills_confirmed'] = NasFreightsSupplierBill::where('status', 'Approved')->count();
            $stats['sup_bills_paid'] = NasFreightsSupplierBill::where('status', 'Paid')->count();
            $stats['sup_due_amount'] = NasFreightsSupplierBill::where('status', 'Approved')->sum('total_amount');
        }

        if ($canSeeMoneyReceipt) {
            $stats['receipts_month'] = NasFreightsMoneyReceipt::whereDate('receipt_date', '>=', $monthStart)->sum('amount_received');
            $stats['receipts_total'] = NasFreightsMoneyReceipt::sum('amount_received');
        }

        if ($canSeeSupplierPayment) {
            $stats['payments_month'] = NasFreightsSupplierPayment::whereDate('payment_date', '>=', $monthStart)->sum('amount_paid');
            $stats['payments_total'] = NasFreightsSupplierPayment::sum('amount_paid');
        }

        if ($canSeeCustomer) {
            $stats['total_customers'] = NasFreightsCustomer::where('status', 'Active')->count();
        }

        if ($canSeeSupplier) {
            $stats['total_suppliers'] = NasFreightsSupplier::where('is_active', true)->count();
        }

        $recentBookings = $canSeeBooking
            ? NasFreightsBooking::where('branch_id', session('nas_freights_branch_id'))->latest()->limit(8)->get()
            : collect();

        $billedBookingIds = $recentBookings->isNotEmpty()
            ? NasFreightsCustomerBillItem::whereIn('booking_id', $recentBookings->pluck('id'))->pluck('booking_id')->flip()->toArray()
            : [];

        $customerDueBills = $canSeeDueList
            ? NasFreightsCustomerBill::where('status', 'Approved')->latest('bill_date')->limit(6)->get()
            : collect();

        $supplierDueBills = $canSeeDueList
            ? NasFreightsSupplierBill::where('status', 'Approved')->latest('bill_date')->limit(6)->get()
            : collect();

        return view('nas-freights.dashboard', compact(
            'stats', 'recentBookings', 'billedBookingIds', 'customerDueBills', 'supplierDueBills',
            'canSeeBooking', 'canSeeCustomerBill', 'canSeeSupplierBill', 'canSeeDueList',
            'canSeeMoneyReceipt', 'canSeeSupplierPayment', 'canSeeCustomer', 'canSeeSupplier',
        ));
    }
}
