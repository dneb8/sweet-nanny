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
