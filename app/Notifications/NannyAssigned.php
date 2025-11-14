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

    /** URL de redirección según rol/permisos del notificado */
    protected function redirectFor(object $notifiable): string
    {
        // Carga relaciones necesarias
        $this->appointment->loadMissing('booking');

        // Si el notificado es NIÑERA: a su show de BookingAppointment (o índice)
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('nanny')) {
            // si tienes show:
            return route('booking-appointments.index');
        }

        // Si es TUTOR: a la vista del Booking (que sí existe para tutor)
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')) {
            return route('bookings.show', $this->appointment->booking_id);
        }

        // Si es ADMIN u otro rol: a algo que todos tengan (por ejemplo, show del booking)
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('admin')) {
            return route('bookings.show', $this->appointment->booking_id);
        }

        // Fallback: home/dashboard para evitar errores
        return route('dashboard');
    }

    /** Construye el payload común para database/broadcast */
    protected function payload(object $notifiable): array
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
            'redirect'       => $this->redirectFor($notifiable), // 👈 dinámico por rol
            'type'           => 'nanny_assigned',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload($notifiable);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload($notifiable));
    }
}
