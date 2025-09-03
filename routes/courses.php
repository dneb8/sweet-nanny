<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('courses')->name('courses.')->group(function () {
    Route::post('/', [CourseController::class, 'store'])->name('store');
    Route::match(['patch', 'put'], '/{course}', [CourseController::class, 'update'])->name('update');
    Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
});
