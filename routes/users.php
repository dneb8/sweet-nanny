<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::match(['patch', 'put'], '/{user}/actualizar', [UserController::class, 'update'])->name('update');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});