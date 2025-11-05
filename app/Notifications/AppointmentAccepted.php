<?php

namespace App\Notifications;

use App\Models\BookingAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AppointmentAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BookingAppointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** Construye el payload común para database/broadcast */
    protected function payload(): array
    {
        // Asegura relaciones y locale en español
        $this->appointment->loadMissing('booking', 'nanny.user');
        $start = $this->appointment->start_date->locale('es');
        $fechaBonita = $start->isoFormat('dddd D [de] MMMM [a las] h:mm a');

        $nannyName = $this->appointment->nanny?->user?->name ?? 'La niñera';

        $mensaje = sprintf(
            '%s ha aceptado tu solicitud para la cita del %s (Servicio #%d).',
            $nannyName,
            $fechaBonita,
            $this->appointment->booking_id 
        );

        return [
            'message'        => $mensaje,
            'appointment_id' => $this->appointment->id,
            'booking_id'     => $this->appointment->booking_id,
            'start_date'     => $this->appointment->start_date->toISOString(),
            'redirect'       => route('bookings.show', $this->appointment->booking_id),
            'type'           => 'appointment_accepted',
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
