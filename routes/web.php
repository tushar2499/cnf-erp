<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
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

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Employees (all companies)
    Route::prefix('employees')->name('employees.')->group(function () {
        // Chevron Lines
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
});

// Per-company panels
Route::prefix('chevron')->name('chevron.')->middleware(['auth', 'company:chevron-lines', 'branch.selected'])
    ->group(base_path('routes/chevron.php'));

Route::prefix('nas-freights')->name('nas-freights.')->middleware(['auth', 'company:nas-freights', 'branch.selected:nas-freights'])
    ->group(base_path('routes/nas-freights.php'));

Route::prefix('nas-trading')->name('nas-trading.')->middleware(['auth', 'company:nas-trading', 'branch.selected:nas-trading'])
    ->group(base_path('routes/nas-trading.php'));
