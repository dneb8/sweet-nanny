<?php

use App\Http\Controllers\EnumController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('api/enums')->name('api.enums.')->group(function () {
    Route::get('/qualities', [EnumController::class, 'qualities'])->name('qualities');
    Route::get('/degrees', [EnumController::class, 'degrees'])->name('degrees');
    Route::get('/course-names', [EnumController::class, 'courseNames'])->name('course-names');
    Route::get('/all', [EnumController::class, 'all'])->name('all');
});
