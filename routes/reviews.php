<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/toggle-approved', [ReviewController::class, 'toggleApproved'])->name('reviews.toggleApproved');
});
