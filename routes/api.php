<?php

use App\Http\Controllers\Api\Admin\EmployeeImportController;
use App\Http\Controllers\Api\Chevron\CustomerImportController;
use App\Http\Controllers\Api\Chevron\DhkJobExpenseImportController;
use App\Http\Controllers\Api\Chevron\DhkJobImportController;
use App\Http\Controllers\Api\Chevron\JobExpenseImportController;
use App\Http\Controllers\Api\Chevron\JobImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'ok']);
});
Route::prefix('chevron')->group(function () {
    Route::post('/customers/import/preview', [CustomerImportController::class, 'preview']);
    Route::post('/customers/import', [CustomerImportController::class, 'import']);

    Route::post('/jobs/import/preview', [JobImportController::class, 'preview']);
    Route::post('/jobs/import', [JobImportController::class, 'import']);

    Route::post('/jobs/dhk/import/preview', [DhkJobImportController::class, 'preview']);
    Route::post('/jobs/dhk/import', [DhkJobImportController::class, 'import']);

    Route::post('/job-expenses/import/preview', [JobExpenseImportController::class, 'preview']);
    Route::post('/job-expenses/import', [JobExpenseImportController::class, 'import']);

    Route::post('/job-expenses/dhk/import/preview', [DhkJobExpenseImportController::class, 'preview']);
    Route::post('/job-expenses/dhk/import', [DhkJobExpenseImportController::class, 'import']);
});

Route::prefix('admin')->group(function () {
    Route::post('/employees/import/preview', [EmployeeImportController::class, 'preview']);
    Route::post('/employees/import', [EmployeeImportController::class, 'import']);
});
