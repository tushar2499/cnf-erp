<?php

use App\Http\Controllers\NasFreights\BookingController;
use App\Http\Controllers\NasFreights\BranchController;
use App\Http\Controllers\NasFreights\BranchSelectController;
use App\Http\Controllers\NasFreights\ContainerTypeController;
use App\Http\Controllers\NasFreights\CustomerBillController;
use App\Http\Controllers\NasFreights\CustomerController;
use App\Http\Controllers\NasFreights\DashboardController;
use App\Http\Controllers\NasFreights\DueListController;
use App\Http\Controllers\NasFreights\EmployeeController;
use App\Http\Controllers\NasFreights\FreightBookingController;
use App\Http\Controllers\NasFreights\ImportController;
use App\Http\Controllers\NasFreights\MoneyReceiptController;
use App\Http\Controllers\NasFreights\OverseasAgentController;
use App\Http\Controllers\NasFreights\PackageTypeController;
use App\Http\Controllers\NasFreights\ReportController;
use App\Http\Controllers\NasFreights\RfqController;
use App\Http\Controllers\NasFreights\ShippingCarrierController;
use App\Http\Controllers\NasFreights\SupplierBillController;
use App\Http\Controllers\NasFreights\SupplierController;
use App\Http\Controllers\NasFreights\SupplierPaymentController;
use App\Http\Controllers\NasFreights\UserController;
use App\Http\Controllers\NasFreights\VehicleController;
use Illuminate\Support\Facades\Route;

// Branch select (exempt from branch middleware — handled in EnsureBranchSelected)
Route::get('/select-branch', [BranchSelectController::class, 'show'])->name('select-branch');
Route::post('/select-branch', [BranchSelectController::class, 'store'])->name('select-branch.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// RFQs
Route::prefix('rfqs')->name('rfqs.')->group(function () {
    Route::get('/search-customers', [RfqController::class, 'searchCustomers'])->name('search-customers');
    Route::get('/search-employees', [RfqController::class, 'searchEmployees'])->name('search-employees');
    Route::get('/search-overseas-agents', [RfqController::class, 'searchOverseasAgents'])->name('search-overseas-agents');
    Route::get('/search-shipping-carriers', [RfqController::class, 'searchShippingCarriers'])->name('search-shipping-carriers');
    Route::get('/', [RfqController::class, 'index'])->name('index');
    Route::get('/create', [RfqController::class, 'create'])->name('create');
    Route::post('/', [RfqController::class, 'store'])->name('store');
    Route::get('/{rfq}', [RfqController::class, 'show'])->name('show');
    Route::get('/{rfq}/edit', [RfqController::class, 'edit'])->name('edit');
    Route::put('/{rfq}', [RfqController::class, 'update'])->name('update');
    Route::patch('/{rfq}/status', [RfqController::class, 'updateStatus'])->name('update-status');
    Route::post('/{rfq}/convert-freight-booking', [RfqController::class, 'convertToFreightBooking'])->name('convert-freight-booking');
    Route::delete('/{rfq}', [RfqController::class, 'destroy'])->name('destroy');
});

// Freight Bookings
Route::prefix('freight-bookings')->name('freight-bookings.')->group(function () {
    Route::get('/search-customers', [FreightBookingController::class, 'searchCustomers'])->name('search-customers');
    Route::get('/search-employees', [FreightBookingController::class, 'searchEmployees'])->name('search-employees');
    Route::get('/search-overseas-agents', [FreightBookingController::class, 'searchOverseasAgents'])->name('search-overseas-agents');
    Route::get('/search-shipping-carriers', [FreightBookingController::class, 'searchShippingCarriers'])->name('search-shipping-carriers');
    Route::get('/', [FreightBookingController::class, 'index'])->name('index');
    Route::get('/create', [FreightBookingController::class, 'create'])->name('create');
    Route::post('/', [FreightBookingController::class, 'store'])->name('store');
    Route::get('/{freightBooking}', [FreightBookingController::class, 'show'])->name('show');
    Route::get('/{freightBooking}/edit', [FreightBookingController::class, 'edit'])->name('edit');
    Route::put('/{freightBooking}', [FreightBookingController::class, 'update'])->name('update');
    Route::delete('/{freightBooking}', [FreightBookingController::class, 'destroy'])->name('destroy');
});

// Transport Bookings
Route::prefix('bookings')->name('bookings.')->group(function () {
    Route::get('/search-employees', [BookingController::class, 'searchEmployees'])->name('search-employees');
    Route::get('/search-customers', [BookingController::class, 'searchCustomers'])->name('search-customers');
    Route::get('/search-vehicles', [BookingController::class, 'searchVehicles'])->name('search-vehicles');
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::get('/create', [BookingController::class, 'create'])->name('create');
    Route::post('/', [BookingController::class, 'store'])->name('store');
    Route::get('/{booking}/edit', [BookingController::class, 'edit'])->name('edit');
    Route::put('/{booking}', [BookingController::class, 'update'])->name('update');
    Route::delete('/{booking}', [BookingController::class, 'destroy'])->name('destroy');
    Route::patch('/{booking}/confirm', [BookingController::class, 'confirm'])->name('confirm');
    Route::patch('/{booking}/reject', [BookingController::class, 'reject'])->name('reject');
});

// Customer Bills
Route::prefix('customer-bills')->name('customer-bills.')->group(function () {
    Route::get('/search-customers', [CustomerBillController::class, 'searchCustomers'])->name('search-customers');
    Route::post('/load-items', [CustomerBillController::class, 'loadItems'])->name('load-items');
    Route::get('/', [CustomerBillController::class, 'index'])->name('index');
    Route::get('/create', [CustomerBillController::class, 'create'])->name('create');
    Route::post('/', [CustomerBillController::class, 'store'])->name('store');
    Route::get('/{customerBill}', [CustomerBillController::class, 'show'])->name('show');
    Route::get('/{customerBill}/edit', [CustomerBillController::class, 'edit'])->name('edit');
    Route::put('/{customerBill}', [CustomerBillController::class, 'update'])->name('update');
    Route::get('/{customerBill}/print', [CustomerBillController::class, 'printView'])->name('print');
    Route::get('/{customerBill}/mushak', [CustomerBillController::class, 'mushakView'])->name('mushak');
    Route::get('/{customerBill}/excel', [CustomerBillController::class, 'billExcel'])->name('excel');
    Route::patch('/{customerBill}/confirm', [CustomerBillController::class, 'confirm'])->name('confirm');
    Route::delete('/{customerBill}', [CustomerBillController::class, 'destroy'])->name('destroy');
});

// Due Lists
Route::prefix('due-lists')->name('due-lists.')->group(function () {
    Route::get('/customer-search', [DueListController::class, 'searchCustomers'])->name('customer-search');
    Route::get('/supplier-search', [DueListController::class, 'searchSuppliers'])->name('supplier-search');
    Route::get('/customer', [DueListController::class, 'customerDue'])->name('customer');
    Route::get('/supplier', [DueListController::class, 'supplierDue'])->name('supplier');
});

// Supplier Bills (Payment Orders)
Route::prefix('supplier-bills')->name('supplier-bills.')->group(function () {
    Route::get('/search-suppliers', [SupplierBillController::class, 'searchSuppliers'])->name('search-suppliers');
    Route::post('/load-items', [SupplierBillController::class, 'loadItems'])->name('load-items');
    Route::get('/', [SupplierBillController::class, 'index'])->name('index');
    Route::get('/create', [SupplierBillController::class, 'create'])->name('create');
    Route::post('/', [SupplierBillController::class, 'store'])->name('store');
    Route::get('/{supplierBill}', [SupplierBillController::class, 'show'])->name('show');
    Route::get('/{supplierBill}/edit', [SupplierBillController::class, 'edit'])->name('edit');
    Route::put('/{supplierBill}', [SupplierBillController::class, 'update'])->name('update');
    Route::get('/{supplierBill}/print', [SupplierBillController::class, 'printView'])->name('print');
    Route::patch('/{supplierBill}/confirm', [SupplierBillController::class, 'confirm'])->name('confirm');
    Route::delete('/{supplierBill}', [SupplierBillController::class, 'destroy'])->name('destroy');
});

// Money Receipts (Customer Payments)
Route::prefix('money-receipts')->name('money-receipts.')->group(function () {
    Route::get('/search-customers', [MoneyReceiptController::class, 'searchCustomers'])->name('search-customers');
    Route::get('/get-bills', [MoneyReceiptController::class, 'getBills'])->name('get-bills');
    Route::get('/', [MoneyReceiptController::class, 'index'])->name('index');
    Route::get('/create', [MoneyReceiptController::class, 'create'])->name('create');
    Route::post('/', [MoneyReceiptController::class, 'store'])->name('store');
    Route::get('/{moneyReceipt}', [MoneyReceiptController::class, 'show'])->name('show');
    Route::get('/{moneyReceipt}/print', [MoneyReceiptController::class, 'printView'])->name('print');
});

// Supplier Payments
Route::prefix('supplier-payments')->name('supplier-payments.')->group(function () {
    Route::get('/search-suppliers', [SupplierPaymentController::class, 'searchSuppliers'])->name('search-suppliers');
    Route::get('/get-bills', [SupplierPaymentController::class, 'getBills'])->name('get-bills');
    Route::get('/', [SupplierPaymentController::class, 'index'])->name('index');
    Route::get('/create', [SupplierPaymentController::class, 'create'])->name('create');
    Route::post('/', [SupplierPaymentController::class, 'store'])->name('store');
    Route::get('/{supplierPayment}', [SupplierPaymentController::class, 'show'])->name('show');
});

// Vehicles
Route::prefix('vehicles')->name('vehicles.')->group(function () {
    Route::get('/suppliers-search', [VehicleController::class, 'searchSuppliers'])->name('suppliers-search');
    Route::get('/', [VehicleController::class, 'index'])->name('index');
    Route::post('/', [VehicleController::class, 'store'])->name('store');
    Route::get('/{vehicle}', [VehicleController::class, 'show'])->name('show');
    Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
    Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');
});

// Employees
Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('index');
    Route::post('/', [EmployeeController::class, 'store'])->name('store');
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
});

// Stakeholders
Route::prefix('stakeholders')->name('stakeholders.')->group(function () {
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/next-id', [CustomerController::class, 'nextId'])->name('next-id');
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });
});

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/booking', [ReportController::class, 'bookingReport'])->name('booking');
    Route::get('/booking/print', [ReportController::class, 'bookingReportPrint'])->name('booking.print');
    Route::get('/booking/pdf', [ReportController::class, 'bookingReportPdf'])->name('booking.pdf');
    Route::get('/booking/excel', [ReportController::class, 'bookingReportExcel'])->name('booking.excel');
    Route::get('/party-bill-summary', [ReportController::class, 'partyBillSummary'])->name('party-bill-summary');
    Route::get('/party-bill-summary/print', [ReportController::class, 'partyBillSummaryPrint'])->name('party-bill-summary.print');
    Route::get('/party-bill-summary/pdf', [ReportController::class, 'partyBillSummaryPdf'])->name('party-bill-summary.pdf');
    Route::get('/party-bill-summary/excel', [ReportController::class, 'partyBillSummaryExcel'])->name('party-bill-summary.excel');
    Route::get('/bill-details', [ReportController::class, 'billDetails'])->name('bill-details');
    Route::get('/bill-details/print', [ReportController::class, 'billDetailsPrint'])->name('bill-details.print');
    Route::get('/bill-details/pdf', [ReportController::class, 'billDetailsPdf'])->name('bill-details.pdf');
    Route::get('/bill-details/excel', [ReportController::class, 'billDetailsExcel'])->name('bill-details.excel');
});

// Users
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

// Import
Route::prefix('import')->name('import.')->group(function () {
    Route::get('/supplier-payments', [ImportController::class, 'supplierPaymentsIndex'])->name('supplier-payments');
    Route::post('/supplier-payments/preview', [ImportController::class, 'supplierPaymentsPreview'])->name('supplier-payments.preview');
    Route::post('/supplier-payments/import', [ImportController::class, 'supplierPaymentsImport'])->name('supplier-payments.import');

    Route::get('/customer-bills', [ImportController::class, 'customerBillsIndex'])->name('customer-bills');
    Route::post('/customer-bills/preview', [ImportController::class, 'customerBillsPreview'])->name('customer-bills.preview');
    Route::post('/customer-bills/import', [ImportController::class, 'customerBillsImport'])->name('customer-bills.import');

    Route::get('/bookings', [ImportController::class, 'bookingsIndex'])->name('bookings');
    Route::post('/bookings/preview', [ImportController::class, 'bookingsPreview'])->name('bookings.preview');
    Route::post('/bookings/import', [ImportController::class, 'bookingsImport'])->name('bookings.import');

    Route::get('/vehicles', [ImportController::class, 'vehiclesIndex'])->name('vehicles');
    Route::post('/vehicles/preview', [ImportController::class, 'vehiclesPreview'])->name('vehicles.preview');
    Route::post('/vehicles/import', [ImportController::class, 'vehiclesImport'])->name('vehicles.import');

    Route::get('/booking-updates', [ImportController::class, 'bookingUpdatesIndex'])->name('booking-updates');
    Route::post('/booking-updates/preview', [ImportController::class, 'bookingUpdatesPreview'])->name('booking-updates.preview');
    Route::post('/booking-updates/import', [ImportController::class, 'bookingUpdatesImport'])->name('booking-updates.import');

    Route::get('/customer-bill-summary', [ImportController::class, 'customerBillSummaryIndex'])->name('customer-bill-summary');
    Route::post('/customer-bill-summary/preview', [ImportController::class, 'customerBillSummaryPreview'])->name('customer-bill-summary.preview');
    Route::post('/customer-bill-summary/import', [ImportController::class, 'customerBillSummaryImport'])->name('customer-bill-summary.import');
});

// Settings — Branches
Route::prefix('settings')->name('settings.')->group(function () {
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('container-types')->name('container-types.')->group(function () {
        Route::get('/', [ContainerTypeController::class, 'index'])->name('index');
        Route::post('/', [ContainerTypeController::class, 'store'])->name('store');
        Route::put('/{containerType}', [ContainerTypeController::class, 'update'])->name('update');
        Route::delete('/{containerType}', [ContainerTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('package-types')->name('package-types.')->group(function () {
        Route::get('/', [PackageTypeController::class, 'index'])->name('index');
        Route::post('/', [PackageTypeController::class, 'store'])->name('store');
        Route::put('/{packageType}', [PackageTypeController::class, 'update'])->name('update');
        Route::delete('/{packageType}', [PackageTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('overseas-agents')->name('overseas-agents.')->group(function () {
        Route::get('/', [OverseasAgentController::class, 'index'])->name('index');
        Route::post('/', [OverseasAgentController::class, 'store'])->name('store');
        Route::put('/{overseasAgent}', [OverseasAgentController::class, 'update'])->name('update');
        Route::delete('/{overseasAgent}', [OverseasAgentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('shipping-carriers')->name('shipping-carriers.')->group(function () {
        Route::get('/', [ShippingCarrierController::class, 'index'])->name('index');
        Route::post('/', [ShippingCarrierController::class, 'store'])->name('store');
        Route::put('/{shippingCarrier}', [ShippingCarrierController::class, 'update'])->name('update');
        Route::delete('/{shippingCarrier}', [ShippingCarrierController::class, 'destroy'])->name('destroy');
    });
});
