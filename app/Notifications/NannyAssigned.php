<?php

namespace App\Notifications;

use App\Models\BookingAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NannyAssigned extends Notification implements ShouldQueue
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
        $this->appointment->loadMissing('booking');
        $start = $this->appointment->start_date->locale('es');
        // Ej: "miércoles 8 de enero a las 8:00 pm"
        $fechaBonita = $start->isoFormat('dddd D [de] MMMM [a las] h:mm a');

        $mensaje = sprintf(
            'Se te ha solicitado para la cita con fecha %s del servicio #%d.',
            $fechaBonita,
            $this->appointment->booking_id 
        );

        return [
            'message'        => $mensaje,
            'appointment_id' => $this->appointment->id,
            'booking_id'     => $this->appointment->booking_id,
            'start_date'     => $this->appointment->start_date->toISOString(),
            'redirect'       => route('bookings.show', $this->appointment->booking_id),
            'type'           => 'nanny_assigned',
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
