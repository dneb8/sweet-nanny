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

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public BookingAppointment $appointment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointment->loadMissing('booking');

        return [
            'message' => sprintf(
                'Fuiste asignada al appointment #%d del booking #%d para la fecha %s',
                $this->appointment->id,
                $this->appointment->booking_id,
                $this->appointment->start_date->format('d/m/Y H:i')
            ),
            'appointment_id' => $this->appointment->id,
            'booking_id' => $this->appointment->booking_id,
            'start_date' => $this->appointment->start_date->toISOString(),
            'redirect' => route('bookings.show', $this->appointment->booking_id),
            'type' => 'nanny_assigned',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $this->appointment->loadMissing('booking');

        return new BroadcastMessage([
            'message' => sprintf(
                'Fuiste asignada al appointment #%d del booking #%d para la fecha %s',
                $this->appointment->id,
                $this->appointment->booking_id,
                $this->appointment->start_date->format('d/m/Y H:i')
            ),
            'appointment_id' => $this->appointment->id,
            'booking_id' => $this->appointment->booking_id,
            'start_date' => $this->appointment->start_date->toISOString(),
            'redirect' => route('bookings.show', $this->appointment->booking_id),
            'type' => 'nanny_assigned',
        ]);
    }
}
