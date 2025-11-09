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
use App\Services\NannySearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BookingAppointmentNannyController extends Controller
{
    protected NannySearchService $nannySearchService;

    public function __construct(NannySearchService $nannySearchService)
    {
        $this->nannySearchService = $nannySearchService;
    }

    /**
     * Display the nanny selection view with Top 3 available nannies
     */
    public function index(Booking $booking, BookingAppointment $appointment): Response
    {
        Gate::authorize('chooseNanny', $appointment);

        if ($appointment->booking_id !== $booking->id) {
            abort(404);
        }

        if ($appointment->nanny_id !== null) {
            return Inertia::render('Error', [
                'status' => 400,
                'message' => 'Esta cita ya tiene una niñera asignada.',
            ]);
        }

        // Disponibilidad base local
        $availableNannies = Nanny::availableBetween($appointment->start_date, $appointment->end_date)
            ->with(['user', 'qualities', 'careers', 'courses'])
            ->get();

        // Preparar filtros para API
        $filters = [
            'qualities'    => $booking->qualities ?? [],
            'courses'      => $booking->courses ?? [],
            'career'       => $booking->careers ?? [],
            'zone'         => $appointment->addresses->first()->zone ?? null,
            'availability' => true,
        ];

        //  Llamada a Flask con manejo de errores
        try {
            $flaskResponse = $this->nannySearchService->sendFiltersToFlask($filters);
            $apiNannyIds = collect($flaskResponse['flask_response']['nanny_ids'] ?? []);
        } catch (\Exception $e) {
            $apiNannyIds = collect([]);
        }

        // Cruzar con IDs de disponibilidad local
        $finalIds = $availableNannies->pluck('id')->intersect($apiNannyIds);

        // Tomar Top 3 según la API
        $top3 = $availableNannies->whereIn('id', $finalIds)->take(3);

        // Fallback local si API falla o no hay suficientes
        if ($top3->count() < 3 && $availableNannies->count() > 0) {
            $remaining = 3 - $top3->count();
            $top3 = $top3->concat(
                $availableNannies->whereNotIn('id', $top3->pluck('id'))->random(
                    min($remaining, $availableNannies->count() - $top3->count())
                )
            );
        }

        return Inertia::render('BookingAppointment/ChooseNanny', [
            'booking'      => $booking->load(['tutor.user']),
            'appointment'  => $appointment->load(['addresses', 'children']),
            'top3Nannies'  => $top3->map(fn($nanny) => $this->formatNannyData($nanny)),
            'qualities'    => QualityEnum::labels(),
            'careers'      => NameCareerEnum::labels(),
            'courseNames'  => CourseNameEnum::labels(),
        ]);
    }

    /**
     * Paginated list of available nannies
     */
    public function availableNannies(Booking $booking, BookingAppointment $appointment): JsonResponse
    {
        Gate::authorize('chooseNanny', $appointment);

        if ($appointment->booking_id !== $booking->id) abort(404);

        $nanniesQuery = Nanny::availableBetween($appointment->start_date, $appointment->end_date)
            ->with(['user', 'qualities', 'careers', 'courses'])
            ->inRandomOrder();

        $searchables = ['user.name', 'user.surnames', 'bio'];

        $nannies = Fetcher::for($nanniesQuery)
            ->allowSearch($searchables)
            ->allowFilters([
                'quality' => ['using' => fn($filter) => $filter->query->whereHas('qualities', fn($q) => $q->where('name', $filter->value))],
                'career'  => ['using' => fn($filter) => $filter->query->whereHas('careers', fn($q) => $q->where('name', $filter->value))],
                'course'  => ['using' => fn($filter) => $filter->query->whereHas('courses', fn($q) => $q->where('name', $filter->value))],
            ])
            ->paginate(15);

        $nannies->getCollection()->transform(fn($nanny) => $this->formatNannyData($nanny));

        return response()->json($nannies);
    }

    /**
     * Assign a nanny to the booking appointment
     */
    public function assign(Booking $booking, BookingAppointment $appointment, Nanny $nanny): RedirectResponse
    {
        Gate::authorize('chooseNanny', $appointment);

        if ($appointment->booking_id !== $booking->id) abort(404);

        if ($appointment->nanny_id !== null) {
            return back()->withErrors(['general' => 'Esta cita ya tiene una niñera asignada.']);
        }

        $isAvailable = Nanny::availableBetween($appointment->start_date, $appointment->end_date)
            ->where('id', $nanny->id)
            ->exists();

        if (! $isAvailable) {
            return back()->withErrors(['general' => 'La niñera seleccionada ya no está disponible en este horario.']);
        }

        $appointment->nanny_id = $nanny->id;
        $appointment->save();

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('notification', 'Niñera asignada correctamente.');
    }

    /**
     * Format nanny data for frontend
     */
    private function formatNannyData(Nanny $nanny): array
    {
        return [
            'id' => $nanny->ulid,
            'name' => $nanny->user?->name . ' ' . $nanny->user?->surnames,
            'profile_photo_url' => $nanny->avatarUrl(),
            'qualities' => $nanny->qualities->pluck('name')->toArray(),
            'careers' => $nanny->careers->pluck('name')->toArray(),
            'courses' => $nanny->courses->pluck('name')->toArray(),
            'experience' => $nanny->start_date ? ['start_date' => $nanny->start_date] : null,
            'description' => $nanny->bio,
        ];
    }
}
