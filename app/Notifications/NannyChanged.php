<?php

namespace App\Notifications;

use App\Models\BookingAppointment;
use App\Models\Nanny;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NannyChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BookingAppointment $appointment,
        public Nanny $oldNanny,
        public Nanny $newNanny
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** Build common payload (DB + Broadcast) */
    protected function payload(): array
    {
        $this->appointment->loadMissing('booking');
        $this->oldNanny->loadMissing('user');
        $this->newNanny->loadMissing('user');

        $start = $this->appointment->start_date->locale('es');
        // Ej: "miércoles 8 de enero a las 8:00 pm"
        $fechaBonita = ucfirst($start->isoFormat('dddd D [de] MMMM [a las] h:mm a'));

        $oldName = $this->oldNanny->user->name ?? 'Desconocida';
        $newName = $this->newNanny->user->name ?? 'Desconocida';

        $mensaje = sprintf(
            'La niñera de la cita del %s fue cambiada de %s a %s para el servicio #%d.',
            $fechaBonita,
            $oldName,
            $newName,
            $this->appointment->booking_id // o $this->appointment->id si prefieres
        );

        return [
            'message'         => $mensaje,
            'appointment_id'  => $this->appointment->id,
            'booking_id'      => $this->appointment->booking_id,
            'old_nanny_name'  => $oldName,
            'new_nanny_name'  => $newName,
            'start_date'      => $this->appointment->start_date->toISOString(),
            'redirect'        => route('bookings.show', $this->appointment->booking_id),
            'type'            => 'nanny_changed',
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
