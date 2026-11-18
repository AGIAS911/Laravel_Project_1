<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/booking', [BookingController::class, 'index'])->middleware('auth')->name('booking.index');
Route::post('/booking', [BookingController::class, 'store'])->middleware('auth')->name('booking.store');
Route::get('/booking/{id}', [BookingController::class, 'show'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('booking.show');
Route::match(['put', 'patch'], '/booking/{id}', [BookingController::class, 'update'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('booking.update');
