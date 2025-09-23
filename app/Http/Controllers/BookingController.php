<?php

namespace App\Http\Controllers;
use Inertia\{Inertia, Response};

use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Redirige a la página para crear un Booking
    */
    public function create(): Response
    {
        // Gate::authorize('create', User::class);
        return Inertia::render('Booking/Create', [
        ]);

    }
}
