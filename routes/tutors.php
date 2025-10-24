<?php

use App\Http\Controllers\TutorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('tutors')->name('tutors.')->group(function () {
    Route::get('/', [TutorController::class, 'index'])->name('index');
    Route::get('/create', [TutorController::class, 'create'])->name('create');
    Route::get('/{tutor}/edit', [TutorController::class, 'edit'])->name('edit');
    Route::match(['patch', 'put'], '/{tutor}/actualizar', [TutorController::class, 'update'])->name('update');
    Route::get('/{tutor}', [TutorController::class, 'show'])->name('show');
    Route::post('/', [TutorController::class, 'store'])->name('store');
    Route::delete('/{tutor}', [TutorController::class, 'destroy'])->name('destroy');
});
