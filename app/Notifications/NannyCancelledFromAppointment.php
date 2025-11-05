<?php

namespace App\Notifications;

use App\Models\BookingAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NannyCancelledFromAppointment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BookingAppointment $appointment,
        public string $cancelledBy // 'tutor', 'nanny', 'admin'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** Construye el payload común para database/broadcast */
    protected function payload(): array
    {
        // Asegura relaciones y locale en español
        $this->appointment->loadMissing('booking');
        $start = $this->appointment->start_date->locale('es');
        $fechaBonita = $start->isoFormat('dddd D [de] MMMM [a las] h:mm a');

        $mensaje = match($this->cancelledBy) {
            'tutor' => sprintf(
                'El tutor ha cancelado la niñera asignada para la cita del %s (Servicio #%d).',
                $fechaBonita,
                $this->appointment->booking_id
            ),
            'nanny' => sprintf(
                'La niñera se ha dado de baja de la cita del %s (Servicio #%d).',
                $fechaBonita,
                $this->appointment->booking_id
            ),
            'admin' => sprintf(
                'Un administrador ha cancelado la niñera asignada para la cita del %s (Servicio #%d).',
                $fechaBonita,
                $this->appointment->booking_id
            ),
            default => sprintf(
                'La niñera ha sido cancelada de la cita del %s (Servicio #%d).',
                $fechaBonita,
                $this->appointment->booking_id
            ),
        };

        return [
            'message'        => $mensaje,
            'appointment_id' => $this->appointment->id,
            'booking_id'     => $this->appointment->booking_id,
            'start_date'     => $this->appointment->start_date->toISOString(),
            'redirect'       => route('bookings.show', $this->appointment->booking_id),
            'type'           => 'nanny_cancelled_from_appointment',
            'cancelled_by'   => $this->cancelledBy,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }
}
