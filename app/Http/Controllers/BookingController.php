<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Services\BookingService;
use App\Enums\Children\KinkshipEnum;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Http\Requests\Bookings\UpdateBookingRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\{Inertia, Response};
use Throwable;

class BookingController extends Controller
{
    public function index(): Response
    {
        $bookings = Booking::with(['tutor.user', 'address', 'bookingAppointments', 'children'])
            ->latest('id')
            ->paginate(12);

        return Inertia::render('Booking/Index', ['bookings' => $bookings]);
    }

    public function create(): Response
    {
        $user = User::with([
            'tutor' => fn ($q) => $q->select('id','user_id')->with([
                'children',
                'user.address',
            ]),
        ])->findOrFail(Auth::id());

        $kinkships = array_map(fn($c) => $c->value, KinkshipEnum::cases());

        return Inertia::render('Booking/Create', [
            'kinkships' => $kinkships,
            'tutor'     => $user->tutor,
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->load(['tutor.user','children','address','bookingAppointments.nanny']);

        return Inertia::render('Booking/Show', ['booking' => $booking]);
    }

    public function store(CreateBookingRequest $request, BookingService $service)
    {
        $booking = $service->create($request->validated());

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('notification', 'Servicio creado correctamente.');
    }

    public function edit(Booking $booking): Response
    {
        $booking->load([
            'tutor',
            'children',
            'bookingAppointments',
            'address',
        ]);

        return Inertia::render('Booking/Edit', [
            'booking'        => $booking,
            'initialBooking' => $booking,
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking, BookingService $service)
    {
        $service->update($booking, $request->validated());

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('notification', 'Servicio actualizado correctamente.');
    }

    public function destroy(Booking $booking, BookingService $service)
    {
        try {
            $service->delete($booking);
            return redirect()->route('bookings.index')->with('notification', 'Servicio eliminado.');
        } catch (Throwable $e) {
            report($e);
            return back()->withErrors(['general' => 'No se pudo eliminar el servicio.']);
        }
    }
}
