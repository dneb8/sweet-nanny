<?php

namespace App\Observers;

use App\Models\BookingAppointment;
use App\Enums\Booking\StatusEnum;
use App\Notifications\AppointmentCancelledDueToInactivity;
use Carbon\Carbon;

class BookingAppointmentObserver
{
    public function creating(BookingAppointment $appointment)
    {
        $this->setStatus($appointment);
    }

    public function updating(BookingAppointment $appointment)
    {
        $this->setStatus($appointment);
        $this->checkInactivityCancellation($appointment);
    }

    /**
     * Check if appointment should be auto-cancelled due to inactivity
     * (no confirmed nanny 30 minutes before start)
     */
    private function checkInactivityCancellation(BookingAppointment $appointment)
    {
        // Skip if already cancelled or completed
        if (in_array($appointment->status, [StatusEnum::CANCELLED, StatusEnum::COMPLETED])) {
            return;
        }

        // Skip if status is not pending (already confirmed or in progress)
        if ($appointment->status !== StatusEnum::PENDING) {
            return;
        }

        $now = now();
        $thirtyMinutesBeforeStart = $appointment->start_date->copy()->subMinutes(30);

        // If we're past the 30-minute threshold and still pending, cancel
        if ($now >= $thirtyMinutesBeforeStart) {
            $appointment->status = StatusEnum::CANCELLED->value;
            
            // Notify tutor after save
            $appointment->saveQuietly(); // Save without triggering observer again
            
            // Load relationship and send notification
            $appointment->loadMissing('booking.tutor.user');
            $tutorUser = $appointment->booking?->tutor?->user;
            if ($tutorUser) {
                $tutorUser->notify(new AppointmentCancelledDueToInactivity($appointment));
            }
        }
    }

    private function setStatus(BookingAppointment $appointment)
    {
        // Respetar terminales
        if (in_array($appointment->status, [StatusEnum::CANCELLED, StatusEnum::COMPLETED])) {
            return;
        }

        // Solo recalcular si cambian fechas
        if (! $appointment->isDirty(['start_date', 'end_date'])) {
            return;
        }

        // Si está PENDING, no promover por tiempo (falta confirmación de niñera)
        if ($appointment->status === StatusEnum::PENDING->value) {
            return;
        }

        // Con CONFIRMED: promover por tiempo
        $now = now();
        if ($appointment->status === StatusEnum::CONFIRMED->value) {
            if ($appointment->start_date <= $now && $appointment->end_date >= $now) {
                $appointment->status = StatusEnum::IN_PROGRESS->value;
            } elseif ($appointment->end_date < $now) {
                $appointment->status = StatusEnum::COMPLETED->value;
            }
        }
    }


}
