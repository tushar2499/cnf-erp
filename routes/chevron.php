<?php

use App\Http\Controllers\Chevron\ReportController;
use App\Http\Controllers\Chevron\UserController;
use App\Http\Controllers\Chevron\AccountController;
use App\Http\Controllers\Chevron\BillController;
use App\Http\Controllers\Chevron\BranchSelectController;
use App\Http\Controllers\Chevron\MoneyReceiptController;
use App\Http\Controllers\Chevron\BranchController;
use App\Http\Controllers\Chevron\CnfJobController;
use App\Http\Controllers\Chevron\JobExpenseController;
use App\Http\Controllers\Chevron\ItemController;
use App\Http\Controllers\Chevron\DashboardController;
use App\Http\Controllers\Chevron\CustomerController;
use App\Http\Controllers\Chevron\DesignationController;
use App\Http\Controllers\Chevron\EmployeeController;
use App\Http\Controllers\Chevron\ExpenseCategoryController;
use App\Http\Controllers\Chevron\ExpenseHeadController;
use App\Http\Controllers\Chevron\JobTypeController;
use App\Http\Controllers\Chevron\PortController;
use App\Http\Controllers\Chevron\ServiceController;
use Illuminate\Support\Facades\Route;

// Branch selection (middleware skips these routes)
Route::get('/select-branch',  [BranchSelectController::class, 'show'])->name('select-branch');
Route::post('/select-branch', [BranchSelectController::class, 'store'])->name('select-branch.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// C&F Jobs
Route::prefix('cnf')->name('cnf.')->group(function () {
    Route::get('/jobs/search-customers',          [CnfJobController::class,    'searchCustomers'])->name('jobs.search-customers');
    Route::get('/jobs/search-items',              [CnfJobController::class,    'searchItems'])->name('jobs.search-items');
    Route::get('/job-expenses/search-jobs',       [JobExpenseController::class,'searchJobs'])->name('job-expenses.search-jobs');
    Route::get('/job-expenses/search-employees',  [JobExpenseController::class,'searchEmployees'])->name('job-expenses.search-employees');
    Route::get('/job-expenses',                   [JobExpenseController::class,'index'])->name('job-expenses.index');
    Route::get('/job-expenses/create',            [JobExpenseController::class,'create'])->name('job-expenses.create');
    Route::post('/job-expenses',                  [JobExpenseController::class,'store'])->name('job-expenses.store');
    Route::get('/job-expenses/{jobExpense}/edit', [JobExpenseController::class,'edit'])->name('job-expenses.edit');
    Route::put('/job-expenses/{jobExpense}',      [JobExpenseController::class,'update'])->name('job-expenses.update');
    Route::delete('/job-expenses/{jobExpense}',   [JobExpenseController::class,'destroy'])->name('job-expenses.destroy');
    Route::get('/money-receipts/search-parties', [MoneyReceiptController::class, 'searchParties'])->name('money-receipts.search-parties');
    Route::get('/money-receipts/party-payable',  [MoneyReceiptController::class, 'getPartyPayable'])->name('money-receipts.party-payable');
    Route::get('/money-receipts',                [MoneyReceiptController::class, 'index'])->name('money-receipts.index');
    Route::get('/money-receipts/create',         [MoneyReceiptController::class, 'create'])->name('money-receipts.create');
    Route::post('/money-receipts',               [MoneyReceiptController::class, 'store'])->name('money-receipts.store');
    Route::get('/money-receipts/{moneyReceipt}/edit',  [MoneyReceiptController::class, 'edit'])->name('money-receipts.edit');
    Route::put('/money-receipts/{moneyReceipt}',       [MoneyReceiptController::class, 'update'])->name('money-receipts.update');
    Route::delete('/money-receipts/{moneyReceipt}',    [MoneyReceiptController::class, 'destroy'])->name('money-receipts.destroy');
    Route::get('/bills/search-jobs',   [BillController::class, 'searchJobs'])->name('bills.search-jobs');
    Route::get('/bills',               [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/create',        [BillController::class, 'create'])->name('bills.create');
    Route::post('/bills',              [BillController::class, 'store'])->name('bills.store');
    Route::get('/bills/{bill}/edit',   [BillController::class, 'edit'])->name('bills.edit');
    Route::put('/bills/{bill}',        [BillController::class, 'update'])->name('bills.update');
    Route::delete('/bills/{bill}',     [BillController::class, 'destroy'])->name('bills.destroy');
    Route::get('/jobs',                  [CnfJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/create',           [CnfJobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs',                 [CnfJobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/edit',       [CnfJobController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}',            [CnfJobController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{job}',         [CnfJobController::class, 'destroy'])->name('jobs.destroy');
});

// Stakeholders
Route::prefix('stakeholders')->name('stakeholders.')->group(function () {
    Route::get('/designations',                  [DesignationController::class, 'index'])->name('designations.index');
    Route::post('/designations',                 [DesignationController::class, 'store'])->name('designations.store');
    Route::put('/designations/{designation}',    [DesignationController::class, 'update'])->name('designations.update');
    Route::delete('/designations/{designation}', [DesignationController::class, 'destroy'])->name('designations.destroy');

    Route::get('/customers/next-id',          [CustomerController::class, 'nextId'])->name('customers.next-id');
    Route::get('/customers',                  [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}',       [CustomerController::class, 'show'])->name('customers.show');
    Route::post('/customers',                 [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}',       [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}',    [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/employees/next-id',         [EmployeeController::class, 'nextId'])->name('employees.next-id');
    Route::get('/employees',                 [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees',                [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}',      [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}',   [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/services',              [ServiceController::class,  'index'])->name('services.index');
    Route::post('/services',             [ServiceController::class,  'store'])->name('services.store');
    Route::put('/services/{service}',    [ServiceController::class,  'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class,  'destroy'])->name('services.destroy');

    Route::get('/job-types',               [JobTypeController::class, 'index'])->name('job-types.index');
    Route::post('/job-types',              [JobTypeController::class, 'store'])->name('job-types.store');
    Route::put('/job-types/{jobType}',     [JobTypeController::class, 'update'])->name('job-types.update');
    Route::delete('/job-types/{jobType}',  [JobTypeController::class, 'destroy'])->name('job-types.destroy');

    Route::get('/ports',             [PortController::class, 'index'])->name('ports.index');
    Route::post('/ports',            [PortController::class, 'store'])->name('ports.store');
    Route::put('/ports/{port}',      [PortController::class, 'update'])->name('ports.update');
    Route::delete('/ports/{port}',   [PortController::class, 'destroy'])->name('ports.destroy');

    Route::get('/items',              [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/{item}',       [ItemController::class, 'show'])->name('items.show');
    Route::post('/items',             [ItemController::class, 'store'])->name('items.store');
    Route::post('/items/{item}',      [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}',    [ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('/branches',                [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches',               [BranchController::class, 'store'])->name('branches.store');
    Route::put('/branches/{branch}',       [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{branch}',    [BranchController::class, 'destroy'])->name('branches.destroy');

    Route::get('/expense-heads',                           [ExpenseHeadController::class, 'index'])->name('expense-heads.index');
    Route::post('/expense-heads',                          [ExpenseHeadController::class, 'store'])->name('expense-heads.store');
    Route::put('/expense-heads/{expenseHead}',             [ExpenseHeadController::class, 'update'])->name('expense-heads.update');
    Route::delete('/expense-heads/{expenseHead}',          [ExpenseHeadController::class, 'destroy'])->name('expense-heads.destroy');

    Route::get('/accounts',               [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts',              [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{account}',     [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{account}',  [AccountController::class, 'destroy'])->name('accounts.destroy');

    Route::get('/expense-categories',                          [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
    Route::post('/expense-categories',                         [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
    Route::put('/expense-categories/{expenseCategory}',        [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
    Route::delete('/expense-categories/{expenseCategory}',     [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
});

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/job-expense-summary',       [ReportController::class, 'jobExpenseSummary'])->name('job-expense-summary');
    Route::get('/job-expense-summary/print', [ReportController::class, 'jobExpenseSummaryPrint'])->name('job-expense-summary.print');
});

// Users
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/',          [UserController::class, 'index'])->name('index');
    Route::post('/',         [UserController::class, 'store'])->name('store');
    Route::get('/{user}',    [UserController::class, 'show'])->name('show');
    Route::put('/{user}',    [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});
