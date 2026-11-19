<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\WorkspaceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BookingWorkflowController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PricingRuleController;
use App\Http\Controllers\DriverBookingController;
use App\Http\Controllers\DriverProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleTypeController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverController;


use App\Http\Controllers\ScanSessionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'active', 'verified'])->name('dashboard');

Route::get('/home', [HomeController::class, 'index'])->middleware('active')->name('home');

Route::resource('vehicles', VehicleController::class)->middleware(['auth', 'active']);
Route::resource('vehicle-types', VehicleTypeController::class)->except(['index', 'show'])
    ->middleware(['auth', 'active', 'role:admin']);
Route::resource('vehicle-types', VehicleTypeController::class)->only(['index', 'show']);
Route::get('/drivers/{driverProfile}', [DriverProfileController::class, 'show'])->name('drivers.show');
Route::get('/drivers/{driverProfile}/license', [DriverController::class, 'license'])
    ->middleware(['auth', 'active'])->name('drivers.license');
Route::middleware(['auth', 'active'])->group(function () {
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
Route::resource('booking', BookingController::class)->middleware(['auth', 'active']);

Route::middleware(['auth', 'active', 'role:customer'])->scopeBindings()->group(function () {
    Route::get('/bookings/available-vehicles', [BookingWorkflowController::class, 'vehicles'])->name('bookings.vehicles');
    Route::post('/bookings/{booking}/submit', [BookingWorkflowController::class, 'submit'])->name('bookings.submit');
    Route::post('/bookings/{booking}/quote', [BookingWorkflowController::class, 'quote'])->name('bookings.quote');
    Route::patch('/bookings/{booking}/cancel', [BookingWorkflowController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/bookings/{booking}/inventory', [InventoryController::class, 'index'])->name('bookings.inventory.index');
    Route::get('/bookings/{booking}/inventory/{inventoryItem}/image', [InventoryController::class, 'image'])->name('bookings.inventory.image');
    Route::post('/bookings/start-inventory', [InventoryController::class, 'startBooking'])->name('bookings.inventory.start');
    Route::post('/bookings/{booking}/inventory', [InventoryController::class, 'store'])->name('bookings.inventory.store');
    Route::put('/bookings/{booking}/inventory/{inventoryItem}', [InventoryController::class, 'update'])->name('bookings.inventory.update');
    Route::delete('/bookings/{booking}/inventory/{inventoryItem}', [InventoryController::class, 'destroy'])->name('bookings.inventory.destroy');
});

Route::resource('booking-services', \App\Http\Controllers\BookingServiceController::class)
    ->middleware(['auth', 'active']);

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');
});

Auth::routes(['verify' => true]);
Route::view('/driver/application-pending', 'driver.application-pending')->name('driver.application.pending');
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\VerificationController::class, 'resend'])
        ->name('verification.send');
    Route::put('/password', [ProfileController::class, 'updatePassword']);
    Route::put('/password/reset', [ProfileController::class, 'updatePassword']);
});
Route::resource('services', ServiceController::class)->except(['index', 'show'])
    ->middleware(['auth', 'active', 'role:admin']);
Route::resource('services', ServiceController::class)->only(['index', 'show']);

//--------------------------------------------------------
Route::middleware(['auth', 'active', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/bookings', [DriverBookingController::class, 'index'])->name('bookings.index');
    Route::get('/schedule', [DriverBookingController::class, 'schedule'])->name('schedule');
    Route::patch('/bookings/{booking}/accept', [BookingWorkflowController::class, 'accept'])->name('bookings.accept');
    Route::patch('/bookings/{booking}/reject', [BookingWorkflowController::class, 'reject'])->name('bookings.reject');
    Route::patch('/bookings/{booking}/start', [BookingWorkflowController::class, 'start'])->name('bookings.start');
    Route::patch('/bookings/{booking}/complete', [BookingWorkflowController::class, 'complete'])->name('bookings.complete');
    Route::get('/dashboard', [DriverController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [DriverController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [DriverController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [DriverController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [DriverController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [WorkspaceController::class, 'index'])->name('dashboard');
        Route::get('/driver-approvals', [UserController::class, 'index'])->name('driver-approvals');
        Route::patch('/vehicle-types/{vehicleType}/status', [VehicleTypeController::class, 'setStatus'])->name('vehicle-types.status');
        Route::patch('/services/{service}/status', [ServiceController::class, 'setStatus'])->name('services.status');
        Route::get('/reports/export', [WorkspaceController::class, 'exportReports'])->name('reports.export');
        Route::get('/reports', [WorkspaceController::class, 'reports'])->name('reports');
        Route::get('/settings', [WorkspaceController::class, 'settings'])->name('settings');
        Route::put('/settings', [WorkspaceController::class, 'updateSettings'])->name('settings.update');
        Route::get('/inbox', [WorkspaceController::class, 'inbox'])->name('inbox');
        Route::patch('/users/{user}/driver-status', [UserController::class, 'reviewDriver'])->name('users.driver-status');
        Route::apiResource('pricing-rules', PricingRuleController::class)->except('show');
        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

//----------------------------------------------
Route::middleware(['auth', 'active', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
});

//------------------------------------------------------
// Payments: customer uploads CliQ proof, admin reviews it.
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/payments/{payment}/receipt', [\App\Http\Controllers\PaymentController::class, 'receipt'])
        ->name('payments.receipt');
});

Route::middleware(['auth', 'active', 'role:customer'])->prefix('payments')->name('payments.')->group(function () {
    Route::get('/', [\App\Http\Controllers\PaymentController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\PaymentController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\PaymentController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin/payments')->name('admin.payments.')->group(function () {
    $review = \App\Http\Controllers\Admin\PaymentReviewController::class;
    Route::get('/', [$review, 'index'])->name('index');
    Route::get('/{payment}', [$review, 'show'])->name('show');
    Route::post('/{payment}/approve', [$review, 'approve'])->name('approve');
    Route::post('/{payment}/reject', [$review, 'reject'])->name('reject');
});

Route::middleware(['auth', 'active', 'role:customer'])->scopeBindings()->group(function () {
    Route::get('/bookings/{booking}/scan', [ScanSessionController::class, 'workspace'])->name('scan-sessions.workspace');
    Route::get('/bookings/{booking}/scan/inventory', [ScanSessionController::class, 'inventory'])->name('scan-sessions.inventory');
    Route::post('/bookings/{booking}/scan-sessions', [ScanSessionController::class, 'store'])
        ->name('scan-sessions.store');
    Route::get('/bookings/{booking}/scan-sessions/{scanSession}', [ScanSessionController::class, 'show'])
        ->name('scan-sessions.show');
    Route::delete('/bookings/{booking}/scan-sessions/{scanSession}', [ScanSessionController::class, 'close'])
        ->name('scan-sessions.close');
});

Route::view('/how-it-works', 'info.how-it-works')->name('info.how-it-works');
Route::view('/faq', 'info.faq')->name('info.faq');
Route::view('/contact', 'info.contact')->name('info.contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1,contact:')->name('info.contact.store');
Route::view('/policies', 'info.policies')->name('info.policies');
