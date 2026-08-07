<?php

use App\Http\Controllers\Chevron\AccountController;
use App\Http\Controllers\Chevron\BillController;
use App\Http\Controllers\Chevron\BranchController;
use App\Http\Controllers\Chevron\BranchSelectController;
use App\Http\Controllers\Chevron\CnfJobController;
use App\Http\Controllers\Chevron\CustomerController;
use App\Http\Controllers\Chevron\DashboardController;
use App\Http\Controllers\Chevron\DesignationController;
use App\Http\Controllers\Chevron\EmployeeController;
use App\Http\Controllers\Chevron\ExpenseCategoryController;
use App\Http\Controllers\Chevron\ExpenseHeadController;
use App\Http\Controllers\Chevron\ItemController;
use App\Http\Controllers\Chevron\JobExpenseController;
use App\Http\Controllers\Chevron\JobTypeController;
use App\Http\Controllers\Chevron\MoneyReceiptController;
use App\Http\Controllers\Chevron\PortController;
use App\Http\Controllers\Chevron\ReportController;
use App\Http\Controllers\Chevron\ServiceController;
use App\Http\Controllers\Chevron\UserController;
use Illuminate\Support\Facades\Route;

// Branch selection (middleware skips these routes)
Route::get('/select-branch', [BranchSelectController::class, 'show'])->name('select-branch');
Route::post('/select-branch', [BranchSelectController::class, 'store'])->name('select-branch.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// C&F Jobs
Route::prefix('cnf')->name('cnf.')->group(function () {
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/search-customers', [CnfJobController::class, 'searchCustomers'])->name('search-customers');
        Route::get('/search-items', [CnfJobController::class, 'searchItems'])->name('search-items');
        Route::get('/', [CnfJobController::class, 'index'])->name('index');
        Route::get('/create', [CnfJobController::class, 'create'])->name('create');
        Route::post('/', [CnfJobController::class, 'store'])->name('store');
        Route::get('/{job}', [CnfJobController::class, 'show'])->name('show');
        Route::get('/{job}/edit', [CnfJobController::class, 'edit'])->name('edit');
        Route::get('/{job}/print', [CnfJobController::class, 'print'])->name('print');
        Route::put('/{job}', [CnfJobController::class, 'update'])->name('update');
        Route::delete('/{job}', [CnfJobController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('job-expenses')->name('job-expenses.')->group(function () {
        Route::get('/search-jobs', [JobExpenseController::class, 'searchJobs'])->name('search-jobs');
        Route::get('/search-employees', [JobExpenseController::class, 'searchEmployees'])->name('search-employees');
        Route::get('/', [JobExpenseController::class, 'index'])->name('index');
        Route::get('/create', [JobExpenseController::class, 'create'])->name('create');
        Route::post('/', [JobExpenseController::class, 'store'])->name('store');
        Route::get('/{jobExpense}/edit', [JobExpenseController::class, 'edit'])->name('edit');
        Route::put('/{jobExpense}', [JobExpenseController::class, 'update'])->name('update');
        Route::delete('/{jobExpense}', [JobExpenseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('money-receipts')->name('money-receipts.')->group(function () {
        Route::get('/search-parties', [MoneyReceiptController::class, 'searchParties'])->name('search-parties');
        Route::get('/party-payable', [MoneyReceiptController::class, 'getPartyPayable'])->name('party-payable');
        Route::get('/', [MoneyReceiptController::class, 'index'])->name('index');
        Route::get('/create', [MoneyReceiptController::class, 'create'])->name('create');
        Route::post('/', [MoneyReceiptController::class, 'store'])->name('store');
        Route::get('/{moneyReceipt}/edit', [MoneyReceiptController::class, 'edit'])->name('edit');
        Route::put('/{moneyReceipt}', [MoneyReceiptController::class, 'update'])->name('update');
        Route::delete('/{moneyReceipt}', [MoneyReceiptController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/search-jobs', [BillController::class, 'searchJobs'])->name('search-jobs');
        Route::get('/', [BillController::class, 'index'])->name('index');
        Route::get('/create', [BillController::class, 'create'])->name('create');
        Route::post('/', [BillController::class, 'store'])->name('store');
        Route::get('/{bill}/print', [BillController::class, 'print'])->name('print');
        Route::get('/{bill}/edit', [BillController::class, 'edit'])->name('edit');
        Route::put('/{bill}', [BillController::class, 'update'])->name('update');
        Route::delete('/{bill}', [BillController::class, 'destroy'])->name('destroy');
    });
});

// Stakeholders
Route::prefix('stakeholders')->name('stakeholders.')->group(function () {
    Route::prefix('designations')->name('designations.')->group(function () {
        Route::get('/', [DesignationController::class, 'index'])->name('index');
        Route::post('/', [DesignationController::class, 'store'])->name('store');
        Route::put('/{designation}', [DesignationController::class, 'update'])->name('update');
        Route::delete('/{designation}', [DesignationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/next-id', [CustomerController::class, 'nextId'])->name('next-id');
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/next-id', [EmployeeController::class, 'nextId'])->name('next-id');
        Route::get('/sample', [EmployeeController::class, 'sampleDownload'])->name('sample');
        Route::post('/import/preview', [EmployeeController::class, 'importPreview'])->name('import.preview');
        Route::post('/import', [EmployeeController::class, 'import'])->name('import');
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
    });
});

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('job-types')->name('job-types.')->group(function () {
        Route::get('/', [JobTypeController::class, 'index'])->name('index');
        Route::post('/', [JobTypeController::class, 'store'])->name('store');
        Route::put('/{jobType}', [JobTypeController::class, 'update'])->name('update');
        Route::delete('/{jobType}', [JobTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('ports')->name('ports.')->group(function () {
        Route::get('/', [PortController::class, 'index'])->name('index');
        Route::post('/', [PortController::class, 'store'])->name('store');
        Route::put('/{port}', [PortController::class, 'update'])->name('update');
        Route::delete('/{port}', [PortController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/next-code', [ItemController::class, 'nextCode'])->name('next-code');
        Route::post('/quick', [ItemController::class, 'quickStore'])->name('quick-store');
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('/{item}', [ItemController::class, 'show'])->name('show');
        Route::post('/{item}', [ItemController::class, 'update'])->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('expense-heads')->name('expense-heads.')->group(function () {
        Route::get('/sample', [ExpenseHeadController::class, 'sampleDownload'])->name('sample');
        Route::post('/import/preview', [ExpenseHeadController::class, 'importPreview'])->name('import.preview');
        Route::post('/import', [ExpenseHeadController::class, 'import'])->name('import');
        Route::get('/employees/search', [ExpenseHeadController::class, 'searchEmployees'])->name('employees.search');
        Route::get('/', [ExpenseHeadController::class, 'index'])->name('index');
        Route::post('/', [ExpenseHeadController::class, 'store'])->name('store');
        Route::put('/{expenseHead}', [ExpenseHeadController::class, 'update'])->name('update');
        Route::delete('/{expenseHead}', [ExpenseHeadController::class, 'destroy'])->name('destroy');
        Route::get('/{expenseHead}/employees', [ExpenseHeadController::class, 'getEmployees'])->name('employees.get');
        Route::post('/{expenseHead}/employees', [ExpenseHeadController::class, 'syncEmployees'])->name('employees.sync');
    });

    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::put('/{account}', [AccountController::class, 'update'])->name('update');
        Route::delete('/{account}', [AccountController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
        Route::get('/sample', [ExpenseCategoryController::class, 'sampleDownload'])->name('sample');
        Route::post('/import/preview', [ExpenseCategoryController::class, 'importPreview'])->name('import.preview');
        Route::post('/import', [ExpenseCategoryController::class, 'import'])->name('import');
        Route::get('/', [ExpenseCategoryController::class, 'index'])->name('index');
        Route::post('/', [ExpenseCategoryController::class, 'store'])->name('store');
        Route::put('/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('update');
        Route::delete('/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('destroy');
    });
});

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/job-expense-summary', [ReportController::class, 'jobExpenseSummary'])->name('job-expense-summary');
    Route::get('/job-expense-summary/print', [ReportController::class, 'jobExpenseSummaryPrint'])->name('job-expense-summary.print');
});

// Users
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});
