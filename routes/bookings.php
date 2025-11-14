<?php

use App\Http\Controllers\BookingAppointmentNannyController;
use App\Http\Controllers\BookingAppointmentController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

// Booking Appointments routes
Route::middleware(['auth', 'verified'])->prefix('booking-appointments')->name('booking-appointments.')->group(function () {
    Route::get('/', [BookingAppointmentController::class, 'index'])->name('index');
    Route::patch('/{appointment}/accept', [BookingAppointmentController::class, 'accept'])->name('accept');
    Route::patch('/{appointment}/reject', [BookingAppointmentController::class, 'reject'])->name('reject');
    Route::patch('/{appointment}/unassign-nanny', [BookingAppointmentController::class, 'unassignNanny'])->name('unassign-nanny');
    Route::patch('/{appointment}/cancel', [BookingAppointmentController::class, 'cancelDirect'])->name('cancel');
});

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
        Route::post('/cancel', [BookingAppointmentController::class, 'cancel'])->name('cancel');
        Route::patch('/dates', [BookingAppointmentController::class, 'updateDates'])->name('update-dates');
        Route::patch('/address', [BookingAppointmentController::class, 'updateAddress'])->name('update-address');
        Route::patch('/children', [BookingAppointmentController::class, 'updateChildren'])->name('update-children');
    });
});

// API routes for nanny selection
Route::middleware(['auth', 'verified'])->prefix('api/bookings')->name('api.bookings.')->group(function () {
    Route::get('/{booking}/appointments/{appointment}/nannies', [BookingAppointmentNannyController::class, 'availableNannies'])
        ->name('appointments.nannies.available');
});
