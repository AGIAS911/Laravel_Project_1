<?php

use App\Http\Controllers\ScanSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('scanner')->group(function () {
    Route::post('/pair', [ScanSessionController::class, 'pair'])->middleware('throttle:10,1');
    Route::get('/device', [ScanSessionController::class, 'device'])->middleware('throttle:1200,1');
    Route::put('/inventory', [ScanSessionController::class, 'sync'])->middleware('throttle:120,1');
});
