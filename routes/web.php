<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DriverBookingController;
use App\Http\Controllers\DriverProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleTypeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::resource('vehicles', VehicleController::class);
Route::resource('vehicle-types', VehicleTypeController::class);
Route::resource('booking', BookingController::class);
Route::get('/drivers/{driverProfile}', [DriverProfileController::class, 'show'])->name('drivers.show');
Route::middleware('auth')->group(function () {
    Route::get('/driver/bookings', [DriverBookingController::class, 'index'])
        ->name('driver.bookings.index');
    Route::patch('/driver/bookings/{booking}/complete', [DriverBookingController::class, 'complete'])
        ->name('driver.bookings.complete');
    Route::get('/booking/{booking}/review', [ReviewController::class, 'show'])
        ->name('booking.reviews.show');
    Route::post('/booking/{booking}/review', [ReviewController::class, 'store'])
        ->name('booking.reviews.store');
    Route::put('/booking/{booking}/review', [ReviewController::class, 'update'])
        ->name('booking.reviews.update');
    Route::delete('/booking/{booking}/review', [ReviewController::class, 'destroy'])
        ->name('booking.reviews.destroy');
    Route::get('/admin/driver-reviews', [ReviewController::class, 'adminDrivers'])
        ->name('admin.driver-reviews.index');
    Route::get('/admin/drivers/{driverProfile}/reviews', [ReviewController::class, 'adminIndex'])
        ->name('admin.drivers.reviews.index');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
