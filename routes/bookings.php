<?php

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
});
