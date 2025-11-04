<?php

namespace App\Http\Controllers;

use App\Enums\Booking\StatusEnum;
use App\Models\Booking;
use App\Models\BookingAppointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class BookingAppointmentController extends Controller
{
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
            return back()->withErrors($v)->withInput()->with('error', 'Error al actualizar las fechas');
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

        return back()->with('success', 'Fechas actualizadas exitosamente');
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

        return back()->with('success', 'Dirección actualizada exitosamente');
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
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Error al actualizar los niños');
        }

        $validated = $validator->validated();

        // Sync children
        $appointment->children()->sync($validated['child_ids']);

        return back()->with('success', 'Niños actualizados exitosamente');
    }
}
