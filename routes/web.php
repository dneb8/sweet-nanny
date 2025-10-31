<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Notification routes
use App\Http\Controllers\NotificationController;

Route::middleware('auth')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
});

// Postal code API for SEPOMEX integration
use App\Http\Controllers\PostalCodeController;

Route::get('api/postal-code/{postalCode}', [PostalCodeController::class, 'show'])
    ->name('postalcode.show');

require __DIR__.'/users.php';
require __DIR__.'/nannies.php';
require __DIR__.'/tutors.php';
require __DIR__.'/courses.php';
require __DIR__.'/careers.php';
require __DIR__.'/bookings.php';
require __DIR__.'/addresses.php';
require __DIR__.'/children.php';
require __DIR__.'/enums.php';
