<?php

namespace App\Http\Controllers;

use App\Classes\Fetcher\Fetcher;
use App\Enums\Booking\StatusEnum;
use App\Enums\Career\NameCareerEnum;
use App\Enums\Course\NameEnum as CourseNameEnum;
use App\Enums\Nanny\QualityEnum;
use App\Models\Booking;
use App\Models\BookingAppointment;
use App\Models\Nanny;
use App\Notifications\NannyAssigned;
use App\Notifications\NannyChanged;
use App\Notifications\NannyUnassigned;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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

        // Asegura que la cita pertenezca al booking.
        if ($appointment->booking_id !== $booking->id) {
            abort(404);
        }

        // Si la cita ya tiene niñera asignada, no se puede elegir de nuevo.
        if ($appointment->nanny_id !== null) {
            return Inertia::render('Error', [
                'status' => 400,
                'message' => 'Esta cita ya tiene una niñera asignada.',
            ]);
        }

        // 1) Disponibilidad base:
        //    - Se toma de nuestros servicios internos (availableBetween)
        //    - Esta es la lista local de niñeras disponibles en ese rango.
        $availableNannies = Nanny::availableBetween($appointment->start_date, $appointment->end_date)
            ->with(['user', 'qualities', 'careers', 'courses'])
            ->get();

        // 2) Llamada a la API (asíncrona vía Job o similar):
        //    - Enviar al Job los campos necesarios:
        //        * booking: qualities, courses, careers
        //        * appointment: address->zone  (NOTA: zone ya no está en booking)
        //    - El Job debe manejar validación/errores de la API y, si falla,
        //      aplicar un "fallback" de emergencia para no romper el flujo.
        //
        //    Resultado esperado del Job/API:
        //      $apiResponse = [
        //        'nanny_ids' => [/* IDs válidos y disponibles según API */]
        //      ];

        // 3) Cruce de resultados:
        //    - Conservar SOLO los IDs devueltos por la API que existan en $availableNannies.
        //    - Esto descarta cualquier ID que no esté realmente disponible en nuestra lista local.
        //
        //    Ejemplo de cruce (cuando integres la API real):
        //    $apiIds = collect($apiResponse['nanny_ids'] ?? []);
        //    $finalIds = $availableNannies->pluck('id')->intersect($apiIds);
        //

        // 4) Top 3:
        //    - Tomar el Top 3 a partir de los IDs que vengan en el apiResponse (ya cruzados).
        //    - Ese Top 3 reemplaza al random actual.
        //
        //    Implementación futura (cuando se tengan los $finalIds):
        //    $top3 = $availableNannies->whereIn('id', $finalIds)->take(3);
        //
        //    Mientras tanto (provisional): random de los disponibles locales.
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
    public function assign(Booking $booking, BookingAppointment $appointment, Nanny $nanny): RedirectResponse
    {
        Gate::authorize('chooseNanny', $appointment);

        // Ensure appointment belongs to booking
        if ($appointment->booking_id !== $booking->id) {
            abort(404);
        }

        // Store old nanny if changing
        $oldNannyId = $appointment->nanny_id;
        $oldNanny = $oldNannyId ? Nanny::find($oldNannyId) : null;
        $isChanging = $oldNannyId !== null;

        // Revalidate availability (don't trust client)
        $isAvailable = Nanny::availableBetween($appointment->start_date, $appointment->end_date)
            ->where('id', $nanny->id)
            ->exists();

        if (! $isAvailable) {
            return back()->withErrors(['general' => 'La niñera seleccionada ya no está disponible en este horario.']);
        }

        // Assign the nanny
        $appointment->nanny_id = $nanny->id;
        $appointment->status = StatusEnum::PENDING->value;
        $appointment->save();

        // Load relationships
        $nanny->loadMissing('user');
        $appointment->loadMissing('booking.tutor.user');

        // Send appropriate notifications
        if ($isChanging && $oldNanny) {
            // Changing nanny - multiple notifications needed
            $oldNanny->loadMissing('user');
            $tutorUser = $appointment->booking?->tutor?->user;
            $currentUser = Auth::user();
            
            // ALWAYS notify the old nanny that their appointment was cancelled
            // This is critical - the old nanny must know they're no longer assigned
            if ($oldNanny->user) {
                $oldNanny->user->notify(new NannyUnassigned($appointment));
            } else {
                // Log if old nanny has no user (shouldn't happen but helps debugging)
                Log::warning('Old nanny has no user', [
                    'nanny_id' => $oldNanny->id,
                    'appointment_id' => $appointment->id
                ]);
            }
            
            // If admin made the change, notify the tutor about the nanny change
            if ($currentUser && $currentUser->hasRole('admin') && $tutorUser && $tutorUser->id !== $currentUser->id) {
                $tutorUser->notify(new NannyChanged($appointment, $oldNanny, $nanny));
            }
            
            // ALWAYS notify the new nanny about their new assignment
            if ($nanny->user) {
                $nanny->user->notify(new NannyAssigned($appointment));
            } else {
                // Log if new nanny has no user (shouldn't happen but helps debugging)
                Log::warning('New nanny has no user', [
                    'nanny_id' => $nanny->id,
                    'appointment_id' => $appointment->id
                ]);
            }
            
            $message = 'Niñera cambiada correctamente.';
        } else {
            // First assignment - notify nanny
            if ($nanny->user) {
                $nanny->user->notify(new NannyAssigned($appointment));
            } else {
                // Log if nanny has no user
                Log::warning('Nanny has no user on first assignment', [
                    'nanny_id' => $nanny->id,
                    'appointment_id' => $appointment->id
                ]);
            }
            $message = 'Niñera asignada correctamente.';
        }

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('notification', $message);
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
