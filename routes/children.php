<?php

use App\Http\Controllers\ChildController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('children')->name('children.')->group(function () {
    Route::post('/', [ChildController::class, 'store'])->name('store');
    Route::match(['patch', 'put'], '/{child}', [ChildController::class, 'update'])->name('update');
    Route::delete('/{child}', [ChildController::class, 'destroy'])->name('destroy');
});
