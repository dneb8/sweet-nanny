<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class BookingStatusService
{
    /**
     * Update booking status based on appointment times
     */
    public function updateStatus(Booking $booking): Booking
    {
        // Si el booking no tiene citas, mantener el estado actual
        if ($booking->bookingAppointments->isEmpty()) {
            return $booking;
        }

        // Si ya está finalizado o cancelado, no actualizar
        if (in_array($booking->status, ['finalizado', 'cancelado'])) {
            return $booking;
        }

        $now = Carbon::now();
        $allAppointments = $booking->bookingAppointments;

        // Verificar si alguna cita está en curso
        $isInProgress = $allAppointments->contains(function ($appointment) use ($now) {
            $startDate = Carbon::parse($appointment->start_date);
            $endDate = Carbon::parse($appointment->end_date);
            return $now->between($startDate, $endDate);
        });

        if ($isInProgress) {
            $booking->update(['status' => 'en_curso']);
            return $booking->fresh();
        }

        // Verificar si todas las citas terminaron
        $allFinished = $allAppointments->every(function ($appointment) use ($now) {
            $endDate = Carbon::parse($appointment->end_date);
            return $endDate->lt($now);
        });

        if ($allFinished) {
            $booking->update(['status' => 'finalizado']);
            return $booking->fresh();
        }

        // Si hay citas futuras y ninguna en curso, mantener estado actual
        return $booking;
    }

    /**
     * Update all bookings statuses (for scheduled command)
     */
    public function updateAllStatuses(): int
    {
        $updatedCount = 0;
        
        $bookings = Booking::with('bookingAppointments')
            ->whereNotIn('status', ['finalizado', 'cancelado'])
            ->get();

        foreach ($bookings as $booking) {
            $originalStatus = $booking->status;
            $this->updateStatus($booking);
            
            if ($booking->status !== $originalStatus) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
