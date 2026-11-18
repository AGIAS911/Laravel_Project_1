<?php

use App\Models\Booking;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleTypeController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::resource('vehicles', VehicleController::class);
Route::resource('vehicle-types', VehicleTypeController::class);
Route::resource('booking', BookingController::class);
Route::resource('booking-services', \App\Http\Controllers\BookingServiceController::class)
    ->middleware('auth');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'active', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/profile', [DriverController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [DriverController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [DriverController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [DriverController::class, 'destroy'])->name('profile.destroy');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');