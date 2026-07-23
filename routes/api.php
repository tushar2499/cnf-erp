<?php

use App\Http\Controllers\Api\Chevron\CustomerImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'ok']);
});
Route::prefix('chevron')->group(function () {
    Route::post('/customers/import/preview', [CustomerImportController::class, 'preview']);
    Route::post('/customers/import', [CustomerImportController::class, 'import']);
});
