<?php

use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('addresses')->name('addresses.')->group(function () {

    Route::post('/', [AddressController::class, 'store'])->name('store');
    Route::match(['patch', 'put'], '/{address}', [AddressController::class, 'update'])->name('update');
    Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');

});
