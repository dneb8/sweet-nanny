<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\BookingStatusService;
use App\Enums\Children\KinkshipEnum;
use App\Enums\Nanny\QualityEnum;
use App\Enums\Career\NameCareerEnum;
use App\Enums\Course\NameEnum as CourseNameEnum;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Http\Requests\Bookings\UpdateBookingRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\{Inertia, Response};
use Throwable;

class BookingController extends Controller
{
    public function index(BookingService $bookingService): Response
    {
        $bookings = $bookingService->indexFetch();

        return Inertia::render('Booking/Index', ['bookings' => $bookings]);
    }

    public function create(): Response
    {
        $user = User::with([
            'tutor' => fn ($q) => $q->select('id','user_id')->with([
                'children',
                'user',
                'addresses', 
            ]),
        ])->findOrFail(Auth::id());

        $kinkships = array_map(fn($c) => $c->value, KinkshipEnum::cases());

        return Inertia::render('Booking/Create', [
            'kinkships'   => $kinkships,
            'tutor'       => $user->tutor,
            'qualities'   => QualityEnum::labels(),
            'careers'     => NameCareerEnum::labels(),
            'courseNames' => CourseNameEnum::labels(),
        ]);
    }

    public function show(Booking $booking, BookingStatusService $statusService): Response
    {
        $booking = Booking::useWritePdo()
            ->with(['tutor.user','address','bookingAppointments.nanny','childrenWithTrashed', 'children'])
            ->findOrFail($booking->id);

        // Actualizar estado basado en horarios de citas
        $statusService->updateStatus($booking);

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
        $kinkships = array_map(fn($c) => $c->value, KinkshipEnum::cases());

        $booking->load(['tutor.children', 'tutor.addresses', 'children', 'bookingAppointments', 'address']);

        return Inertia::render('Booking/Edit', [
            'booking'        => $booking,
            'initialBooking' => $booking,
            'tutor'          => $booking->tutor, 
            'initialChildren'=> $booking->tutor?->children ?? [],
            'kinkships'      => $kinkships,
            'qualities'      => QualityEnum::labels(),
            'careers'        => NameCareerEnum::labels(),
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
