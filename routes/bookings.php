<?php

use App\Http\Controllers\BookingAppointmentNannyController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('bookings')->name('bookings.')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::get('/create', [BookingController::class, 'create'])->name('create');
    Route::get('/{booking}/edit', [BookingController::class, 'edit'])->name('edit');
    Route::match(['patch', 'put'], '/{booking}/actualizar', [BookingController::class, 'update'])->name('update');
    Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
    Route::post('/', [BookingController::class, 'store'])->name('store');
    Route::delete('/{booking}', [BookingController::class, 'destroy'])->name('destroy');

    // Nanny selection routes for appointments
    Route::prefix('{booking}/appointments/{appointment}')->name('appointments.')->group(function () {
        Route::get('/nannies/choose', [BookingAppointmentNannyController::class, 'index'])->name('nannies.choose');
        Route::post('/nannies/{nanny}', [BookingAppointmentNannyController::class, 'assign'])->name('nannies.assign');
    });
});

// API routes for nanny selection
Route::middleware(['auth', 'verified'])->prefix('api/bookings')->name('api.bookings.')->group(function () {
    Route::get('/{booking}/appointments/{appointment}/nannies', [BookingAppointmentNannyController::class, 'availableNannies'])
        ->name('appointments.nannies.available');
});
