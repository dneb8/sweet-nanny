<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAppointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

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
}
