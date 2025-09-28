<?php

namespace App\Http\Controllers;

use App\Enums\Children\KinkshipEnum;
use App\Models\User;
use Inertia\{Inertia, Response};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Redirige a la página para crear un Booking
    */
    public function create(): Response
    {
        // Gate::authorize('create', User::class);

        $user = User::with([
            'tutor' => fn ($q) => $q->select('id','user_id'), 
            'address',
            'tutor.children',
        ])->find(Auth::id());

        $kinkships = KinkshipEnum::cases();
        return Inertia::render('Booking/Create', [
            'kinkships' => $kinkships,
            'tutor' => $user->tutor
        ]);

    }
}
