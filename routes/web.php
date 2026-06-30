<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
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
});

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/companies', [\App\Http\Controllers\Admin\CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{company}/edit', [\App\Http\Controllers\Admin\CompanyController::class, 'edit'])->name('companies.edit');
    Route::post('/companies/{company}', [\App\Http\Controllers\Admin\CompanyController::class, 'update'])->name('companies.update');
});

// Per-company panels
Route::prefix('chevron')->name('chevron.')->middleware(['auth', 'company:chevron-lines', 'branch.selected'])
    ->group(base_path('routes/chevron.php'));

Route::prefix('nas-freights')->name('nas-freights.')->middleware(['auth', 'company:nas-freights', 'branch.selected:nas-freights'])
    ->group(base_path('routes/nas-freights.php'));

Route::prefix('nas-trading')->name('nas-trading.')->middleware(['auth', 'company:nas-trading', 'branch.selected:nas-trading'])
    ->group(base_path('routes/nas-trading.php'));
