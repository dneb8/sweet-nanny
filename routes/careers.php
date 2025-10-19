<?php

use App\Http\Controllers\CareerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('careers')->name('careers.')->group(function () {
    Route::post('/', [CareerController::class, 'store'])->name('store');
    Route::match(['patch', 'put'], '/{career}', [CareerController::class, 'update'])->name('update');
    Route::delete('/{career}', [CareerController::class, 'destroy'])->name('destroy');
});
