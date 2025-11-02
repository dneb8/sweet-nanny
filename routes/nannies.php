<?php

use App\Http\Controllers\NannyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('nannies')->name('nannies.')->group(function () {
    Route::get('/', [NannyController::class, 'index'])->name('index');
    Route::get('/create', [NannyController::class, 'create'])->name('create');
    Route::get('/{nanny}/edit', [NannyController::class, 'edit'])->name('edit');
    Route::match(['patch', 'put'], '/{nanny}/actualizar', [NannyController::class, 'update'])->name('update');
    Route::get('/{nanny}', [NannyController::class, 'show'])->name('show');
    Route::post('/', [NannyController::class, 'store'])->name('store');
    Route::delete('/{nanny}', [NannyController::class, 'destroy'])->name('destroy');
    Route::patch('/{nanny}/qualities', [NannyController::class, 'updateQualities'])->name('update.qualities');
    Route::patch('/{nanny}/profile', [NannyController::class, 'updateProfile'])->name('nannies.updateProfile');


});
