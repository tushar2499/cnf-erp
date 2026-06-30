<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingLc;
use App\Models\NasTrading\NasTradingCustomerBill;
use App\Models\NasTrading\NasTradingDelivery;
use App\Models\NasTrading\NasTradingShipment;
use App\Models\NasTrading\NasTradingMoneyReceipt;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'open_lcs'          => NasTradingLc::where('lc_status', 'Open')->count(),
            'draft_bills'       => NasTradingCustomerBill::where('status', 'Draft')->count(),
            'confirmed_bills'   => NasTradingCustomerBill::where('status', 'Confirmed')->count(),
            'total_due'         => NasTradingCustomerBill::where('status', 'Confirmed')->sum('total_amount'),
            'pending_deliveries'=> NasTradingDelivery::where('delivery_status', 'Pending')->count(),
            'in_transit_ships'  => NasTradingShipment::where('shipment_status', 'In Transit')->count(),
            'receipts_today'    => NasTradingMoneyReceipt::whereDate('receipt_date', today())->sum('amount_received'),
            'total_lcs'         => NasTradingLc::count(),
        ];

        $recentLcs = NasTradingLc::latest()->limit(8)->get();
        $recentBills = NasTradingCustomerBill::where('status', 'Confirmed')->latest()->limit(5)->get();

        return view('nas-trading.dashboard', compact('stats', 'recentLcs', 'recentBills'));
    }
}
