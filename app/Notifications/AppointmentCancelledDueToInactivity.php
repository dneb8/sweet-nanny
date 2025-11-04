<?php

namespace App\Notifications;

use App\Models\BookingAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledDueToInactivity extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BookingAppointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** Build common payload (DB + Broadcast) */
    protected function payload(): array
    {
        $this->appointment->loadMissing('booking');
        
        $start = $this->appointment->start_date->locale('es');
        // Format: "miércoles 8 de enero a las 8:00 pm"
        $fechaBonita = ucfirst($start->isoFormat('dddd D [de] MMMM [a las] h:mm a'));

        $mensaje = sprintf(
            'Lo sentimos, tu cita del %s ha sido cancelada debido a inactividad (no se confirmó niñera).',
            $fechaBonita
        );

        return [
            'message'        => $mensaje,
            'appointment_id' => $this->appointment->id,
            'booking_id'     => $this->appointment->booking_id,
            'start_date'     => $this->appointment->start_date->toISOString(),
            'redirect'       => route('bookings.show', $this->appointment->booking_id),
            'type'           => 'appointment_cancelled_inactivity',
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
