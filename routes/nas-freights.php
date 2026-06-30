<?php

use App\Http\Controllers\NasFreights\UserController;
use App\Http\Controllers\NasFreights\BookingController;
use App\Http\Controllers\NasFreights\ReportController;
use App\Http\Controllers\NasFreights\BranchController;
use App\Http\Controllers\NasFreights\BranchSelectController;
use App\Http\Controllers\NasFreights\CustomerBillController;
use App\Http\Controllers\NasFreights\DueListController;
use App\Http\Controllers\NasFreights\MoneyReceiptController;
use App\Http\Controllers\NasFreights\SupplierBillController;
use App\Http\Controllers\NasFreights\SupplierPaymentController;
use App\Http\Controllers\NasFreights\CustomerController;
use App\Http\Controllers\NasFreights\DashboardController;
use App\Http\Controllers\NasFreights\EmployeeController;
use App\Http\Controllers\NasFreights\SupplierController;
use App\Http\Controllers\NasFreights\VehicleController;
use Illuminate\Support\Facades\Route;

// Branch select (exempt from branch middleware — handled in EnsureBranchSelected)
Route::get('/select-branch',  [BranchSelectController::class, 'show'])->name('select-branch');
Route::post('/select-branch', [BranchSelectController::class, 'store'])->name('select-branch.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Bookings
Route::prefix('bookings')->name('bookings.')->group(function () {
    Route::get('/search-employees', [BookingController::class, 'searchEmployees'])->name('search-employees');
    Route::get('/search-customers', [BookingController::class, 'searchCustomers'])->name('search-customers');
    Route::get('/search-vehicles',  [BookingController::class, 'searchVehicles'])->name('search-vehicles');
    Route::get('/',                 [BookingController::class, 'index'])->name('index');
    Route::get('/create',           [BookingController::class, 'create'])->name('create');
    Route::post('/',                [BookingController::class, 'store'])->name('store');
    Route::get('/{booking}/edit',      [BookingController::class, 'edit'])->name('edit');
    Route::put('/{booking}',           [BookingController::class, 'update'])->name('update');
    Route::delete('/{booking}',        [BookingController::class, 'destroy'])->name('destroy');
    Route::patch('/{booking}/confirm', [BookingController::class, 'confirm'])->name('confirm');
    Route::patch('/{booking}/reject',  [BookingController::class, 'reject'])->name('reject');
});

// Customer Bills
Route::prefix('customer-bills')->name('customer-bills.')->group(function () {
    Route::get('/search-customers',      [CustomerBillController::class, 'searchCustomers'])->name('search-customers');
    Route::post('/load-items',           [CustomerBillController::class, 'loadItems'])->name('load-items');
    Route::get('/',                      [CustomerBillController::class, 'index'])->name('index');
    Route::get('/create',                [CustomerBillController::class, 'create'])->name('create');
    Route::post('/',                     [CustomerBillController::class, 'store'])->name('store');
    Route::get('/{customerBill}',           [CustomerBillController::class, 'show'])->name('show');
    Route::get('/{customerBill}/edit',      [CustomerBillController::class, 'edit'])->name('edit');
    Route::put('/{customerBill}',           [CustomerBillController::class, 'update'])->name('update');
    Route::get('/{customerBill}/print',     [CustomerBillController::class, 'printView'])->name('print');
    Route::patch('/{customerBill}/confirm', [CustomerBillController::class, 'confirm'])->name('confirm');
    Route::delete('/{customerBill}',        [CustomerBillController::class, 'destroy'])->name('destroy');
});

// Due Lists
Route::prefix('due-lists')->name('due-lists.')->group(function () {
    Route::get('/customer-search', [DueListController::class, 'searchCustomers'])->name('customer-search');
    Route::get('/supplier-search', [DueListController::class, 'searchSuppliers'])->name('supplier-search');
    Route::get('/customer',        [DueListController::class, 'customerDue'])->name('customer');
    Route::get('/supplier',        [DueListController::class, 'supplierDue'])->name('supplier');
});

// Supplier Bills (Payment Orders)
Route::prefix('supplier-bills')->name('supplier-bills.')->group(function () {
    Route::get('/search-suppliers',      [SupplierBillController::class, 'searchSuppliers'])->name('search-suppliers');
    Route::post('/load-items',           [SupplierBillController::class, 'loadItems'])->name('load-items');
    Route::get('/',                      [SupplierBillController::class, 'index'])->name('index');
    Route::get('/create',                [SupplierBillController::class, 'create'])->name('create');
    Route::post('/',                     [SupplierBillController::class, 'store'])->name('store');
    Route::get('/{supplierBill}',           [SupplierBillController::class, 'show'])->name('show');
    Route::get('/{supplierBill}/edit',      [SupplierBillController::class, 'edit'])->name('edit');
    Route::put('/{supplierBill}',           [SupplierBillController::class, 'update'])->name('update');
    Route::get('/{supplierBill}/print',     [SupplierBillController::class, 'printView'])->name('print');
    Route::patch('/{supplierBill}/confirm', [SupplierBillController::class, 'confirm'])->name('confirm');
    Route::delete('/{supplierBill}',        [SupplierBillController::class, 'destroy'])->name('destroy');
});

// Money Receipts (Customer Payments)
Route::prefix('money-receipts')->name('money-receipts.')->group(function () {
    Route::get('/search-customers', [MoneyReceiptController::class, 'searchCustomers'])->name('search-customers');
    Route::get('/get-bills',        [MoneyReceiptController::class, 'getBills'])->name('get-bills');
    Route::get('/',                 [MoneyReceiptController::class, 'index'])->name('index');
    Route::get('/create',           [MoneyReceiptController::class, 'create'])->name('create');
    Route::post('/',                [MoneyReceiptController::class, 'store'])->name('store');
    Route::get('/{moneyReceipt}',        [MoneyReceiptController::class, 'show'])->name('show');
    Route::get('/{moneyReceipt}/print',  [MoneyReceiptController::class, 'printView'])->name('print');
});

// Supplier Payments
Route::prefix('supplier-payments')->name('supplier-payments.')->group(function () {
    Route::get('/search-suppliers',   [SupplierPaymentController::class, 'searchSuppliers'])->name('search-suppliers');
    Route::get('/get-bills',          [SupplierPaymentController::class, 'getBills'])->name('get-bills');
    Route::get('/',                   [SupplierPaymentController::class, 'index'])->name('index');
    Route::get('/create',             [SupplierPaymentController::class, 'create'])->name('create');
    Route::post('/',                  [SupplierPaymentController::class, 'store'])->name('store');
    Route::get('/{supplierPayment}',  [SupplierPaymentController::class, 'show'])->name('show');
});

// Vehicles
Route::prefix('vehicles')->name('vehicles.')->group(function () {
    Route::get('/suppliers-search',      [VehicleController::class, 'searchSuppliers'])->name('suppliers-search');
    Route::get('/',                      [VehicleController::class, 'index'])->name('index');
    Route::post('/',                     [VehicleController::class, 'store'])->name('store');
    Route::get('/{vehicle}',             [VehicleController::class, 'show'])->name('show');
    Route::put('/{vehicle}',             [VehicleController::class, 'update'])->name('update');
    Route::delete('/{vehicle}',          [VehicleController::class, 'destroy'])->name('destroy');
});

// Employees
Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/',               [EmployeeController::class, 'index'])->name('index');
    Route::post('/',              [EmployeeController::class, 'store'])->name('store');
    Route::get('/{employee}',     [EmployeeController::class, 'show'])->name('show');
    Route::put('/{employee}',     [EmployeeController::class, 'update'])->name('update');
    Route::delete('/{employee}',  [EmployeeController::class, 'destroy'])->name('destroy');
});

// Stakeholders
Route::prefix('stakeholders')->name('stakeholders.')->group(function () {
    Route::get('/suppliers',                [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers',               [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}',     [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}',  [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('/customers/next-id',        [CustomerController::class, 'nextId'])->name('customers.next-id');
    Route::get('/customers',                [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers',               [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}',     [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}',     [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}',  [CustomerController::class, 'destroy'])->name('customers.destroy');
});

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/booking',                   [ReportController::class, 'bookingReport'])->name('booking');
    Route::get('/booking/print',             [ReportController::class, 'bookingReportPrint'])->name('booking.print');
    Route::get('/booking/pdf',               [ReportController::class, 'bookingReportPdf'])->name('booking.pdf');
    Route::get('/booking/excel',             [ReportController::class, 'bookingReportExcel'])->name('booking.excel');
    Route::get('/party-bill-summary',        [ReportController::class, 'partyBillSummary'])->name('party-bill-summary');
    Route::get('/party-bill-summary/print',  [ReportController::class, 'partyBillSummaryPrint'])->name('party-bill-summary.print');
    Route::get('/party-bill-summary/pdf',    [ReportController::class, 'partyBillSummaryPdf'])->name('party-bill-summary.pdf');
    Route::get('/party-bill-summary/excel',  [ReportController::class, 'partyBillSummaryExcel'])->name('party-bill-summary.excel');
    Route::get('/bill-details',              [ReportController::class, 'billDetails'])->name('bill-details');
    Route::get('/bill-details/print',        [ReportController::class, 'billDetailsPrint'])->name('bill-details.print');
    Route::get('/bill-details/pdf',          [ReportController::class, 'billDetailsPdf'])->name('bill-details.pdf');
    Route::get('/bill-details/excel',        [ReportController::class, 'billDetailsExcel'])->name('bill-details.excel');
});

// Users
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/',          [UserController::class, 'index'])->name('index');
    Route::post('/',         [UserController::class, 'store'])->name('store');
    Route::get('/{user}',    [UserController::class, 'show'])->name('show');
    Route::put('/{user}',    [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

// Settings — Branches
Route::prefix('settings')->name('settings.')->group(function () {
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/',            [BranchController::class, 'index'])->name('index');
        Route::post('/',           [BranchController::class, 'store'])->name('store');
        Route::put('/{branch}',    [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
    });
});
