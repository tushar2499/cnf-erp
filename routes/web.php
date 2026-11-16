<?php

use App\Http\Controllers\Admin\Accounting\AccountController as AccountingAccountController;
use App\Http\Controllers\Admin\Accounting\MappingController as AccountingMappingController;
use App\Http\Controllers\Admin\Accounting\PostingController as AccountingPostingController;
use App\Http\Controllers\Admin\Accounting\ReportController as AccountingReportController;
use App\Http\Controllers\Admin\Accounting\VoucherController as AccountingVoucherController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DesignationController as AdminDesignationController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
    ? redirect()->route('company.select')
    : redirect()->route('login');
});

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('event:clear');
    Artisan::call('clear-compiled');

    return 'All cache cleared';
});

// Company picker
Route::middleware('auth')->group(function () {
    Route::get('/company/select', [CompanyController::class, 'select'])->name('company.select');
    Route::post('/company/switch/{slug}', [CompanyController::class, 'switch'])->name('company.switch');
    // Keep-alive: called every 10 min from long-form pages to prevent session expiry
    Route::get('/keepalive', fn () => response()->json(['ok' => true]))->name('keepalive');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Companies
    Route::get('/companies', [App\Http\Controllers\Admin\CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{company}/edit', [App\Http\Controllers\Admin\CompanyController::class, 'edit'])->name('companies.edit');
    Route::post('/companies/{company}', [App\Http\Controllers\Admin\CompanyController::class, 'update'])->name('companies.update');

    // Designations
    Route::get('/designations', [AdminDesignationController::class, 'index'])->name('designations.index');
    Route::post('/designations', [AdminDesignationController::class, 'store'])->name('designations.store');
    Route::put('/designations/{designation}', [AdminDesignationController::class, 'update'])->name('designations.update');
    Route::delete('/designations/{designation}', [AdminDesignationController::class, 'destroy'])->name('designations.destroy');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Employees (all companies)
    Route::prefix('employees')->name('employees.')->group(function () {
        // Unified employee access management
        Route::prefix('unified')->name('unified.')->group(function () {
            Route::get('/', [AdminEmployeeController::class, 'indexUnified'])->name('index');
            Route::put('/{employee}', [AdminEmployeeController::class, 'updateUnified'])->name('update');
            Route::get('/{employee}/branch-access', [AdminEmployeeController::class, 'branchAccess'])->name('branch-access');
            Route::post('/{employee}/branch-access', [AdminEmployeeController::class, 'saveBranchAccess'])->name('branch-access.save');
            Route::delete('/{employee}', [AdminEmployeeController::class, 'destroyUnified'])->name('destroy');
        });

        // Chevron Lines
        Route::get('/{employee}/view', [AdminEmployeeController::class, 'showEmployee'])->name('show');
        Route::get('/search', [AdminEmployeeController::class, 'search'])->name('search');
        Route::get('/next-id', [AdminEmployeeController::class, 'nextId'])->name('next-id');
        Route::get('/sample', [AdminEmployeeController::class, 'sampleDownload'])->name('sample');
        Route::post('/import/preview', [AdminEmployeeController::class, 'importPreview'])->name('import.preview');
        Route::post('/import', [AdminEmployeeController::class, 'import'])->name('import');
        Route::get('/', [AdminEmployeeController::class, 'index'])->name('index');
        Route::post('/', [AdminEmployeeController::class, 'store'])->name('store');
        Route::put('/{employee}', [AdminEmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [AdminEmployeeController::class, 'destroy'])->name('destroy');

        // NAS Freights
        Route::prefix('nas-freights')->name('nas-freights.')->group(function () {
            Route::get('/', [AdminEmployeeController::class, 'indexNasFreights'])->name('index');
            Route::post('/', [AdminEmployeeController::class, 'storeNasFreights'])->name('store');
            Route::get('/{employee}', [AdminEmployeeController::class, 'showNasFreights'])->name('show');
            Route::put('/{employee}', [AdminEmployeeController::class, 'updateNasFreights'])->name('update');
            Route::delete('/{employee}', [AdminEmployeeController::class, 'destroyNasFreights'])->name('destroy');
        });

        // NAS Trading
        Route::prefix('nas-trading')->name('nas-trading.')->group(function () {
            Route::get('/', [AdminEmployeeController::class, 'indexNasTrading'])->name('index');
            Route::post('/', [AdminEmployeeController::class, 'storeNasTrading'])->name('store');
            Route::get('/{employee}', [AdminEmployeeController::class, 'showNasTrading'])->name('show');
            Route::put('/{employee}', [AdminEmployeeController::class, 'updateNasTrading'])->name('update');
            Route::delete('/{employee}', [AdminEmployeeController::class, 'destroyNasTrading'])->name('destroy');
        });
    });

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Accounting (shared chart of accounts for all companies)
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/accounts', [AccountingAccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/parents', [AccountingAccountController::class, 'parents'])->name('accounts.parents');
        Route::get('/accounts/next-code', [AccountingAccountController::class, 'nextCode'])->name('accounts.next-code');
        Route::post('/accounts/import/preview', [AccountingAccountController::class, 'importPreview'])->name('accounts.import.preview');
        Route::post('/accounts/import', [AccountingAccountController::class, 'import'])->name('accounts.import');
        Route::post('/accounts', [AccountingAccountController::class, 'store'])->name('accounts.store');
        Route::put('/accounts/{account}', [AccountingAccountController::class, 'update'])->name('accounts.update');
        Route::delete('/accounts/{account}', [AccountingAccountController::class, 'destroy'])->name('accounts.destroy');
        Route::get('/ledgers', [AccountingAccountController::class, 'ledgers'])->name('accounts.ledgers');

        Route::get('/mappings', [AccountingMappingController::class, 'index'])->name('mappings.index');
        Route::post('/mappings', [AccountingMappingController::class, 'store'])->name('mappings.store');
        Route::put('/mappings/{mapping}', [AccountingMappingController::class, 'update'])->name('mappings.update');
        Route::delete('/mappings/{mapping}', [AccountingMappingController::class, 'destroy'])->name('mappings.destroy');

        Route::get('/posting', [AccountingPostingController::class, 'index'])->name('posting.index');
        Route::get('/posting/entries', [AccountingPostingController::class, 'entries'])->name('posting.entries');
        Route::post('/posting/post', [AccountingPostingController::class, 'post'])->name('posting.post');
        Route::post('/posting/post-many', [AccountingPostingController::class, 'postMany'])->name('posting.post-many');

        Route::get('/vouchers', [AccountingVoucherController::class, 'index'])->name('vouchers.index');
        Route::get('/vouchers/create', [AccountingVoucherController::class, 'create'])->name('vouchers.create');
        Route::get('/vouchers/parties', [AccountingVoucherController::class, 'parties'])->name('vouchers.parties');
        Route::post('/vouchers', [AccountingVoucherController::class, 'store'])->name('vouchers.store');
        Route::get('/vouchers/{voucher}', [AccountingVoucherController::class, 'show'])->name('vouchers.show');
        Route::get('/vouchers/{voucher}/edit', [AccountingVoucherController::class, 'edit'])->name('vouchers.edit');
        Route::put('/vouchers/{voucher}', [AccountingVoucherController::class, 'update'])->name('vouchers.update');
        Route::post('/vouchers/{voucher}/cancel', [AccountingVoucherController::class, 'cancel'])->name('vouchers.cancel');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/ledger', [AccountingReportController::class, 'ledger'])->name('ledger');
            Route::get('/trial-balance', [AccountingReportController::class, 'trialBalance'])->name('trial-balance');
            Route::get('/profit-loss', [AccountingReportController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/balance-sheet', [AccountingReportController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/day-book', [AccountingReportController::class, 'dayBook'])->name('day-book');
            Route::get('/cash-book', [AccountingReportController::class, 'cashBook'])->name('cash-book');
        });
    });
});

// Per-company panels
Route::prefix('chevron')->name('chevron.')->middleware(['auth', 'company:chevron-lines', 'branch.selected'])
    ->group(base_path('routes/chevron.php'));

Route::prefix('nas-freights')->name('nas-freights.')->middleware(['auth', 'company:nas-freights', 'branch.selected:nas-freights'])
    ->group(base_path('routes/nas-freights.php'));

Route::prefix('nas-trading')->name('nas-trading.')->middleware(['auth', 'company:nas-trading', 'branch.selected:nas-trading'])
    ->group(base_path('routes/nas-trading.php'));
