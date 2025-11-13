<?php

namespace App\Http\Controllers;

use App\Enums\Booking\StatusEnum;
use App\Models\Booking;
use App\Models\BookingAppointment;
use App\Notifications\AppointmentAccepted;
use App\Notifications\AppointmentRejected;
use App\Notifications\NannyCancelledFromAppointment;
use App\Services\BookingAppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class BookingAppointmentController extends Controller
{
    /**
     * Display a listing of booking appointments
     */
    public function index(BookingAppointmentService $service): Response
    {
        Gate::authorize('viewAny', BookingAppointment::class);

        $bookingAppointments = $service->indexFetch();

        return Inertia::render('BookingAppointment/Index', [
            'bookingAppointments' => $bookingAppointments,
        ]);
    }

    /**
     * Nanny accepts the assignment (pending -> confirmed)
     */
    public function accept(BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('accept', $appointment);

        if ($appointment->status->value !== StatusEnum::PENDING->value) {
            return back()->with('error', 'Solo se pueden aceptar citas en estado pendiente');
        }

        if (! $appointment->nanny_id) {
            return back()->with('error', 'No hay niñera asignada para aceptar');
        }

        $appointment->update([
            'status' => StatusEnum::CONFIRMED->value,
        ]);

        // Notify tutor & admin
        $appointment->loadMissing('booking.tutor.user');
        if ($tutorUser = $appointment->booking?->tutor?->user) {
            $tutorUser->notify(new AppointmentAccepted($appointment));
        }
        if ($admin = \App\Models\User::role('admin')->first()) {
            $admin->notify(new AppointmentAccepted($appointment));
        }

        return back()->with('success', 'Cita aceptada exitosamente');
    }

    /**
     * Nanny rejects the assignment (pending -> draft, remove nanny)
     */
    public function reject(BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('reject', $appointment);

        if ($appointment->status->value !== StatusEnum::PENDING->value) {
            return back()->with('error', 'Solo se pueden rechazar citas en estado pendiente');
        }

        if (! $appointment->nanny_id) {
            return back()->with('error', 'No hay niñera asignada para rechazar');
        }

        $appointment->update([
            'nanny_id' => null,
            'status' => StatusEnum::DRAFT->value,
        ]);

        // Notify tutor & admin
        $appointment->loadMissing('booking.tutor.user');
        if ($tutorUser = $appointment->booking?->tutor?->user) {
            $tutorUser->notify(new AppointmentRejected($appointment));
        }
        if ($admin = \App\Models\User::role('admin')->first()) {
            $admin->notify(new AppointmentRejected($appointment));
        }

        return back()->with('success', 'Solicitud rechazada exitosamente');
    }

    /**
     * Unassign nanny from a confirmed appointment (-> draft)
     * Can be done by tutor (owner), nanny assigned, or admin
     */
    public function unassignNanny(BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('unassignNanny', $appointment);

        if ($appointment->status->value !== StatusEnum::CONFIRMED->value) {
            return back()->with('error', 'Solo se puede cancelar la niñera de citas confirmadas');
        }

        if (! $appointment->nanny_id) {
            return back()->with('error', 'No hay niñera asignada');
        }

        $user = Auth::user();
        $appointment->loadMissing('booking.tutor.user', 'nanny.user');

        // Who cancelled? (for notification copy)
        $cancelledBy = 'admin';
        if ($user->hasRole('tutor') && $appointment->booking?->tutor?->user_id === $user->id) {
            $cancelledBy = 'tutor';
        } elseif ($user->hasRole('nanny') && $appointment->nanny?->user_id === $user->id) {
            $cancelledBy = 'nanny';
        }

        $nannyUser = $appointment->nanny?->user;
        $tutorUser = $appointment->booking?->tutor?->user;

        $appointment->update([
            'nanny_id' => null,
            'status' => StatusEnum::DRAFT->value,
        ]);

        if ($cancelledBy === 'tutor') {
            if ($nannyUser) {
                $nannyUser->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
            if ($admin = \App\Models\User::role('admin')->first()) {
                $admin->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
        } elseif ($cancelledBy === 'nanny') {
            if ($tutorUser) {
                $tutorUser->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
            if ($admin = \App\Models\User::role('admin')->first()) {
                $admin->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
        } else {
            if ($tutorUser) {
                $tutorUser->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
            if ($nannyUser) {
                $nannyUser->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
        }

        return back()->with('success', 'Niñera cancelada exitosamente');
    }

    public function cancelDirect(BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('cancel', $appointment);

        if ($appointment->status->value !== StatusEnum::CONFIRMED->value) {
            return back()->with('error', 'Solo se pueden cancelar citas en estado confirmado');
        }

        $appointment->update([
            'status' => StatusEnum::CANCELLED->value,
        ]);

        return back()->with('success', 'Cita cancelada exitosamente');
    }

    /**
     * Cancel nested (redirect back to booking show)
     */
    public function cancel(Booking $booking, BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('cancel', $appointment);

        $appointment->update([
            'status' => StatusEnum::CANCELLED->value,
        ]);

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('success', 'Cita cancelada exitosamente');
    }

    /**
     * Update dates (DRAFT/PENDING)
     */
    public function updateDates(Request $request, Booking $booking, BookingAppointment $appointment)
    {
        Gate::authorize('updateDates', $appointment);

        $v = Validator::make($request->all(), [
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:8'],
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput()->with('error', 'Error al actualizar las fechas');
        }

        $data = $v->validated();
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $dur = $data['duration'] ?? max(1, (int) ceil($start->floatDiffInHours($end)));

        DB::transaction(function () use ($appointment, $start, $end, $dur) {
            if (! is_null($appointment->nanny_id)) {
                $appointment->forceFill([
                    'nanny_id' => null,
                    'status' => StatusEnum::DRAFT->value,
                ])->save();
            }

            $payload = ['start_date' => $start, 'end_date' => $end];
            if ($appointment->isFillable('duration')) {
                $payload['duration'] = $dur;
            }

            $appointment->update($payload);
        });

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('openAppointmentId', $appointment->id)
            ->with('success', 'Fechas actualizadas exitosamente');
    }

    /**
     * Update address (DRAFT/PENDING)
     */
    public function updateAddress(Request $request, Booking $booking, BookingAppointment $appointment)
    {
        Gate::authorize('updateAddress', $appointment);

        dd($request->all(), 'Reached updateAddress', $appointment, $booking);

        $validator = Validator::make($request->all(), [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return back()->withErrors($validator)->withInput()->with('error', 'Error al actualizar la dirección');
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($appointment, $validated) {
            if (! is_null($appointment->nanny_id)) {
                $appointment->forceFill([
                    'nanny_id' => null,
                    'status' => StatusEnum::DRAFT->value,
                ])->save();
            }

            $appointment->addresses()->sync([$validated['address_id']]);
        });

        if ($request->expectsJson()) {
            return response()->json(['ok' => true], 200);
        }

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('openAppointmentId', $appointment->id)
            ->with('success', 'Dirección actualizada exitosamente');
    }

    /**
     * Update children (DRAFT/PENDING)
     */
    public function updateChildren(Request $request, Booking $booking, BookingAppointment $appointment)
    {
        Gate::authorize('updateChildren', $appointment);

        $validator = Validator::make($request->all(), [
            'child_ids' => ['required', 'array', 'min:1', 'max:4'],
            'child_ids.*' => ['required', 'integer', 'exists:children,id'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return back()->withErrors($validator)->withInput()->with('error', 'Error al actualizar los niños');
        }

        $validated = $validator->validated();
        $appointment->children()->sync($validated['child_ids']);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true], 200);
        }

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('openAppointmentId', $appointment->id)
            ->with('success', 'Niños actualizados exitosamente');
    }
}
