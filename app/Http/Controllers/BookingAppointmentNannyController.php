<?php

namespace App\Http\Controllers;

use App\Classes\Fetcher\Fetcher;
use App\Enums\Career\NameCareerEnum;
use App\Enums\Course\NameEnum as CourseNameEnum;
use App\Enums\Nanny\QualityEnum;
use App\Http\Requests\BookingAppointment\AssignNannyRequest;
use App\Models\Booking;
use App\Models\BookingAppointment;
use App\Models\Nanny;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BookingAppointmentNannyController extends Controller
{
    /**
     * Display the nanny selection view with Top 3 available nannies
     */
    public function index(Booking $booking, BookingAppointment $appointment): Response
    {
        Gate::authorize('chooseNanny', $appointment);

        // Ensure appointment belongs to booking
        if ($appointment->booking_id !== $booking->id) {
            abort(404);
        }

        // Check if appointment already has a nanny assigned
        if ($appointment->nanny_id !== null) {
            return Inertia::render('Error', [
                'status' => 400,
                'message' => 'Esta cita ya tiene una niñera asignada.',
            ]);
        }

        // Get available nannies for this time slot
        $availableNannies = Nanny::availableBetween($appointment->start_date, $appointment->end_date)
            ->with(['user', 'qualities', 'careers', 'courses'])
            ->get();

        // Get Top 3 random nannies from available set
        $count = $availableNannies->count();
        $randomCount = min(3, $count);
        $top3 = $randomCount > 0 ? $availableNannies->random($randomCount) : collect([]);

        return Inertia::render('BookingAppointment/ChooseNanny', [
            'booking' => $booking->load(['tutor.user']),
            'appointment' => $appointment->load(['addresses', 'children']),
            'top3Nannies' => $top3->map(fn ($nanny) => $this->formatNannyData($nanny)),
            'qualities' => QualityEnum::labels(),
            'careers' => NameCareerEnum::labels(),
            'courseNames' => CourseNameEnum::labels(),
        ]);
    }

    /**
     * Get paginated list of available nannies with filters
     */
    public function availableNannies(Booking $booking, BookingAppointment $appointment): JsonResponse
    {
        Gate::authorize('chooseNanny', $appointment);

        // Ensure appointment belongs to booking
        if ($appointment->booking_id !== $booking->id) {
            abort(404);
        }

        $nanniesQuery = Nanny::availableBetween($appointment->start_date, $appointment->end_date)
            ->with(['user', 'qualities', 'careers', 'courses'])
            ->inRandomOrder();

        $searchables = ['user.name', 'user.surnames', 'bio'];

        $nannies = Fetcher::for($nanniesQuery)
            ->allowSearch($searchables)
            ->allowFilters([
                'quality' => [
                    'using' => function ($filter) {
                        $filter->query->whereHas('qualities', function ($q) use ($filter) {
                            $q->where('name', $filter->value);
                        });
                    },
                ],
                'career' => [
                    'using' => function ($filter) {
                        $filter->query->whereHas('careers', function ($q) use ($filter) {
                            $q->where('name', $filter->value);
                        });
                    },
                ],
                'course' => [
                    'using' => function ($filter) {
                        $filter->query->whereHas('courses', function ($q) use ($filter) {
                            $q->where('name', $filter->value);
                        });
                    },
                ],
            ])
            ->paginate(15);

        // Transform the data
        $nannies->getCollection()->transform(fn ($nanny) => $this->formatNannyData($nanny));

        return response()->json($nannies);
    }

    /**
     * Assign a nanny to the booking appointment
     */
    public function assign(AssignNannyRequest $request, Booking $booking, BookingAppointment $appointment, Nanny $nanny): RedirectResponse
    {
        Gate::authorize('chooseNanny', $appointment);

        // Ensure appointment belongs to booking
        if ($appointment->booking_id !== $booking->id) {
            abort(404);
        }

        // Check if appointment already has a nanny
        if ($appointment->nanny_id !== null) {
            return back()->withErrors(['general' => 'Esta cita ya tiene una niñera asignada.']);
        }

        // Revalidate availability (don't trust client)
        $isAvailable = Nanny::availableBetween($appointment->start_date, $appointment->end_date)
            ->where('id', $nanny->id)
            ->exists();

        if (! $isAvailable) {
            return back()->withErrors(['general' => 'La niñera seleccionada ya no está disponible en este horario.']);
        }

        // Assign the nanny
        $appointment->nanny_id = $nanny->id;
        $appointment->save();

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('notification', 'Niñera asignada correctamente.');
    }

    /**
     * Format nanny data for frontend consumption
     */
    private function formatNannyData(Nanny $nanny): array
    {
        return [
            'id' => $nanny->ulid,
            'name' => $nanny->user?->name.' '.$nanny->user?->surnames,
            'profile_photo_url' => $nanny->avatarUrl(),
            'qualities' => $nanny->qualities->pluck('name')->toArray(),
            'careers' => $nanny->careers->pluck('name')->toArray(),
            'courses' => $nanny->courses->pluck('name')->toArray(),
            'experience' => $nanny->start_date ? [
                'start_date' => $nanny->start_date,
            ] : null,
            'description' => $nanny->bio,
        ];
    }
}
