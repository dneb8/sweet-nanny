<?php

namespace App\Observers;

use App\Models\BookingAppointment;
use App\Enums\Booking\StatusEnum;
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
    }

    private function setStatus(BookingAppointment $appointment)
    {
        // Si ya está cancelado o completado, no cambiar
        if (in_array($appointment->status, [StatusEnum::CANCELLED, StatusEnum::COMPLETED])) {
            return;
        }

        if (!$appointment->nanny_id) {
            $appointment->status = StatusEnum::PENDING;
        } else {
            $now = Carbon::now();
            if ($appointment->start_date <= $now && $appointment->end_date >= $now) {
                $appointment->status = StatusEnum::IN_PROGRESS;
            } elseif ($appointment->end_date < $now) {
                $appointment->status = StatusEnum::COMPLETED;
            } else {
                $appointment->status = StatusEnum::CONFIRMED;
            }
        }
    }
}
