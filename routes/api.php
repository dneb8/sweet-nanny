<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NannySearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Rutas de tu API que devuelven JSON o interactúan con servicios externos.
| Estas rutas están automáticamente protegidas por el middleware "api" y usan el prefijo /api.
*/

Route::post('/nannies/train', [NannySearchController::class, 'trainModel']);
Route::post('/nannies/search', [NannySearchController::class, 'search']);
