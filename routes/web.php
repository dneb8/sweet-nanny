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
require __DIR__.'/users.php';
require __DIR__.'/nannies.php';
require __DIR__.'/tutors.php';
require __DIR__.'/courses.php';
require __DIR__.'/careers.php';
require __DIR__.'/bookings.php';
require __DIR__.'/addresses.php';
require __DIR__.'/children.php';
require __DIR__.'/enums.php';
require __DIR__.'/reviews.php';
require __DIR__.'/notifications.php';


// Prueba api (entorno local)
Route::get('/test-env', function () {
    return response()->json([
        'env_var' => env('NANNY_API_KEY'),
        'config_var' => config('services.flask.nanny_api_key')
    ]);
});





