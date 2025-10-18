<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Services\BookingService;
use App\Enums\Children\KinkshipEnum;
use App\Enums\Nanny\QualityEnum;
use App\Enums\Career\DegreeEnum;
use App\Enums\Course\NameEnum as CourseNameEnum;
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
                'addresses', // Include tutor's addresses
            ]),
        ])->findOrFail(Auth::id());

        $kinkships = array_map(fn($c) => $c->value, KinkshipEnum::cases());

        return Inertia::render('Booking/Create', [
            'kinkships'   => $kinkships,
            'tutor'       => $user->tutor,
            'qualities'   => QualityEnum::labels(),
            'degrees'     => DegreeEnum::labels(),
            'courseNames' => CourseNameEnum::labels(),
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking = Booking::useWritePdo()
            ->with(['tutor.user','address','bookingAppointments.nanny','childrenWithTrashed', 'children'])
            ->findOrFail($booking->id);

        return Inertia::render('Booking/Show', ['booking' => $booking]);
    }

    public function store(CreateBookingRequest $request, BookingService $service)
    {
        $request->merge(['booking' => ['tutor_id' => (int) $request->user()->tutor_id] + (array) $request->input('booking', [])]);

        $booking = $service->create($request->validated());

        return to_route('bookings.show', $booking)->with('notification', 'Servicio creado correctamente.');
    }


    public function edit(Booking $booking): Response
    {
        $booking->load([
            'tutor.addresses',
            'children',
            'bookingAppointments',
            'address',
        ]);

        $kinkships = array_map(fn($c) => $c->value, KinkshipEnum::cases());

        return Inertia::render('Booking/Edit', [
            'booking'        => $booking,
            'initialBooking' => $booking,
            'kinkships'      => $kinkships,
            'qualities'      => QualityEnum::labels(),
            'degrees'        => DegreeEnum::labels(),
            'courseNames'    => CourseNameEnum::labels(),
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
