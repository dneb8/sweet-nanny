<?php

use App\Http\Controllers\{
    UserController,
    NannyController,
};
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::match(['patch', 'put'], '/{user}/actualizar', [UserController::class, 'update'])->name('update');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'verified'])->prefix('nannies')->name('nannies.')->group(function () {
    Route::get('/', [NannyController::class, 'index'])->name('index');
    Route::get('/create', [NannyController::class, 'create'])->name('create');
    Route::get('/{user}/edit', [NannyController::class, 'edit'])->name('edit');
    Route::match(['patch', 'put'], '/{user}/actualizar', [NannyController::class, 'update'])->name('update');
    Route::get('/{user}', [NannyController::class, 'show'])->name('show');
    Route::post('/', [NannyController::class, 'store'])->name('store');
    Route::delete('/{user}', [NannyController::class, 'destroy'])->name('destroy');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
