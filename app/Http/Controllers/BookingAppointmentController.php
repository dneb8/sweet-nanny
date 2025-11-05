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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
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
     * Accept a booking appointment (pending -> confirmed)
     * Nanny accepts the assignment
     */
    public function accept(BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('view', $appointment);

        if ($appointment->status->value !== StatusEnum::PENDING->value) {
            return back()->with('error', 'Solo se pueden aceptar citas en estado pendiente');
        }

        if (!$appointment->nanny_id) {
            return back()->with('error', 'No hay niñera asignada para aceptar');
        }

        $appointment->update([
            'status' => StatusEnum::CONFIRMED->value,
        ]);

        // Notify tutor
        $appointment->loadMissing('booking.tutor.user');
        $tutorUser = $appointment->booking?->tutor?->user;
        if ($tutorUser) {
            $tutorUser->notify(new AppointmentAccepted($appointment));
        }

        // Notify admin (get first admin user)
        $admin = \App\Models\User::role('admin')->first();
        if ($admin) {
            $admin->notify(new AppointmentAccepted($appointment));
        }

        return back()->with('success', 'Cita aceptada exitosamente');
    }

    /**
     * Reject a booking appointment (remove nanny assignment)
     * Nanny rejects the assignment
     */
    public function reject(BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('view', $appointment);

        if ($appointment->status->value !== StatusEnum::PENDING->value) {
            return back()->with('error', 'Solo se pueden rechazar citas en estado pendiente');
        }

        if (!$appointment->nanny_id) {
            return back()->with('error', 'No hay niñera asignada para rechazar');
        }

        $oldNannyId = $appointment->nanny_id;

        // Remove nanny and set status to draft
        $appointment->update([
            'nanny_id' => null,
            'status' => StatusEnum::DRAFT->value,
        ]);

        // Notify tutor
        $appointment->loadMissing('booking.tutor.user');
        $tutorUser = $appointment->booking?->tutor?->user;
        if ($tutorUser) {
            $tutorUser->notify(new AppointmentRejected($appointment));
        }

        // Notify admin
        $admin = \App\Models\User::role('admin')->first();
        if ($admin) {
            $admin->notify(new AppointmentRejected($appointment));
        }

        // Check if booking should go back to draft
        $booking = $appointment->booking;
        if ($booking) {
            $hasAnyConfirmedAppointments = $booking->appointments()
                ->where('status', StatusEnum::CONFIRMED->value)
                ->exists();
            
            if (!$hasAnyConfirmedAppointments) {
                $booking->update(['status' => StatusEnum::DRAFT->value]);
            }
        }

        return back()->with('success', 'Solicitud rechazada exitosamente');
    }

    /**
     * Unassign nanny from a confirmed appointment
     * Can be done by tutor, nanny, or admin
     */
    public function unassignNanny(BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('view', $appointment);

        if ($appointment->status->value !== StatusEnum::CONFIRMED->value) {
            return back()->with('error', 'Solo se puede cancelar la niñera de citas confirmadas');
        }

        if (!$appointment->nanny_id) {
            return back()->with('error', 'No hay niñera asignada');
        }
        $user = Auth::user();
        $appointment->loadMissing('booking.tutor.user', 'nanny.user');
        $appointment->loadMissing('booking.tutor.user', 'nanny.user');

        // Determine who is cancelling
        $cancelledBy = 'admin'; // default
        if ($user->hasRole('tutor') && $appointment->booking?->tutor?->user_id === $user->id) {
            $cancelledBy = 'tutor';
        } elseif ($user->hasRole('nanny') && $appointment->nanny?->user_id === $user->id) {
            $cancelledBy = 'nanny';
        }

        $nannyUser = $appointment->nanny?->user;
        $tutorUser = $appointment->booking?->tutor?->user;

        // Remove nanny and set to pending
        $appointment->update([
            'nanny_id' => null,
            'status' => StatusEnum::DRAFT->value,
        ]);

        // Send notifications based on who cancelled
        if ($cancelledBy === 'tutor') {
            // Notify nanny and admin
            if ($nannyUser) {
                $nannyUser->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
            $admin = \App\Models\User::role('admin')->first();
            if ($admin) {
                $admin->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
        } elseif ($cancelledBy === 'nanny') {
            // Notify tutor and admin
            if ($tutorUser) {
                $tutorUser->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
            $admin = \App\Models\User::role('admin')->first();
            if ($admin) {
                $admin->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
        } else {
            // Admin cancelled, notify both
            if ($tutorUser) {
                $tutorUser->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
            if ($nannyUser) {
                $nannyUser->notify(new NannyCancelledFromAppointment($appointment, $cancelledBy));
            }
        }

        $appointment->update(['status' => StatusEnum::DRAFT->value]);

        return back()->with('success', 'Niñera cancelada exitosamente');
    }

    /**
     * Cancel a booking appointment directly (confirmed -> cancelled)
     */
    public function cancelDirect(BookingAppointment $appointment): RedirectResponse
    {
        Gate::authorize('view', $appointment);

        if ($appointment->status !== StatusEnum::CONFIRMED->value) {
            return back()->with('error', 'Solo se pueden cancelar citas en estado confirmado');
        }

        $appointment->update([
            'status' => StatusEnum::CANCELLED->value,
        ]);

        return back()->with('success', 'Cita cancelada exitosamente');
    }

    /**
     * Cancel a booking appointment
     */
    public function cancel(Booking $booking, BookingAppointment $appointment): RedirectResponse
    {
        // Authorization: use the same chooseNanny policy for simplicity
        // Admin can cancel any, Tutor can cancel their own
        Gate::authorize('chooseNanny', $appointment);

        // Update status to cancelled
        $appointment->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('success', 'Cita cancelada exitosamente');
    }

    /**
     * Update appointment dates
     */
    public function updateDates(Request $request, Booking $booking, BookingAppointment $appointment)
    {
        $v = Validator::make($request->all(), [
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:8'],
        ]);

        if ($v->fails()) {
            return redirect()->back()->withErrors($v)->withInput()->with('error', 'Error al actualizar las fechas');
        }

        $data = $v->validated();
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $dur = $data['duration'] ?? max(1, (int) ceil($start->floatDiffInHours($end)));

        DB::transaction(function () use ($appointment, $start, $end, $dur) {
            // Si tenía niñera, desasigna y vuelve a DRAFT
            if (! is_null($appointment->nanny_id)) {
                $appointment->forceFill([
                    'nanny_id' => null,
                    'status' => StatusEnum::DRAFT,
                ])->save();
            }

            $payload = ['start_date' => $start, 'end_date' => $end];
            if ($appointment->isFillable('duration')) {
                $payload['duration'] = $dur;
            }

            $appointment->update($payload);
        });

        return redirect()->back()->with('success', 'Fechas actualizadas exitosamente');
    }

    /**
     * Update appointment address
     */
    public function updateAddress(Request $request, Booking $booking, BookingAppointment $appointment)
    {

        $validator = Validator::make($request->all(), [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Error al actualizar la dirección');
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($appointment, $validated) {
            // If status is pending and we're editing address, unassign nanny and revert to draft
            if (! is_null($appointment->nanny_id)) {
                $appointment->forceFill([
                    'nanny_id' => null,
                    'status' => StatusEnum::DRAFT,
                ])->save();
            }

            // Sync the address (replace existing)
            $appointment->addresses()->sync([$validated['address_id']]);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Dirección actualizada exitosamente'], 200);
        }
        return redirect()->back()->with('success', 'Dirección actualizada exitosamente');
    }

    /**
     * Update appointment children
     */
    public function updateChildren(Request $request, Booking $booking, BookingAppointment $appointment)
    {

        $validator = Validator::make($request->all(), [
            'child_ids' => ['required', 'array', 'min:1', 'max:4'],
            'child_ids.*' => ['required', 'integer', 'exists:children,id'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Error al actualizar los niños');
        }

        $validated = $validator->validated();

        // Sync children
        $appointment->children()->sync($validated['child_ids']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Niños actualizados exitosamente'], 200);
        }
        return redirect()->back()->with('success', 'Niños actualizados exitosamente');
    }
}
