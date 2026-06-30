<?php

use App\Http\Controllers\NasTrading\BankController;
use App\Http\Controllers\NasTrading\BranchController;
use App\Http\Controllers\NasTrading\BranchSelectController;
use App\Http\Controllers\NasTrading\CnfAgentController;
use App\Http\Controllers\NasTrading\CustomerController;
use App\Http\Controllers\NasTrading\DashboardController;
use App\Http\Controllers\NasTrading\EmployeeController;
use App\Http\Controllers\NasTrading\ExpenseHeadController;
use App\Http\Controllers\NasTrading\ImporterController;
use App\Http\Controllers\NasTrading\ItemController;
use App\Http\Controllers\NasTrading\LcController;
use App\Http\Controllers\NasTrading\LcExpenseController;
use App\Http\Controllers\NasTrading\PortController;
use App\Http\Controllers\NasTrading\PsiCompanyController;
use App\Http\Controllers\NasTrading\ShipmentController;
use App\Http\Controllers\NasTrading\SupplierController;
use App\Http\Controllers\NasTrading\TransportCompanyController;
use App\Http\Controllers\NasTrading\CustomerBillController;
use App\Http\Controllers\NasTrading\DeliveryController;
use App\Http\Controllers\NasTrading\MoneyReceiptController;
use App\Http\Controllers\NasTrading\DueListController;
use App\Http\Controllers\NasTrading\UserController;
use App\Http\Controllers\NasTrading\ImportController;
use Illuminate\Support\Facades\Route;

// Branch select
Route::get('/select-branch',  [BranchSelectController::class, 'show'])->name('select-branch');
Route::post('/select-branch', [BranchSelectController::class, 'store'])->name('select-branch.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Employees
Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/',              [EmployeeController::class, 'index'])->name('index');
    Route::post('/',             [EmployeeController::class, 'store'])->name('store');
    Route::get('/{employee}',    [EmployeeController::class, 'show'])->name('show');
    Route::put('/{employee}',    [EmployeeController::class, 'update'])->name('update');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
});

// Customers
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/search',         [CustomerController::class, 'search'])->name('search');
    Route::get('/',               [CustomerController::class, 'index'])->name('index');
    Route::post('/',              [CustomerController::class, 'store'])->name('store');
    Route::get('/{customer}',     [CustomerController::class, 'show'])->name('show');
    Route::put('/{customer}',     [CustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}',  [CustomerController::class, 'destroy'])->name('destroy');
});

// Suppliers
Route::prefix('suppliers')->name('suppliers.')->group(function () {
    Route::get('/search',         [SupplierController::class, 'search'])->name('search');
    Route::get('/',               [SupplierController::class, 'index'])->name('index');
    Route::post('/',              [SupplierController::class, 'store'])->name('store');
    Route::get('/{supplier}',     [SupplierController::class, 'show'])->name('show');
    Route::put('/{supplier}',     [SupplierController::class, 'update'])->name('update');
    Route::delete('/{supplier}',  [SupplierController::class, 'destroy'])->name('destroy');
});

// Items
Route::prefix('items')->name('items.')->group(function () {
    Route::get('/search',      [ItemController::class, 'search'])->name('search');
    Route::get('/',            [ItemController::class, 'index'])->name('index');
    Route::post('/',           [ItemController::class, 'store'])->name('store');
    Route::get('/{item}',      [ItemController::class, 'show'])->name('show');
    Route::put('/{item}',      [ItemController::class, 'update'])->name('update');
    Route::delete('/{item}',   [ItemController::class, 'destroy'])->name('destroy');
});

// Banks
Route::prefix('banks')->name('banks.')->group(function () {
    Route::get('/',          [BankController::class, 'index'])->name('index');
    Route::post('/',         [BankController::class, 'store'])->name('store');
    Route::get('/{bank}',    [BankController::class, 'show'])->name('show');
    Route::put('/{bank}',    [BankController::class, 'update'])->name('update');
    Route::delete('/{bank}', [BankController::class, 'destroy'])->name('destroy');
});

// Importers
Route::prefix('importers')->name('importers.')->group(function () {
    Route::get('/',               [ImporterController::class, 'index'])->name('index');
    Route::post('/',              [ImporterController::class, 'store'])->name('store');
    Route::get('/{importer}',     [ImporterController::class, 'show'])->name('show');
    Route::put('/{importer}',     [ImporterController::class, 'update'])->name('update');
    Route::delete('/{importer}',  [ImporterController::class, 'destroy'])->name('destroy');
});

// Expense Heads
Route::prefix('expense-heads')->name('expense-heads.')->group(function () {
    Route::get('/',                   [ExpenseHeadController::class, 'index'])->name('index');
    Route::post('/',                  [ExpenseHeadController::class, 'store'])->name('store');
    Route::get('/{expenseHead}',      [ExpenseHeadController::class, 'show'])->name('show');
    Route::put('/{expenseHead}',      [ExpenseHeadController::class, 'update'])->name('update');
    Route::delete('/{expenseHead}',   [ExpenseHeadController::class, 'destroy'])->name('destroy');
});

// PSI Companies
Route::prefix('psi-companies')->name('psi-companies.')->group(function () {
    Route::get('/',               [PsiCompanyController::class, 'index'])->name('index');
    Route::post('/',              [PsiCompanyController::class, 'store'])->name('store');
    Route::get('/{psiCompany}',   [PsiCompanyController::class, 'show'])->name('show');
    Route::put('/{psiCompany}',   [PsiCompanyController::class, 'update'])->name('update');
    Route::delete('/{psiCompany}',[PsiCompanyController::class, 'destroy'])->name('destroy');
});

// CNF Agents
Route::prefix('cnf-agents')->name('cnf-agents.')->group(function () {
    Route::get('/',              [CnfAgentController::class, 'index'])->name('index');
    Route::post('/',             [CnfAgentController::class, 'store'])->name('store');
    Route::get('/{cnfAgent}',    [CnfAgentController::class, 'show'])->name('show');
    Route::put('/{cnfAgent}',    [CnfAgentController::class, 'update'])->name('update');
    Route::delete('/{cnfAgent}', [CnfAgentController::class, 'destroy'])->name('destroy');
});

// Transport Companies
Route::prefix('transport-companies')->name('transport-companies.')->group(function () {
    Route::get('/',                       [TransportCompanyController::class, 'index'])->name('index');
    Route::post('/',                      [TransportCompanyController::class, 'store'])->name('store');
    Route::get('/{transportCompany}',     [TransportCompanyController::class, 'show'])->name('show');
    Route::put('/{transportCompany}',     [TransportCompanyController::class, 'update'])->name('update');
    Route::delete('/{transportCompany}',  [TransportCompanyController::class, 'destroy'])->name('destroy');
});

// Ports
Route::prefix('ports')->name('ports.')->group(function () {
    Route::get('/',          [PortController::class, 'index'])->name('index');
    Route::post('/',         [PortController::class, 'store'])->name('store');
    Route::get('/{port}',    [PortController::class, 'show'])->name('show');
    Route::put('/{port}',    [PortController::class, 'update'])->name('update');
    Route::delete('/{port}', [PortController::class, 'destroy'])->name('destroy');
});

// LCs
Route::prefix('lcs')->name('lcs.')->group(function () {
    Route::get('/search-customers', [LcController::class, 'searchCustomers'])->name('search-customers');
    Route::get('/search-suppliers', [LcController::class, 'searchSuppliers'])->name('search-suppliers');
    Route::get('/search',           [LcController::class, 'search'])->name('search');
    Route::get('/',                 [LcController::class, 'index'])->name('index');
    Route::get('/create',           [LcController::class, 'create'])->name('create');
    Route::post('/',                [LcController::class, 'store'])->name('store');
    Route::get('/{lc}',             [LcController::class, 'show'])->name('show');
    Route::get('/{lc}/edit',        [LcController::class, 'edit'])->name('edit');
    Route::put('/{lc}',             [LcController::class, 'update'])->name('update');
    Route::delete('/{lc}',          [LcController::class, 'destroy'])->name('destroy');
    Route::get('/{lc}/generate-bill', [LcController::class, 'generateBill'])->name('generate-bill');
});

// LC Expenses
Route::prefix('lc-expenses')->name('lc-expenses.')->group(function () {
    Route::post('/',                [LcExpenseController::class, 'store'])->name('store');
    Route::put('/{lcExpense}',      [LcExpenseController::class, 'update'])->name('update');
    Route::delete('/{lcExpense}',   [LcExpenseController::class, 'destroy'])->name('destroy');
});

// Shipments
Route::prefix('shipments')->name('shipments.')->group(function () {
    Route::get('/search-lc',        [ShipmentController::class, 'searchLc'])->name('search-lc');
    Route::get('/',                 [ShipmentController::class, 'index'])->name('index');
    Route::get('/create',           [ShipmentController::class, 'create'])->name('create');
    Route::post('/',                [ShipmentController::class, 'store'])->name('store');
    Route::get('/{shipment}',       [ShipmentController::class, 'show'])->name('show');
    Route::get('/{shipment}/edit',  [ShipmentController::class, 'edit'])->name('edit');
    Route::put('/{shipment}',       [ShipmentController::class, 'update'])->name('update');
    Route::delete('/{shipment}',    [ShipmentController::class, 'destroy'])->name('destroy');
});

// Customer Bills
Route::prefix('customer-bills')->name('customer-bills.')->group(function () {
    Route::get('/',                             [CustomerBillController::class, 'index'])->name('index');
    Route::get('/create',                       [CustomerBillController::class, 'create'])->name('create');
    Route::post('/',                            [CustomerBillController::class, 'store'])->name('store');
    Route::get('/{customerBill}',               [CustomerBillController::class, 'show'])->name('show');
    Route::get('/{customerBill}/edit',          [CustomerBillController::class, 'edit'])->name('edit');
    Route::put('/{customerBill}',               [CustomerBillController::class, 'update'])->name('update');
    Route::patch('/{customerBill}/confirm',     [CustomerBillController::class, 'confirm'])->name('confirm');
    Route::delete('/{customerBill}',            [CustomerBillController::class, 'destroy'])->name('destroy');
});

// Deliveries
Route::prefix('deliveries')->name('deliveries.')->group(function () {
    Route::get('/',                 [DeliveryController::class, 'index'])->name('index');
    Route::get('/create',           [DeliveryController::class, 'create'])->name('create');
    Route::post('/',                [DeliveryController::class, 'store'])->name('store');
    Route::get('/{delivery}',       [DeliveryController::class, 'show'])->name('show');
    Route::get('/{delivery}/edit',  [DeliveryController::class, 'edit'])->name('edit');
    Route::put('/{delivery}',       [DeliveryController::class, 'update'])->name('update');
    Route::patch('/{delivery}/dispatch', [DeliveryController::class, 'dispatch'])->name('dispatch');
    Route::patch('/{delivery}/deliver',  [DeliveryController::class, 'deliver'])->name('deliver');
});

// Due List
Route::prefix('due-lists')->name('due-lists.')->group(function () {
    Route::get('/customer',         [DueListController::class, 'customerDue'])->name('customer');
    Route::get('/search-customers', [DueListController::class, 'searchCustomers'])->name('search-customers');
});

// Money Receipts
Route::prefix('money-receipts')->name('money-receipts.')->group(function () {
    Route::get('/search-customers', [MoneyReceiptController::class, 'searchCustomers'])->name('search-customers');
    Route::get('/get-bills',        [MoneyReceiptController::class, 'getBills'])->name('get-bills');
    Route::get('/',                 [MoneyReceiptController::class, 'index'])->name('index');
    Route::get('/create',           [MoneyReceiptController::class, 'create'])->name('create');
    Route::post('/',                [MoneyReceiptController::class, 'store'])->name('store');
    Route::get('/{moneyReceipt}',   [MoneyReceiptController::class, 'show'])->name('show');
});

// Data Import
Route::prefix('import')->name('import.')->group(function () {
    Route::get('/chevron',  [ImportController::class, 'preview'])->name('chevron.preview');
    Route::post('/chevron', [ImportController::class, 'import'])->name('chevron');
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
